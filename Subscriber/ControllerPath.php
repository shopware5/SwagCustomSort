<?php

namespace Shopware\CustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;

class ControllerPath implements SubscriberInterface
{

    protected $bootstrap;

    public function __construct(\Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_CustomSort' => 'onGetCustomSortControllerPath'
        );
    }

    /**
    * This function is responsible to resolve the backend / frontend controller path.
    *
    * @param  \Enlight_Event_EventArgs $args
    * @return string
    */
    public function onGetCustomSortControllerPath(\Enlight_Event_EventArgs $args)
    {
        switch ($args->getName()) {
            case 'Enlight_Controller_Dispatcher_ControllerPath_Backend_CustomSort':
                return $this->bootstrap->Path() . 'Controllers/Backend/CustomSort.php';
        }
    }

}