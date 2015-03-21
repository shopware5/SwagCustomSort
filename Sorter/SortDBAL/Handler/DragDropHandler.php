<?php

namespace Shopware\SwagCustomSort\Sorter\SortDBAL\Handler;

use \Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use \Shopware\Bundle\SearchBundle\SortingInterface;
use \Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use \Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory;
use \Shopware\Bundle\SearchBundleDBAL\SortingHandler\ReleaseDateSortingHandler;
use \Shopware\Bundle\SearchBundleDBAL\SortingHandler\PopularitySortingHandler;
use \Shopware\Bundle\SearchBundleDBAL\SortingHandler\PriceSortingHandler;
use \Shopware\Bundle\SearchBundleDBAL\SortingHandler\ProductNameSortingHandler;
use \Shopware\Bundle\SearchBundleDBAL\SortingHandler\SearchRankingSortingHandler;

class DragDropHandler implements SortingHandlerInterface
{
    public function supportsSorting(SortingInterface $sorting)
    {
        return ($sorting instanceof \Shopware\SwagCustomSort\Sorter\Sort\DragDropSorting);
    }

    public function generateSorting(SortingInterface $sorting, QueryBuilder $query, ShopContextInterface $context)
    {
        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        $categoryId = Shopware()->Front()->Request()->getParam('sCategory');
        $linkedCategoryId = $categoryComponent->getLinkedCategoryId($categoryId);

        //apply 'plugin' order
        $query->leftJoin(
            'productCategory',
            's_articles_sort',
            'customSort',
            'customSort.articleId = productCategory.articleID AND (customSort.categoryId = productCategory.categoryID OR customSort.categoryId IS NULL)'
        );

        if ($linkedCategoryId) {
            $query->andWhere('customSort.categoryId = :sortCategoryId OR customSort.categoryId IS NULL');
            $query->setParameter('sortCategoryId', $linkedCategoryId);
        }

        $query->addOrderBy('-customSort.position', $sorting->getDirection());

        //for records with no 'plugin' order data use the default shopware order
        $handlerData = $this->getDefaultData();
        if ($handlerData) {
            $sorting->setDirection($handlerData['direction']);
            $handlerData['handler']->generateSorting($sorting, $query, $context);
        }
    }

    protected function getDefaultData()
    {
        $defaultSort = Shopware()->Config()->get('defaultListingSorting');

        switch ($defaultSort) {
            case StoreFrontCriteriaFactory::SORTING_RELEASE_DATE:
                return array(
                    'handler' => new ReleaseDateSortingHandler(),
                    'direction' => 'DESC'
                );
            case StoreFrontCriteriaFactory::SORTING_POPULARITY:
                return array(
                    'handler' => new PopularitySortingHandler(),
                    'direction' => 'DESC'
                );
            case StoreFrontCriteriaFactory::SORTING_CHEAPEST_PRICE:
                return array(
                    'handler' => new PriceSortingHandler(Shopware()->Container()->get('shopware_searchdbal.search_price_helper_dbal')),
                    'direction' => 'ASC'
                );
            case StoreFrontCriteriaFactory::SORTING_HIGHEST_PRICE:
                return array(
                    'handler' => new PriceSortingHandler(Shopware()->Container()->get('shopware_searchdbal.search_price_helper_dbal')),
                    'direction' => 'DESC'
                );
            case StoreFrontCriteriaFactory::SORTING_PRODUCT_NAME_ASC:
                return array(
                    'handler' => new ProductNameSortingHandler(),
                    'direction' => 'ASC'
                );
            case StoreFrontCriteriaFactory::SORTING_PRODUCT_NAME_DESC:
                return array(
                    'handler' => new ProductNameSortingHandler(),
                    'direction' => 'DESC'
                );
            case StoreFrontCriteriaFactory::SORTING_SEARCH_RANKING:
                return array(
                    'handler' => new SearchRankingSortingHandler(),
                    'direction' => 'DESC'
                );
        }
    }
}