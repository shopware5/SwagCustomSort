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

namespace Shopware\SwagCustomSort\Sorter;

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\SwagCustomSort\Sorter\Sort\DragDropSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;

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
