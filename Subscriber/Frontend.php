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
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onPostDispatchSecureListing',
            'Enlight_Controller_Action_PreDispatch_Frontend_Listing' => 'onPreDispatchListing'
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

        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();

        $categoryId = $request->getParam('sCategory');
        $hideFilters = $view->sCategoryContent['hideFilter'];
        $showCustomSort = $customSortComponent->showCustomSortName($categoryId);
        if ($showCustomSort && !$hideFilters) {
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

    public function onPreDispatchListing(Enlight_Event_EventArgs $args)
    {
        //TODO: check license

        $customSortComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        if (!$customSortComponent instanceof Listing) {
            return;
        }

        $request = $args->getSubject()->Request();
        $categoryId = $request->getParam('sCategory');
        $useDefaultSort = $customSortComponent->showCustomSortAsDefault($categoryId);
        $sortName = $customSortComponent->getFormattedSortName();
        if ((!$useDefaultSort && empty($sortName)) || $request->getParam('sSort') !== null) {
            return;
        }

        $request->setParam('sSort', \Shopware\SwagCustomSort\Sorter\SortFactory::DRAG_DROP_SORTING);
    }
}