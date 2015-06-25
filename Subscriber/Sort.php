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
use Enlight_Event_EventArgs;
use Shopware\SwagCustomSort\Sorter\SortFactory;
use \Shopware\SwagCustomSort\Sorter\SortDBAL\Handler\DragDropHandler;

class Sort implements SubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Shopware_SearchBundle_Create_Listing_Criteria' => 'onCreateListingCriteria',
            'Shopware_SearchBundleDBAL_Collect_Sorting_Handlers' => 'onCollectSortingHandlers'
        );
    }

    /**
     * When a Criteria is created, check for plugin sorting options. If plugin sorting options exist, add them.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onCreateListingCriteria(Enlight_Event_EventArgs $args)
    {
        $request = $args->get('request');
        $criteria = $args->get('criteria');
        $sorter = new SortFactory($request, $criteria);
        $sorter->addSort();
    }

    /**
     * Register plugin sorting handlers.
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return DragDropHandler
     */
    public function onCollectSortingHandlers(Enlight_Event_EventArgs $args)
    {
        $args->setReturn(new DragDropHandler());

        return $args->getReturn();
    }
}
