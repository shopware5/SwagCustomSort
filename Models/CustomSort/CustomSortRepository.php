<?php

namespace Shopware\CustomModels\CustomSort;

use Shopware\Components\Model\ModelRepository;

class CustomSortRepository extends ModelRepository
{

    /**
     * Check if selected category has custom sorted products
     *
     * @param $categoryId
     * @return bool
     */
    public function hasCustomSort($categoryId)
    {
        $categoryId = (int) $categoryId;
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->select('id')
            ->from('s_articles_sort', 'sort')
            ->where('categoryId = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $result = (bool) $builder->execute()->fetchColumn();

        return $result;
    }

    /**
     * Return last sort position for selected category
     *
     * @param $categoryId
     * @return mixed
     */
    public function getMaxPosition($categoryId)
    {
        $categoryId = (int) $categoryId;
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->select('MAX(position)')
            ->from('s_articles_sort', 'sort')
            ->where('categoryId = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $max = $builder->execute()->fetchColumn();

        return $max;
    }

    /**
     * Return product list for selected category
     *
     * @param $categoryId
     * @param $orderBy
     * @return mixed
     */
    public function getArticleImageQuery($categoryId, $orderBy)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();

        $builder->select(array(
                'sort.id as positionId',
                'product.id',
                'product.name',
                'images.img as path',
                'images.extension',
                'sort.position as position',
                'sort.position as oldPosition',
                'sort.pin as pin',
            ))
            ->from('s_articles', 'product')
            ->innerJoin('product', 's_articles_categories_ro', 'productCategory', 'productCategory.articleID = product.id')
            ->leftJoin('product', 's_articles_img', 'images', 'product.id = images.articleID')
            ->leftJoin('product', 's_articles_sort', 'sort', 'product.id = sort.articleId AND (sort.categoryId = productCategory.categoryID OR sort.categoryId IS NULL)')
            ->where('productCategory.categoryID = :categoryId')
            ->andWhere('images.main = 1')
            ->groupBy('product.id')
            ->orderBy('-sort.position', 'DESC')
            ->setParameter('categoryId', $categoryId);

        $this->sortUnsortedByDefault($builder, $orderBy);

        return $builder;
    }

    /**
     * Return total count of products in selected category
     *
     * @param $categoryId
     * @return mixed
     */
    public function getArticleImageCountQuery($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();

        $builder->select('COUNT(DISTINCT product.id) as Total')
            ->from('s_articles', 'product')
            ->innerJoin('product', 's_articles_categories_ro', 'productCategory', 'productCategory.articleID = product.id')
            ->leftJoin('product', 's_articles_img', 'images', 'product.id = images.articleID')
            ->where('productCategory.categoryID = :categoryId')
            ->andWhere('images.main = 1')
            ->setParameter('categoryId', $categoryId);

        return $builder;
    }

    /**
     * Sort products for current category by passed sort type
     *
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param integer $orderBy
     */
    private function sortUnsortedByDefault($builder, $orderBy)
    {
        switch ($orderBy) {
            case 1:
                $builder->addOrderBy('product.datum', 'DESC')
                    ->addOrderBy('product.changetime', 'DESC');
                break;
            case 2:
                $builder->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
                    ->addOrderBy('topSeller.sales', 'DESC')
                    ->addOrderBy('topSeller.article_id', 'DESC');
                break;
            case 3:
                $builder->addSelect('MIN(ROUND(defaultPrice.price * priceVariant.minpurchase * 1, 2)) as cheapest_price')
                    ->leftJoin('product', 's_articles_prices', 'defaultPrice', 'defaultPrice.articleID = product.id')
                    ->innerJoin('defaultPrice', 's_articles_details', 'priceVariant', 'priceVariant.id = defaultPrice.articledetailsID')
                    ->addOrderBy('cheapest_price', 'ASC');
                break;
            case 4:
                $builder->addSelect('MIN(ROUND(defaultPrice.price * priceVariant.minpurchase * 1, 2)) as cheapest_price')
                    ->leftJoin('product', 's_articles_prices', 'defaultPrice', 'defaultPrice.articleID = product.id')
                    ->innerJoin('defaultPrice', 's_articles_details', 'priceVariant', 'priceVariant.id = defaultPrice.articledetailsID')
                    ->addOrderBy('cheapest_price', 'DESC');
                break;
            case 5:
                $builder->addOrderBy('product.name', 'ASC');
                break;
            case 6:
                $builder->addOrderBy('product.name', 'DESC');
                break;
            case 9:
                $builder
                    ->innerJoin('product', 's_articles_details', 'variant', 'variant.id = product.main_detail_id')
                    ->addOrderBy('variant.instock', 'ASC');
                break;
            case 10:
                $builder
                    ->innerJoin('product', 's_articles_details', 'variant', 'variant.id = product.main_detail_id')
                    ->addOrderBy('variant.instock', 'DESC');
                break;

        }
    }

    /**
     * Sets pin value to 0
     *
     * @param $id - the id of the s_articles_sort record
     */
    public function unpinById($id)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->update('s_articles_sort')
                ->set('pin', 0)
                ->where('id = :id')
                ->setParameter('id', $id);

        $builder->execute();
    }

    /**
     * Deletes all records, which are unpinned, until the pinned record with max position
     *
     * @param $categoryId
     */
    public function deleteUnpinnedRecords($categoryId)
    {
        $maxPinPosition = $this->getMaxPinPosition($categoryId);
        if ($maxPinPosition === null) {
            $maxPinPosition = 0;
        }

        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->delete('s_articles_sort')
                ->where('categoryId = :categoryId')
                ->andWhere('position >= :maxPinPosition')
                ->andWhere('pin = 0')
                ->setParameter('categoryId', $categoryId)
                ->setParameter(':maxPinPosition', $maxPinPosition);

        $builder->execute();
    }

    /**
     * Returns the position of the pinned record with max position
     *
     * @param $categoryId
     * @return mixed
     */
    public function getMaxPinPosition($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->select(array('MAX(position) AS maxPinPosition'))
                ->from('s_articles_sort')
                ->where('categoryId = :categoryId')
                ->andWhere('pin = 1')
                ->orderBy('position', 'DESC')
                ->setParameter('categoryId', $categoryId);

        $maxPinPosition = $builder->execute()->fetchColumn();

        return $maxPinPosition;
    }

    /**
     * Returns product position for selected product
     *
     * @param $articleId
     * @return mixed
     */
    public function getPositionByArticleId($articleId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->select(array('position'))
            ->from('s_articles_sort')
            ->where('articleId = :articleId')
            ->setParameter('articleId', $articleId);
        $position = $builder->execute()->fetchColumn();

        return $position;
    }

    /**
     * Returns last deleted position of product for selected category
     *
     * @param $categoryId
     * @return mixed
     */
    public function getPositionOfDeletedProduct($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->select(array('swag_deleted_position'))
            ->from('s_categories_attributes')
            ->where('categoryID = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $deletedPosition = $builder->execute()->fetchColumn();

        return $deletedPosition;
    }

    /**
     * Delete custom sort flag for selected category
     *
     * @param $categoryId
     */
    public function resetDeletedPosition($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->update('s_categories_attributes')
            ->set('swag_deleted_position', 'null')
            ->where('categoryID = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $builder->execute();
    }

    /**
     * Update category attributes for selected category
     *
     * @param $categoryId
     * @param $baseSort
     * @param null $categoryLink
     * @param null $defaultSort
     */
    public function updateCategoryAttributes($categoryId, $baseSort, $categoryLink = null, $defaultSort = null)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->update('s_categories_attributes')
            ->set('swag_base_sort', $baseSort)
            ->where('categoryID = :categoryId')
            ->setParameter('categoryId', $categoryId);

        if ($categoryLink !== null) {
            $builder->set('swag_link', $categoryLink);
        }

        if ($defaultSort !== null) {
            $builder->set('swag_show_by_default', $defaultSort);
        }

        $builder->execute();
    }
}
