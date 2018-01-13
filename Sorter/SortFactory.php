<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagCustomSort\Sorter;

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\SwagCustomSort\Sorter\Sort\DragDropSorting;

class SortFactory
{
    const DRAG_DROP_SORTING = 8;

    private $request = null;

    private $criteria = null;

    public function __construct(Request $request, Criteria $criteria)
    {
        $this->request = $request;
        $this->criteria = $criteria;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Criteria|null
     */
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
