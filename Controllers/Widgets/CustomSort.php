<?php

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
        $hideFilters = $this->Request()->getParam('hideFilter');

        $categoryComponent = Shopware()->Container()->get('swagcustomsort.listing_component');

        $showCustomSort = $categoryComponent->showCustomSortName();
        if ($showCustomSort && !$hideFilters) {
            $showCustomSortOption = true;
        } else {
            $showCustomSortOption = false;
        }

        $this->View()->showCustomSort = $showCustomSortOption;
    }

}