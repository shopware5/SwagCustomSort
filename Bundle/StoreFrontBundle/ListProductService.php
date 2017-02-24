<?php
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Shopware\SwagCustomSort\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\SwagCustomSort\Components\Sorting;

class ListProductService implements ListProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $coreService = null;

    /**
     * @var Sorting $sortingComponent
     */
    private $sortingComponent = null;

    /**
     * @param ListProductServiceInterface $coreService
     * @param Sorting $sortingComponent
     */
    public function __construct(ListProductServiceInterface $coreService, Sorting $sortingComponent)
    {
        $this->coreService = $coreService;
        $this->sortingComponent = $sortingComponent;
    }

    /**
     * @param string $number
     * @param Struct\ProductContextInterface $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * @param array $numbers
     * @param Struct\ProductContextInterface $context
     * @return Struct\ListProduct[]
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        $getSortedNumbers = $this->sortingComponent->sortByNumber($numbers);

        $products = $this->coreService->getList($getSortedNumbers, $context);

        return $products;
    }
}
