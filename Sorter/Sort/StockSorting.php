<?php
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Shopware\SwagCustomSort\Sorter\Sort;

use Shopware\Bundle\SearchBundle\Sorting\Sorting;

class StockSorting extends Sorting
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'stock';
    }
}
