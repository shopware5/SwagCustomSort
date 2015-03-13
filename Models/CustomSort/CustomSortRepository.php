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
            ))
            ->from('s_articles', 'product')
            ->innerJoin('product', 's_articles_details', 'variant', 'variant.id = product.main_detail_id')
            ->innerJoin('product', 's_articles_prices', 'defaultPrice', 'defaultPrice.articleID = product.id')
            ->innerJoin('defaultPrice', 's_articles_details', 'priceVariant', 'priceVariant.id = defaultPrice.articledetailsID')
            ->innerJoin('product', 's_articles_categories_ro', 'productCategory', 'productCategory.articleID = product.id')
            ->leftJoin('product', 's_articles_img', 'images', 'product.id = images.articleID')
            ->leftJoin('product', 's_articles_sort', 'sort', 'product.id = sort.articleId')
            ->where('productCategory.categoryID = :categoryId')
            ->andWhere('images.main = 1')
            ->groupBy('product.id')
            ->orderBy('-sort.position', 'DESC')
            ->setParameter('categoryId', $categoryId)
            ;

        return $builder;
    }
}
