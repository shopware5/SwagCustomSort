<?php

namespace Shopware\CustomModels\CustomSort;

use Shopware\Components\Model\ModelRepository;

class CustomSortRepository extends ModelRepository
{
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

    public function getArticleImageQuery($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();

        $builder
            ->select(array(
                'sort.id as positionId',
                'product.id',
                'product.name',
                'images.img as path',
                'images.extension',
                'MIN(ROUND(defaultPrice.price * priceVariant.minpurchase * 1, 2)) as cheapest_price',
                'sort.position as position',
                'sort.position as oldPosition',
                'sort.pin as pin',
            ))
            ->from('s_articles', 'product')
            ->innerJoin('product', 's_articles_details', 'variant', 'variant.id = product.main_detail_id')
            ->innerJoin('product', 's_articles_prices', 'defaultPrice', 'defaultPrice.articleID = product.id')
            ->innerJoin('defaultPrice', 's_articles_details', 'priceVariant', 'priceVariant.id = defaultPrice.articledetailsID')
            ->innerJoin('product', 's_articles_categories_ro', 'productCategory', 'productCategory.articleID = product.id')
            ->leftJoin('product', 's_articles_img', 'images', 'product.id = images.articleID')
            ->leftJoin('product', 's_articles_sort', 'sort', 'product.id = sort.articleId AND (sort.categoryId = productCategory.categoryID OR sort.categoryId IS NULL)')
            ->where('productCategory.categoryID = :categoryId')
            ->andWhere('images.main = 1')
            ->groupBy('product.id')
            ->orderBy('-sort.position', 'DESC')
            ->setParameter('categoryId', $categoryId)
            ;

        return $builder;
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

    public function getCategoriesByLinkedCategoryId($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->select(array('categoryID'))
            ->from('s_categories_attributes')
            ->where('swag_link = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $ids = $builder->execute()->fetchAll();

        return $ids;
    }

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

    public function resetDeletedPosition($categoryId)
    {
        $builder = $this->getEntityManager()->getDBALQueryBuilder();
        $builder->update('s_categories_attributes')
            ->set('swag_deleted_position', 'null')
            ->where('categoryID = :categoryId')
            ->setParameter('categoryId', $categoryId);

//        $builder->execute();
    }
}
