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
use Shopware\SwagCustomSort\Sorter\SortDBAL\Handler\RatingSortingHandler;
use Shopware\SwagCustomSort\Sorter\SortDBAL\Handler\StockSortingHandler;
use Shopware\SwagCustomSort\Components\Listing;
use Shopware\SwagCustomSort\Sorter\Sort\DragDropSorting;

class DragDropHandler implements SortingHandlerInterface
{

    const SORTING_STOCK_ASC = 9;
    const SORTING_STOCK_DESC = 10;

    /**
     * @param SortingInterface $sorting
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return ($sorting instanceof DragDropSorting);
    }

    /**
     * @param SortingInterface $sorting
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     * @throws \Exception
     */
    public function generateSorting(SortingInterface $sorting, QueryBuilder $query, ShopContextInterface $context)
    {
        /** @var Listing $categoryComponent */
        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        $categoryId = Shopware()->Front()->Request()->getParam('sCategory');
        $linkedCategoryId = $categoryComponent->getLinkedCategoryId($categoryId);
        $hasCustomSort = $categoryComponent->hasCustomSort($categoryId);
        $baseSort = $categoryComponent->getCategoryBaseSort($categoryId);
        if ($hasCustomSort || $baseSort > 0) {
            $baseSorting = $categoryComponent->getCategoryBaseSort($categoryId);
        } else {
            $baseSorting = Shopware()->Config()->get('defaultListingSorting');
        }

        //apply 'plugin' order
        if ($linkedCategoryId) {
            $query->leftJoin(
                'productCategory',
                's_articles_sort',
                'customSort',
                'customSort.articleId = productCategory.articleID'
            );

            $query->andWhere('customSort.categoryId = :sortCategoryId OR customSort.categoryId IS NULL');
            $query->setParameter('sortCategoryId', $linkedCategoryId);
        } else {
            $query->leftJoin(
                'productCategory',
                's_articles_sort',
                'customSort',
                'customSort.articleId = productCategory.articleID AND (customSort.categoryId = productCategory.categoryID OR customSort.categoryId IS NULL)'
            );
        }

        $query->addOrderBy('-customSort.position', $sorting->getDirection());

        //for records with no 'plugin' order data use the default shopware order
        $handlerData = $this->getDefaultData($baseSorting);
        if ($handlerData) {
            $sorting->setDirection($handlerData['direction']);
            $handlerData['handler']->generateSorting($sorting, $query, $context);
        }
    }

    /**
     * @param $defaultSort
     * @return array
     */
    protected function getDefaultData($defaultSort)
    {
        switch ($defaultSort) {
            case StoreFrontCriteriaFactory::SORTING_RELEASE_DATE:
                return [
                    'handler' => new ReleaseDateSortingHandler(),
                    'direction' => 'DESC'
                ];
            case StoreFrontCriteriaFactory::SORTING_POPULARITY:
                return [
                    'handler' => new PopularitySortingHandler(),
                    'direction' => 'DESC'
                ];
            case StoreFrontCriteriaFactory::SORTING_CHEAPEST_PRICE:
                return [
                    'handler' => new PriceSortingHandler(Shopware()->Container()->get('shopware_searchdbal.search_price_helper_dbal')),
                    'direction' => 'ASC'
                ];
            case StoreFrontCriteriaFactory::SORTING_HIGHEST_PRICE:
                return [
                    'handler' => new PriceSortingHandler(Shopware()->Container()->get('shopware_searchdbal.search_price_helper_dbal')),
                    'direction' => 'DESC'
                ];
            case StoreFrontCriteriaFactory::SORTING_PRODUCT_NAME_ASC:
                return [
                    'handler' => new ProductNameSortingHandler(),
                    'direction' => 'ASC'
                ];
            case StoreFrontCriteriaFactory::SORTING_PRODUCT_NAME_DESC:
                return [
                    'handler' => new ProductNameSortingHandler(),
                    'direction' => 'DESC'
                ];
            case StoreFrontCriteriaFactory::SORTING_SEARCH_RANKING:
                return [
                    'handler' => new RatingSortingHandler(),
                    'direction' => 'DESC'
                ];
            case DragDropHandler::SORTING_STOCK_ASC:
                return [
                    'handler' => new StockSortingHandler(),
                    'direction' => 'ASC'
                ];
            case DragDropHandler::SORTING_STOCK_DESC:
                return [
                    'handler' => new StockSortingHandler(),
                    'direction' => 'DESC'
                ];
        }
    }
}
