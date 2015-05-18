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

        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        if (!$categoryComponent instanceof Listing) {
            return;
        }

        $view = $args->getSubject()->View();
        $hideFilters = $view->sCategoryContent['hideFilter'];
        $categoryId = $view->sCategoryContent['id'];
        $showCustomSort = $categoryComponent->showCustomSortName($categoryId);
        $baseSort = $categoryComponent->getCategoryBaseSort($categoryId);
        if (($showCustomSort || $baseSort > 0) && !$hideFilters) {
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

        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        if (!$categoryComponent instanceof Listing) {
            return;
        }

        $request = $args->getSubject()->Request();
        $categoryId = (int) $request->getParam('sCategory');
        $useDefaultSort = $categoryComponent->showCustomSortAsDefault($categoryId);
        $sortName = $categoryComponent->getFormattedSortName();
        $baseSort = $categoryComponent->getCategoryBaseSort($categoryId);
        if ((!$useDefaultSort && $baseSort) || empty($sortName) || $request->getParam('sSort') !== null) {
            return;
        }

        $request->setParam('sSort', \Shopware\SwagCustomSort\Sorter\SortFactory::DRAG_DROP_SORTING);
    }
}