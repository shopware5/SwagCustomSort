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

class Shopware_Controllers_Widgets_CustomSort extends Enlight_Controller_Action
{
    /**
     * Add template dir to bonus system widget templates
     */
    public function preDispatch()
    {
        $plugin = Shopware()->Plugins()->Frontend()->SwagCustomSort();

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() < 3;
        if ($isEmotion) {
            $template = 'emotion/';
        } else {
            $template = 'responsive/';
        }

        $this->View()->addTemplateDir($plugin->Path() . '/Views/' . $template);
    }

    public function defaultSortAction()
    {
        $sCategoryContent = $this->Request()->getParam('sCategoryContent');
        $sSort = $this->Request()->getParam('sSort');

        $hideFilters = $sCategoryContent['hideFilter'];
        $categoryId = (int) $sCategoryContent['id'];

        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');
        $showCustomSort = $categoryComponent->showCustomSortName($categoryId);
        $useDefaultSort = $categoryComponent->showCustomSortAsDefault($categoryId);
        $baseSort = $categoryComponent->getCategoryBaseSort($categoryId);
        if (($showCustomSort && !$hideFilters) || ($baseSort > 0 && $useDefaultSort)) {
            $showCustomSortOption = true;
        } else {
            $showCustomSortOption = false;
        }

        $this->View()->assign('showCustomSort', $showCustomSortOption);
        $this->View()->assign('sSort', $sSort);
    }
}
