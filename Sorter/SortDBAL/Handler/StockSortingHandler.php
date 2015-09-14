<?php

namespace Shopware\SwagCustomSort\Sorter\SortDBAL\Handler;

use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;

use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\SwagCustomSort\Sorter\Sort\StockSorting;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\SortingHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StockSortingHandler implements SortingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return ($sorting instanceof StockSorting);
    }

    /**
     * {@inheritdoc}
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {

        $query->addOrderBy('variant.instock', $sorting->getDirection())
            ->addOrderBy('product.id', 'DESC');
    }
}