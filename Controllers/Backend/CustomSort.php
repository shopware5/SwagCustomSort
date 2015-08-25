<?php

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use Shopware\Components\Model\ModelManager;
use Shopware\CustomModels\CustomSort\CustomSortRepository;
use Shopware\Models\Article\Article;
use Shopware\Models\Attribute\Category as CategoryAttributes;
use Shopware\Models\Category\Category;

class Shopware_Controllers_Backend_CustomSort extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var ModelManager $em
     */
    private $em = null;

    /**
     * @var CustomSortRepository
     */
    private $sortRepo = null;

    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    private $db = null;

    /**
     * References the shopware config object
     *
     * @var \Shopware_Components_Config
     */
    private $config = null;


    /**
     * @var \Enlight_Event_EventManager
     */
    private $events = null;

    /**
     * @return ModelManager
     */
    public function getModelManager()
    {
        if ($this->em === null) {
            $this->em = Shopware()->Models();
        }

        return $this->em;
    }

    /**
     * @var array
     */
    protected $categoryIdCollection;

    /**
     * Returns sort repository
     *
     * @return CustomSortRepository
     */
    public function getSortRepository()
    {
        if ($this->sortRepo === null) {
            $this->sortRepo = $this->getModelManager()->getRepository('\Shopware\CustomModels\CustomSort\ArticleSort');
        }

        return $this->sortRepo;
    }

    /**
     * Returns pdo mysql db adapter instance
     *
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDB()
    {
        if ($this->db === null) {
            $this->db = Shopware()->Db();
        }

        return $this->db;
    }

    /**
     * Returns config instance
     *
     * @return \Shopware_Components_Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = Shopware()->Config();
        }

        return $this->config;
    }

    /**
     * @return \Enlight_Event_EventManager
     */
    public function getEvents()
    {
        if ($this->events === null) {
            $this->events = Shopware()->Events();
        }

        return $this->events;
    }

    /**
     * Get article list and images for current category
     */
    public function getArticleListAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $limit = (int) $this->Request()->getParam('limit', null);
        $offset = (int) $this->Request()->getParam('start');

        $defaultSort = $this->getConfig()->get('defaultListingSorting');
        $sort = (int) $this->Request()->getParam('sortBy', $defaultSort);

        try {
            $builder = $this->getSortRepository()->getArticleImageQuery($categoryId, $sort);
            $countBuilder = $this->getSortRepository()->getArticleImageCountQuery($categoryId);

            if ($offset !== null && $limit !== null) {
                $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
            }

            $getProducts = $builder->execute()->fetchAll();
            $total = $countBuilder->execute()->fetch();

            $this->View()->assign(['success' => true, 'data' => $getProducts, 'total' => $total['Total']]);
        } catch (\Exception $ex) {
            $this->View()->assign(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    /**
     * Get settings for current category
     */
    public function getCategorySettingsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $defaultSort = $this->getConfig()->get('defaultListingSorting');

        $data = [
            'id' => null,
            'defaultSort' => 0,
            'categoryLink' => 0,
            'baseSort' => $defaultSort
        ];

        /** @var CategoryAttributes $categoryAttributes */
        $categoryAttributes = $this->getModelManager()->getRepository('\Shopware\Models\Attribute\Category')
            ->findOneBy(['categoryId' => $categoryId]);
        if ($categoryAttributes) {
            $baseSort = $categoryAttributes->getSwagBaseSort();
            if ($baseSort > 0) {
                $defaultSort = $baseSort;
            }

            $data = [
                'id' => null,
                'defaultSort' => $categoryAttributes->getSwagShowByDefault(),
                'categoryLink' => $categoryAttributes->getSwagLink(),
                'baseSort' => $defaultSort
            ];
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Save category settings for current category
     */
    public function saveCategorySettingsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $categoryLink = (int) $this->Request()->getParam('categoryLink');
        $defaultSort = (int) $this->Request()->getParam('defaultSort');
        $baseSort = (int) $this->Request()->getParam('baseSort');

        try {
            $this->getSortRepository()->updateCategoryAttributes($categoryId, $baseSort, $categoryLink, $defaultSort);

            $this->View()->assign(['success' => true]);
        } catch (\Exception $ex) {
            $this->View()->assign(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    /**
     * Save product list after product reorder
     */
    public function saveArticleListAction()
    {
        $movedProducts = $this->Request()->getParam('products');
        if (empty($movedProducts)) {
            return;
        }
        if ($movedProducts['id']) {
            $movedProducts = [$movedProducts];
        }

        $categoryId = (int) $this->Request()->getParam('categoryId');
        $movedProducts = $this->prepareKeys($movedProducts);
        $offset = $this->getOffset($movedProducts, $categoryId);
        $length = $this->getLength($movedProducts, $offset, $categoryId);
        $defaultSort = $this->getConfig()->get('defaultListingSorting');
        $sort = (int) $this->Request()->getParam('sortBy', $defaultSort);

        //get all products
        $builder = $this->getSortRepository()->getArticleImageQuery($categoryId, $sort);
        if ($offset !== null && $length !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($length);
        }

        $allProducts = $builder->execute()->fetchAll();

        //check for deleted products
        $deletedPosition = $this->getSortRepository()->getPositionOfDeletedProduct($categoryId);
        if ($deletedPosition !== null) {
            $allProducts = $this->fixDeletedPosition((int) $deletedPosition, $allProducts);
        }

        //get sorted products
        $sortedProducts = $this->applyNewPosition($allProducts, $movedProducts, $offset);

        //get sql values needed for update query
        $sqlValues = $this->getSQLValues($sortedProducts, $categoryId);

        //update positions
        $sql = "REPLACE INTO s_articles_sort (id, categoryId, articleId, position, pin) VALUES " . rtrim($sqlValues, ',');
        $this->getDB()->query($sql);

        //reset deleted product flag
        $this->getSortRepository()->resetDeletedPosition($categoryId);

        //after update check for unnecessary records (delete all records to the last pin product)
        $this->getSortRepository()->deleteUnpinnedRecords($categoryId);

        //set current product's cache as invalid
        $this->invalidateProductCache($movedProducts);

        $this->View()->assign(['success' => true]);
    }

    /**
     * Apply new positions of the products
     *
     * @param array $allProducts - all products contained in the current category
     * @param array $products - the selected products, that were dragged
     * @param int $index - the id of offset products
     * @return array $result
     */
    private function applyNewPosition($allProducts, $products, $index)
    {
        $allProducts = $this->prepareKeys($allProducts);
        $products = $this->prepareKeys($products);

        //apply new positions for the products
        $result = [];
        foreach ($products as $productData) {
            $newPosition = $productData['position'];
            $oldPosition = $productData['oldPosition'];

            $result[$newPosition] = $productData;
            $result[$newPosition]['position'] = $newPosition;
            $result[$newPosition]['oldPosition'] = $oldPosition;
        }

        foreach ($allProducts as $id => &$product) {
            if (array_key_exists($id, $products)) {
                continue;
            }

            while (array_key_exists($index, $result)) {
                ++$index;
            }

            $result[$index] = $product;
            $result[$index]['position'] = $index;
            $result[$index]['oldPosition'] = $index;

            $index++;
        }

        return $result;
    }

    /**
     * Returns sql values for update query
     *
     * @param array $productsForUpdate
     * @param int $categoryId
     * @return string - values for update
     */
    private function getSQLValues($productsForUpdate, $categoryId)
    {
        $sqlValues = '';
        foreach ($productsForUpdate as $newArticle) {
            if ($newArticle['id'] > 0) {
                $sqlValues .= "('" . $newArticle['positionId'] . "', '" . $categoryId . "', '" . $newArticle['id'] . "', '" . $newArticle['position'] . "', '" . $newArticle['pin'] . "'),";
            }
        }

        return $sqlValues;
    }

    private function prepareKeys($products)
    {
        $result = [];
        foreach ($products as $product) {
            $result[$product['id']] = $product;
        }

        return $result;
    }

    /**
     * Helper function, for getting a part of the array, which contains all products.
     * Returns the offset from which the new array should start.
     *
     * @param array $products - selected products
     * @param int $categoryId
     * @return int - the smallest position
     */
    private function getOffset($products, $categoryId)
    {
        $offset = null;
        foreach ($products as $productData) {
            $newPosition = $productData['position'];
            $oldPosition = $productData['oldPosition'];

            if ($offset > min($newPosition, $oldPosition) || $offset === null) {
                $offset = min($newPosition, $oldPosition);
            }
        }

        $maxPosition = $this->getSortRepository()->getMaxPosition($categoryId);
        if ($maxPosition === null) {
            return 0;
        }

        //checks for deleted products
        $deletedPosition = $this->getSortRepository()->getPositionOfDeletedProduct($categoryId);
        if ($deletedPosition !== null) {
            $offset = min($offset, ++$maxPosition, $deletedPosition);
        } else {
            $offset = min($offset, ++$maxPosition);
        }

        return $offset;
    }

    /**
     * Helper function, for getting a part of the array, which contains all products.
     * Returns the length of the new array.
     *
     * @param array $products
     * @param int $offset
     * @param int $categoryId
     * @return int - the length of the new array
     */
    private function getLength($products, $offset, $categoryId)
    {
        $length = null;
        foreach ($products as $productData) {
            $newPosition = $productData['position'];
            $oldPosition = $productData['oldPosition'];

            if ($length < max($newPosition, $oldPosition) || $length === null) {
                $length = max($newPosition, $oldPosition);
            }
        }

        //checks for deleted products
        $deletedPosition = $this->getSortRepository()->getPositionOfDeletedProduct($categoryId);
        if ($deletedPosition !== null) {
            $maxPosition = $this->getSortRepository()->getMaxPosition($categoryId);
            $length = max($length, $maxPosition);
        }

        $length = ($length - $offset) + 1;

        return $length;
    }

    /**
     * Unpin product
     */
    public function unpinArticleAction()
    {
        $product = $this->Request()->getParam('products');
        $sortId = (int) $product['positionId'];

        try {
            if (!$sortId) {
                throw new Exception("Unpin product '{$product['name']}' with id '{$product['id']}', failed!");
            }

            $categoryId = (int) $this->Request()->getParam('categoryId');

            $this->getSortRepository()->unpinById($sortId);

            $this->getSortRepository()->deleteUnpinnedRecords($categoryId);

            $this->View()->assign(['success' => true]);
        } catch (\Exception $ex) {
            $this->View()->assign(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    private function fixDeletedPosition($deletedPosition, $allProducts)
    {
        $index = $deletedPosition;
        foreach ($allProducts as &$product) {
            if ($product['position'] < $deletedPosition) {
                continue;
            }

            if ($product['position'] === null) {
                break;
            }

            $product['position'] = $index++;
        }

        return $allProducts;
    }

    private function invalidateProductCache($movedProducts)
    {
        //Invalidate the cache for the current product
        foreach ($movedProducts as $product) {
            $this->getEvents()->notify('Shopware_Plugins_HttpCache_InvalidateCacheId', ['cacheId' => "a{$product['id']}"]);
            break;
        }
    }

    /**
     * Remove product from current and child categories.
     */
    public function removeProductAction()
    {
        $articleId = (int) $this->Request()->get('articleId');
        $categoryId = (int) $this->Request()->get('categoryId');

        /** @var Category $category */
        $category = Shopware()->Models()->getReference('Shopware\Models\Category\Category', $categoryId);
        if ($category) {
            $this->collectCategoryIds($category);
            $categories = $this->getCategoryIdCollection();

            /** @var Article $article */
            $article = Shopware()->Models()->getReference('Shopware\Models\Article\Article', (int) $articleId);
            $article->removeCategory($category);

            if ($categories) {
                foreach ($categories as $childCategoryId) {
                    /** @var Category $childCategoryModel */
                    $childCategoryModel = Shopware()->Models()->getReference('Shopware\Models\Category\Category', $childCategoryId);
                    if ($childCategoryModel) {
                        $article->removeCategory($childCategoryModel);
                    }
                }
            }

            Shopware()->Models()->flush();
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Check current category for child categories and
     * add ids to collection.
     *
     * @param Category $categoryModel
     */
    private function collectCategoryIds($categoryModel)
    {
        $categoryId = $categoryModel->getId();
        $this->setCategoryIdCollection($categoryId);

        $sql = "SELECT id FROM s_categories WHERE path LIKE ?";
        $categories = Shopware()->Db()->fetchAll($sql, ['%|' . $categoryId . '|%']);

        if (!$categories) {
            return;
        }

        foreach ($categories as $categoryId) {
            $this->setCategoryIdCollection($categoryId);
        }

        return;
    }

    /**
     * Get category ids collection.
     *
     * @return array
     */
    public function getCategoryIdCollection()
    {
        return $this->categoryIdCollection;
    }

    /**
     * Insert category id to category ids collection.
     *
     * @param $categoryIdCollection
     * @return array
     */
    public function setCategoryIdCollection($categoryIdCollection)
    {
        $this->categoryIdCollection[] = $categoryIdCollection;
    }
}
