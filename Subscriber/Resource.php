<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\SwagCustomSort\Components\Listing;
use Shopware\SwagCustomSort\Components\Sorting;
use Shopware_Components_Config as ShopwareConfig;

class Resource implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ShopwareConfig
     */
    private $config;

    /**
     * @param ModelManager   $modelManager
     * @param ShopwareConfig $config
     */
    public function __construct(ModelManager $modelManager, ShopwareConfig $config)
    {
        $this->modelManager = $modelManager;
        $this->config = $config;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Bootstrap_InitResource_swagcustomsort.listing_component' => 'onInitListingComponent',
            'Enlight_Bootstrap_InitResource_swagcustomsort.sorting_component' => 'onInitSortingComponent',
        ];
    }

    /**
     * returns new instance of Listing
     *
     * @return Listing
     */
    public function onInitListingComponent()
    {
        return new Listing(
            $this->config,
            $this->modelManager
        );
    }

    /**
     * returns new instance of Sorting
     *
     * @return Sorting
     */
    public function onInitSortingComponent()
    {
        return new Sorting();
    }
}
