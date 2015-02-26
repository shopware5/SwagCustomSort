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
    }

}