<?php

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\SwagCustomSort\Sorter\SortFactory;
use \Shopware\SwagCustomSort\Sorter\SortDBAL\Handler\DragDropHandler;

class Sort implements SubscriberInterface
{
    protected $bootstrap;

    public function __construct(\Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Shopware_SearchBundle_Create_Listing_Criteria'         => 'onCreateListingCriteria',
            'Shopware_SearchBundleDBAL_Collect_Sorting_Handlers'    => 'onCollectSortingHandlers'
        );
    }

    /**
     * When a Criteria is created, check for plugin sorting options. If plugin sorting options exist, add them.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onCreateListingCriteria(Enlight_Event_EventArgs $args)
    {
        //TODO: check license

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
        //TODO: check license

        $args->setReturn(new DragDropHandler());
        return $args->getReturn();
    }
}