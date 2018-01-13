<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagCustomSort\Sorter\SortDBAL\Handler;

use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\SwagCustomSort\Sorter\Sort\RatingSorting;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class RatingSortingHandler implements SortingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return $sorting instanceof RatingSorting;
    }

    /**
     * {@inheritdoc}
     */
    public function generateSorting(SortingInterface $sorting, QueryBuilder $query, ShopContextInterface $context)
    {
        $query->addSelect('(SUM(vote.points) / COUNT(vote.id)) as votes')
            ->leftJoin('product', 's_articles_vote', 'vote', 'product.id = vote.articleID')
            ->addOrderBy('votes', 'DESC')
            ->addOrderBy('product.id', 'DESC')
            ->groupBy('product.id');
    }
}
