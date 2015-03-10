<?php

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    protected $bootstrap;

    public function __construct(\Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecureBackendIndex'
        );
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecureBackendIndex(Enlight_Event_EventArgs $args)
    {
        //TODO: check license

        $view = $args->getSubject()->View();

        $view->addTemplateDir($this->bootstrap->Path() . 'Views/');
        $view->extendsTemplate('backend/custom_sort/header.tpl');
    }
}