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

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\Container;
use Shopware\SwagCustomSort\Bundle\StoreFrontBundle\ListProductService;
use Shopware\SwagCustomSort\Bundle\SearchBundle\SortProductSearch;
use Shopware\SwagCustomSort\Components\Sorting;

class StoreFrontBundle implements SubscriberInterface
{
    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var Sorting $sortingComponent
     */
    private $sortingComponent;

    /**
     * @param Container $container
     * @param Sorting $sortingComponent
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
            'Enlight_Bootstrap_AfterInitResource_shopware_search.product_search' => 'afterInitProductSearch'
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
