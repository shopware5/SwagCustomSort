<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\SwagCustomSort\Bundle\SearchBundle\SortProductSearch;
use Shopware\SwagCustomSort\Bundle\StoreFrontBundle\ListProductService;
use Shopware\SwagCustomSort\Components\Sorting;
use Symfony\Component\DependencyInjection\Container;

class StoreFrontBundle implements SubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Sorting
     */
    private $sortingComponent;

    /**
     * @param Container $container
     * @param Sorting   $sortingComponent
     */
    public function __construct(Container $container, Sorting $sortingComponent)
    {
        $this->container = $container;
        $this->sortingComponent = $sortingComponent;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Bootstrap_AfterInitResource_shopware_storefront.list_product_service' => 'afterInitListProductService',
            'Enlight_Bootstrap_AfterInitResource_shopware_search.product_search' => 'afterInitProductSearch',
        ];
    }

    public function afterInitListProductService()
    {
        $coreService = $this->container->get('shopware_storefront.list_product_service');
        $newService = new ListProductService($coreService, $this->sortingComponent);

        Shopware()->Container()->set('shopware_storefront.list_product_service', $newService);
    }

    public function afterInitProductSearch()
    {
        $coreProductSearch = $this->container->get('shopware_search.product_search');
        $sortProductSearch = new SortProductSearch($coreProductSearch, $this->sortingComponent);
        $this->container->set('shopware_search.product_search', $sortProductSearch);
    }
}
