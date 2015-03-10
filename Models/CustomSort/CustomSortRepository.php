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
        $builder->select(array('DISTINCT image.img as path', 'image.extension', 'article.name'))
            ->from('s_articles_img', 'image')
            ->leftJoin('image', 's_articles_categories_ro', 'category', 'category.articleID = image.articleID')
            ->leftJoin('category', 's_articles', 'article', 'article.id = category.articleID')
            ->leftJoin('article', 's_articles_details', 'details', 'details.articleID = article.id')
            ->leftJoin('article', 's_articles_prices', 'price', 'price.articleID = article.id')
            ->where('category.categoryID = :categoryId')
            ->andWhere('image.main = 1')
            ->orderBy('price.price', 'ASC')
            ->setParameter('categoryId', $categoryId)
        ;

        return $builder;
    }
}
