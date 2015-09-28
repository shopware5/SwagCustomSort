<?php

namespace Shopware\SwagCustomSort\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

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

    public function get($number, Struct\ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);
        return array_shift($products);
    }

    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        $getSortedNumbers = $this->sortingComponent->sortByNumber($numbers);

        $products = $this->coreService->getList($getSortedNumbers, $context);

        return $products;
    }
}
