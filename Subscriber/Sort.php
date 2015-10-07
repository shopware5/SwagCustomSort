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
use Enlight_Controller_Request_Request as Request;
use Enlight_Event_EventArgs;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\Model\ModelManager;
use Shopware\SwagCustomSort\Components\Sorting;
use Shopware\SwagCustomSort\Sorter\SortFactory;
use \Shopware\SwagCustomSort\Sorter\SortDBAL\Handler\DragDropHandler;
use Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap as PluginBootstrap;
use Shopware\SwagCustomSort\Components\Listing;

class Sort implements SubscriberInterface
{
    /**
     * @var PluginBootstrap $bootstrap
     */
    private $bootstrap;

    /**
     * @var ModelManager $em
     */
    protected $em;

    /**
     * @var Sorting $sortingComponent
     */
    private $sortingComponent;

    /**
     * @param PluginBootstrap $bootstrap
     * @param ModelManager $em
     * @param Sorting $sortingComponent
     */
    public function __construct(PluginBootstrap $bootstrap, ModelManager $em, Sorting $sortingComponent)
    {
        $this->bootstrap = $bootstrap;
        $this->em = $em;
        $this->sortingComponent = $sortingComponent;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_SearchBundle_Create_Listing_Criteria' => 'onCreateListingCriteria',
            'Shopware_SearchBundle_Create_Ajax_Listing_Criteria' => 'onCreateListingCriteria',
            'Shopware_SearchBundle_Create_Product_Navigation_Criteria' => 'onCreateListingCriteria',
            'Shopware_SearchBundleDBAL_Collect_Sorting_Handlers' => 'onCollectSortingHandlers'
        ];
    }

    /**
     * When a Criteria is created, check for plugin sorting options. If plugin sorting options exist, add them.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onCreateListingCriteria(Enlight_Event_EventArgs $args)
    {
        /** @var Request $request */
        $request = $args->get('request');
        /** @var Criteria $criteria */
        $criteria = $args->get('criteria');

        $allowedActions = ['index', 'ajaxListing', 'productNavigation'];

        //Don't apply custom sort if we are not in category listing
        if (!in_array($request->getActionName(), $allowedActions)) {
            return;
        }

        /** @var Listing $categoryComponent */
        $categoryComponent = $this->bootstrap->get('swagcustomsort.listing_component');
        if (!$categoryComponent instanceof Listing) {
            return;
        }

        $categoryId = (int)$request->getParam('sCategory');
        $useDefaultSort = $categoryComponent->showCustomSortAsDefault($categoryId);
        $sortName = $categoryComponent->getFormattedSortName();
        $baseSort = $categoryComponent->getCategoryBaseSort($categoryId);
        $sortId = $request->getParam('sSort');
        if ((!$useDefaultSort && $baseSort) || empty($sortName) || ($sortId !== null && $sortId != SortFactory::DRAG_DROP_SORTING)) {
            return;
        }

        $criteria->resetSorting();
        $request->setParam('sSort', SortFactory::DRAG_DROP_SORTING);

        $page = (int) $request->getParam('sPage');
        $offset = (int) $criteria->getOffset();
        $limit = (int) $criteria->getLimit();

        //Get all sorted products for current category and set them in components for further sorting
        $linkedCategoryId = $categoryComponent->getLinkedCategoryId($categoryId);
        $sortedProducts = $this->em->getRepository('\Shopware\CustomModels\CustomSort\ArticleSort')
            ->getSortedProducts($categoryId, $linkedCategoryId);
        $this->sortingComponent->setSortedProducts($sortedProducts);

        //Get new offset based on page so we can get correct position of unsorted products
        $newOffset = $this->sortingComponent->getOffset($offset, $page, $limit);

        $this->sortingComponent->setOffsetAndLimit($offset, $limit);

        $criteria->offset($newOffset);

        $sorter = new SortFactory($request, $criteria);
        $sorter->addSort();
    }

    /**
     * Register plugin sorting handlers.
     *
     * @return DragDropHandler
     */
    public function onCollectSortingHandlers()
    {
        return new DragDropHandler($this->sortingComponent);
    }
}
