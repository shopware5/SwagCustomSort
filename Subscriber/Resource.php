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
use Shopware\Components\Model\ModelManager;
use Shopware\SwagCustomSort\Components\Listing;
use Shopware_Components_Config as ShopwareConfig;

class Resource implements SubscriberInterface
{
    /**
     * @var ModelManager $modelManager
     */
    private $modelManager;

    /**
     * @var ShopwareConfig
     */
    private $config;

    /**
     * @param ModelManager $modelManager
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
            'Enlight_Bootstrap_InitResource_swagcustomsort.listing_component' => 'onInitListingComponent'
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
}
