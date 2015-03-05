<?php

namespace Shopware\SwagCustomSort\Components;

class Listing
{

    private $config = null;

    public function __construct(Shopware_Components_Config $config) {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function showCustomSortName()
    {
        $sortName = $this->getFormattedSortName();
        if (empty($sortName)) {
            return false;
        }

        $hasCustomSort = $this->hasCustomSort();
        if ($hasCustomSort) {
            return true;
        }

        return false;
    }

    public function getFormattedSortName()
    {
        $formattedName = $this->getSortName();

        return trim($formattedName);
    }

    public function getSortName()
    {
        $name = $this->getConfig()->get('swagCustomSortName');

        return $name;
    }

    public function hasCustomSort()
    {
//        $isSynced = $this->isSynced();
//        if ($isSynced) {
//            return true;
//        }
//
//        $hasOwnSort = $this->hasOwnSort();
//        if ($hasOwnSort) {
//            return true;
//        }

        return false;
    }
}