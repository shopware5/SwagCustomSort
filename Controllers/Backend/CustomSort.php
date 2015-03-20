<?php

class Shopware_Controllers_Backend_CustomSort extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var Shopware\Components\Model\ModelManager $em
     */
    private $em = null;

    /**
     * @var Shopware\CustomModels\CustomSort\ArticleSort
     */
    private $sortRepo = null;

    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    private $db = null;

    /**
     * References the shopware config object
     *
     * @var Shopware_Components_Config
     */
    private $config = null;

    /**
     * @return Shopware\Components\Model\ModelManager
     */
    public function getModelManager()
    {
        if ($this->em === null) {
            $this->em = Shopware()->Models();
        }

        return $this->em;
    }

    /**
     * Returns sort repository
     *
     * @return Shopware\CustomModels\CustomSort\ArticleSort
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
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
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
     * @return Shopware_Components_Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = Shopware()->Config();
        }

        return $this->config;
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
            $builder = $this->getSortRepository()->getArticleImageQuery($categoryId);
            $this->sortUnsortedByDefault($builder, $sort);

            $total = $builder->execute()->rowCount();

            if ($offset !== null && $limit !== null) {
                $builder->setFirstResult($offset)
                        ->setMaxResults($limit);
            }

            $result = $builder->execute()->fetchAll();

            $this->View()->assign(array('success' => true, 'data' => $result, 'total' => $total));
        } catch (\Exception $ex) {
            $this->View()->assign(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    /**
     * Sort products for current category by passed sort type
     *
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param integer $sort
     */
    private function sortUnsortedByDefault($builder, $sort)
    {
        switch ($sort) {
            case 1:
                $builder
                    ->addOrderBy('product.datum', 'DESC')
                    ->addOrderBy('product.changetime', 'DESC');
                break;
            case 2:
                $builder
                    ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
                    ->addOrderBy('topSeller.sales', 'DESC')
                    ->addOrderBy('topSeller.article_id', 'DESC');
                break;
            case 3:
                $builder
                    ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                    ->addOrderBy('cheapest_price', 'ASC');
                break;
            case 4:
                $builder
                    ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                    ->addOrderBy('cheapest_price', 'DESC');
                break;
            case 5:
                $builder->addOrderBy('product.name', 'ASC');
                break;
            case 6:
                $builder->addOrderBy('product.name', 'DESC');
                break;
        }
    }

    /**
     * Get settings for current category
     */
    public function getCategorySettingsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');

        $data = array();

        $categoryAttributes = $this->getModelManager()->getRepository('\Shopware\Models\Attribute\Category')->findOneBy(array('categoryId' => $categoryId));
        if ($categoryAttributes) {
            $defaultSort = $this->getConfig()->get('defaultListingSorting');
            $data = array(
                'id' => null,
                'defaultSort' => $categoryAttributes->getSwagShowByDefault(),
                'categoryLink' => $categoryAttributes->getSwagLink(),
                'baseSort' => $defaultSort
            );
        }

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Save category settings for current category
     */
    public function saveCategorySettingsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $categoryLink = (int) $this->Request()->getParam('categoryLink');
        $defaultSort = (int) $this->Request()->getParam('defaultSort');

        try {
            $builder = $this->getModelManager()->createQueryBuilder();
            $builder->update('\Shopware\Models\Attribute\Category', 'categoryAttribute')
                ->set('categoryAttribute.swagLink', $categoryLink)
                ->set('categoryAttribute.swagShowByDefault', $defaultSort)
                ->where('categoryAttribute.categoryId = :categoryId')
                ->setParameter('categoryId', $categoryId);

            $builder->getQuery()->execute();

            $this->View()->assign(array('success' => true));
        } catch(\Exception $ex) {
            $this->View()->assign(array('success' => false, 'message' => $ex->getMessage()));
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
            $movedProducts = array($movedProducts);
        }

        $categoryId = (int) $this->Request()->getParam('categoryId');
        $defaultSort = $this->getConfig()->get('defaultListingSorting');
        $sort = (int) $this->Request()->getParam('sortBy', $defaultSort);

        //get all products
        $builder = $this->getSortRepository()->getArticleImageQuery($categoryId);
        $this->sortUnsortedByDefault($builder, $sort);
        $allProducts = $builder->execute()->fetchAll();

        //get sorted products
        $sortedProducts = $this->applyNewPosition($allProducts, $movedProducts, $categoryId);

        //get sql values needed for update query
        $sqlValues = $this->getSQLValues($sortedProducts, $categoryId);

        $sql = "REPLACE INTO s_articles_sort (id, categoryId, articleId, position, pin) VALUES " . rtrim($sqlValues, ',');
        $this->getDB()->query($sql);

        $this->getSortRepository()->deleteUnpinnedRecords($categoryId);
    }

    /**
     * Apply new positions of the products
     *
     * @param array $allProducts - all products contained in the current category
     * @param array $products - the selected products, that were dragged
     * @param int $categoryId - the id of the current category
     * @return array $result
     */
    private function applyNewPosition($allProducts, $products, $categoryId)
    {
        $allProducts = $this->prepareKeys($allProducts);
        $products = $this->prepareKeys($products);

        //get all products that should be updated
        $offset = $this->getOffset($products, $categoryId);
        $length = $this->getLength($products, $offset);
        $productsForUpdate = array_slice($allProducts, $offset, $length, true);

        //apply new positions for the products
        $result = array();
        foreach($products as $productData) {
            $newPosition = $productData['position'];
            $oldPosition = $productData['oldPosition'];

            $result[$newPosition] = $productData;
            $result[$newPosition]['position'] = $newPosition;
            $result[$newPosition]['oldPosition'] = $oldPosition;
        }

        $index = $offset;
        foreach($productsForUpdate as $id => &$product) {
            if (array_key_exists($id, $products)) {
                continue;
            }

            while($result[$index]) {
                $index++;
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
        foreach($productsForUpdate as $newArticle) {
            if ($newArticle['id'] > 0) {
                $sqlValues .= "('" . $newArticle['positionId'] . "', '" . $categoryId . "', '" . $newArticle['id'] . "', '" . $newArticle['position'] . "', '" . $newArticle['pin'] . "'),";
            }
        }

        return $sqlValues;
    }

    private function prepareKeys($products)
    {
        $result = array();
        foreach($products as $product) {
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
        foreach($products as $productData) {
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

        $offset = min($offset, ++$maxPosition);

        return $offset;
    }

    /**
     * Helper function, for getting a part of the array, which contains all products.
     * Returns the length of the new array.
     *
     * @param array $products
     * @param int $offset
     * @return int - the length of the new array
     */
    private function getLength($products, $offset)
    {
        $length = null;
        foreach($products as $productData) {
            $newPosition = $productData['position'];
            $oldPosition = $productData['oldPosition'];

            if ($length < max($newPosition, $oldPosition) || $length === null) {
                $length = max($newPosition, $oldPosition);
            }
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
        if (!$product['positionId']) {
            $this->View()->assign(array('success' => false, 'message' => 'Fail'));
            return;
        }

        $categoryId = $this->Request()->getParam('categoryId');
        $sortId = (int) $product['positionId'];

        $this->getSortRepository()->unpinById($sortId);

        $this->getSortRepository()->deleteUnpinnedRecords($categoryId);
    }
}