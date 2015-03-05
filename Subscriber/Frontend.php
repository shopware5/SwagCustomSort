<?php

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\SwagCustomSort\Components\Listing;

class Frontend implements SubscriberInterface
{
    protected $bootstrap;

    public function __construct(\Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onPostDispatchSecureListing'
        );
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecureListing(Enlight_Event_EventArgs $args)
    {
        //TODO: check license

        $customSortComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        if (!$customSortComponent instanceof Listing) {
            return;
        }

        $view = $args->getSubject()->View();
        $categoryId = $view->sCategoryInfo['id'];

        $showCustomSort = $customSortComponent->showCustomSortName($categoryId);
        if ($showCustomSort) {
            $view->showCustomSort = true;
            $this->extendsTemplate($view, 'frontend/listing/actions/action-sorting.tpl');
        }
    }

    protected function extendsTemplate($view, $templatePath)
    {
        $version = Shopware()->Shop()->getTemplate()->getVersion();
        if ($version >= 3) {
            $view->addTemplateDir($this->bootstrap->Path() . 'Views/responsive/');
        } else {
            $view->addTemplateDir($this->bootstrap->Path() . 'Views/emotion/');
            $view->extendsTemplate($templatePath);
        }
    }
}