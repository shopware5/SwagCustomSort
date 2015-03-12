<?php

namespace Shopware\SwagCustomSort\Sorter;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\SwagCustomSort\Sorter\Sort\DragDropSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;

class SortFactory
{
    const DRAG_DROP_SORTING = 8;

    private $request = null;

    private $criteria = null;

    public function __construct(Enlight_Controller_Request_RequestHttp $request, Criteria $criteria)
    {
        $this->request  = $request;
        $this->criteria = $criteria;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function addSort()
    {
        $sortParam = $this->getRequest()->getParam('sSort');
        switch ($sortParam) {
            case self::DRAG_DROP_SORTING:
                $this->getCriteria()->addSorting(
                    new DragDropSorting(SortingInterface::SORT_DESC)
                );
                break;
            default:
                return;
        }
    }
}
