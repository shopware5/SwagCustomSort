<?php
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Shopware\SwagCustomSort\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\SwagCustomSort\Components\Sorting;

/**
 * @category  Shopware
 * @package   ShopwarePlugins\SwagCustomSort\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SortProductSearch implements ProductSearchInterface
{
    /**
     * @var ProductSearchInterface $productSearch
     */
    private $productSearch;

    /**
     * @var Sorting $sortingComponent
     */
    private $sortingComponent;

    /**
     * @param ProductSearchInterface $productSearch
     * @param Sorting $sortingComponent
     */
    public function __construct(ProductSearchInterface $productSearch, Sorting $sortingComponent)
    {
        $this->productSearch = $productSearch;
        $this->sortingComponent = $sortingComponent;
    }

    /**
     * Creates a search request on the internal search gateway to
     * get the product result for the passed criteria object.
     *
     * @param Criteria $criteria
     * @param Struct\ProductContextInterface $context
     * @return ProductSearchResult
     */
    public function search(Criteria $criteria, Struct\ProductContextInterface $context)
    {
        $productSearchResult = $this->productSearch->search($criteria, $context);

        $facets = $productSearchResult->getFacets();

        $totalCount = $productSearchResult->getTotalCount() + $this->sortingComponent->getTotalCount();

        return new ProductNumberSearchResult(
            $productSearchResult->getProducts(),
            $totalCount,
            $facets
        );
    }
}
