<?php

namespace Shopware\SwagCustomSort\Components;

class Listing
{
    /**
     * @var Shopware_Components_Config
     */
    private $config = null;

    /**
     * @var /Shopware\Components\Model\ModelManager
     */
    private $em = null;

    private $categoryAttributesRepo = null;

    private $categoryRepo = null;

    private $customSortRepo = null;

    public function __construct(Shopware_Components_Config $config, \Shopware\Components\Model\ModelManager $em) {
        $this->config = $config;
        $this->em = $em;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function getCategoryAttributesRepository()
    {
        if ($this->categoryAttributesRepo === null) {
            $this->categoryAttributesRepo = $this->getEntityManager()->getRepository('Shopware\Models\Attribute\Category');
        }

        return $this->categoryAttributesRepo;
    }

    public function getCategoryRepository()
    {
        if ($this->categoryRepo === null) {
            $this->categoryRepo = $this->getEntityManager()->getRepository('Shopware\Models\Category\Category');
        }

        return $this->categoryRepo;
    }

    public function getCustomSortRepository()
    {
        if ($this->customSortRepo === null) {
            $this->customSortRepo = $this->getEntityManager()->getRepository('Shopware\CustomModels\CustomSort\ArticleSort');
        }

        return $this->customSortRepo;
    }

    public function showCustomSortName($categoryId)
    {
        $sortName = $this->getFormattedSortName();
        if (empty($sortName)) {
            return false;
        }

        $hasCustomSort = $this->hasCustomSort($categoryId);
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

    public function hasCustomSort($categoryId)
    {
        $isLinked = $this->isLinked($categoryId);
        if ($isLinked) {
            return true;
        }

        $hasOwnSort = $this->hasOwnSort($categoryId);
        if ($hasOwnSort) {
            return true;
        }

        return false;
    }

    public function isLinked($categoryId)
    {
        /* @var \Shopware\Models\Attribute\Category $categoryAttributes */
        $categoryAttributes = $this->getCategoryAttributesRepository()->findOneBy(array('categoryId' => $categoryId));
        if (!$categoryAttributes instanceof \Shopware\Models\Attribute\Category) {
            return false;
        }

        $linkedCategoryId = $categoryAttributes->getSwagLink();
        if ($linkedCategoryId === null) {
            return false;
        }

        /* @var \Shopware\Models\Category\Category $category */
        $category = $this->getCategoryRepository()->find($linkedCategoryId);
        if (!$category instanceof \Shopware\Models\Category\Category) {
            return false;
        }

        return true;
    }

    public function hasOwnSort($categoryId)
    {
        return $this->getCustomSortRepository()->hasCustomSort($categoryId);
    }
}