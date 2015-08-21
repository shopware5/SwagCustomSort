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
use Shopware\SwagCustomSort\Components\Listing;
use Shopware\SwagCustomSort\Sorter\SortFactory;

class Frontend implements SubscriberInterface
{
    /**
     * @var string $bootstrapPath
     */
    protected $bootstrapPath;

    public function __construct($bootstrapPath)
    {
        $this->bootstrapPath = $bootstrapPath;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onPostDispatchSecureListing',
            'Enlight_Controller_Action_PreDispatch_Frontend_Listing' => 'onPreDispatchListing'
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecureListing(Enlight_Event_EventArgs $args)
    {
        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        if (!$categoryComponent instanceof Listing) {
            return;
        }

        /** @var \Enlight_View_Default $view */
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

    /**
     * @param \Enlight_View_Default $view
     * @param $templatePath
     */
    protected function extendsTemplate($view, $templatePath)
    {
        $version = Shopware()->Shop()->getTemplate()->getVersion();
        if ($version >= 3) {
            $view->addTemplateDir($this->bootstrapPath . 'Views/responsive/');
        } else {
            $view->addTemplateDir($this->bootstrapPath . 'Views/emotion/');
            $view->extendsTemplate($templatePath);
        }
    }

    public function onPreDispatchListing(Enlight_Event_EventArgs $args)
    {
        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        if (!$categoryComponent instanceof Listing) {
            return;
        }

        /** @var \Enlight_Controller_Request_RequestHttp $request */
        $request = $args->getSubject()->Request();
        $categoryId = (int) $request->getParam('sCategory');
        $useDefaultSort = $categoryComponent->showCustomSortAsDefault($categoryId);
        $sortName = $categoryComponent->getFormattedSortName();
        $baseSort = $categoryComponent->getCategoryBaseSort($categoryId);
        if ((!$useDefaultSort && $baseSort) || empty($sortName) || $request->getParam('sSort') !== null) {
            return;
        }

        $request->setParam('sSort', SortFactory::DRAG_DROP_SORTING);
    }
}
