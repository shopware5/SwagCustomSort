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
}
