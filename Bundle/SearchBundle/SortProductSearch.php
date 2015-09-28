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
