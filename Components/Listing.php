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

namespace Shopware\SwagCustomSort\Components;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\Category as CategoryAttributes;
use Shopware\Models\Category\Category;
use Shopware_Components_Config as Config;

class Listing
{
    /**
     * @var Config
     */
    private $config = null;

    /**
     * @var ModelManager
     */
    private $em = null;

    private $categoryAttributesRepo = null;

    private $categoryRepo = null;

    private $customSortRepo = null;

    public function __construct(Config $config, ModelManager $em)
    {
        $this->config = $config;
        $this->em = $em;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return ModelManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getCategoryAttributesRepository()
    {
        if ($this->categoryAttributesRepo === null) {
            $this->categoryAttributesRepo = $this->getEntityManager()->getRepository('Shopware\Models\Attribute\Category');
        }

        return $this->categoryAttributesRepo;
    }

    /**
     * @return null|\Shopware\Models\Category\Repository
     */
    public function getCategoryRepository()
    {
        if ($this->categoryRepo === null) {
            $this->categoryRepo = $this->getEntityManager()->getRepository('Shopware\Models\Category\Category');
        }

        return $this->categoryRepo;
    }

    /**
     * @return null|\Shopware\CustomModels\CustomSort\CustomSortRepository
     */
    public function getCustomSortRepository()
    {
        if ($this->customSortRepo === null) {
            $this->customSortRepo = $this->getEntityManager()->getRepository('Shopware\CustomModels\CustomSort\ArticleSort');
        }

        return $this->customSortRepo;
    }

    /**
     * @param $categoryId
     * @return bool
     */
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

    /**
     * @return string
     */
    public function getFormattedSortName()
    {
        $formattedName = $this->getSortName();

        return trim($formattedName);
    }

    /**
     * @return null
     */
    public function getSortName()
    {
        $name = $this->getConfig()->get('swagCustomSortName');

        return $name;
    }

    /**
     * @param $categoryId
     * @return bool
     */
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

    /**
     * @param $categoryId
     * @return bool
     */
    public function isLinked($categoryId)
    {
        /* @var CategoryAttributes $categoryAttributes */
        $categoryAttributes = $this->getCategoryAttributesRepository()->findOneBy(['categoryId' => $categoryId]);
        if (!$categoryAttributes instanceof CategoryAttributes) {
            return false;
        }

        $linkedCategoryId = $categoryAttributes->getSwagLink();
        if ($linkedCategoryId === null) {
            return false;
        }

        /* @var Category $category */
        $category = $this->getCategoryRepository()->find($linkedCategoryId);
        if (!$category instanceof Category) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether this category has own custom sort
     *
     * @param $categoryId
     * @return bool
     */
    public function hasOwnSort($categoryId)
    {
        return $this->getCustomSortRepository()->hasCustomSort($categoryId);
    }

    /**
     * Checks whether this category has to use its custom sort by default, e.g. on category load use this custom sort
     *
     * @param $categoryId
     * @return bool
     */
    public function showCustomSortAsDefault($categoryId)
    {
        /* @var CategoryAttributes $categoryAttributes */
        $categoryAttributes = $this->getCategoryAttributesRepository()->findOneBy(['categoryId' => $categoryId]);
        if (!$categoryAttributes instanceof Category) {
            return false;
        }

        $useDefaultSort = (bool) $categoryAttributes->getSwagShowByDefault();
        $hasOwnSort = $this->hasOwnSort($categoryId);
        $baseSort = $this->getCategoryBaseSort($categoryId);
        if ($useDefaultSort && ($hasOwnSort || $baseSort > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the id of the linked category.
     *
     * @param $categoryId
     * @return int
     */
    public function getLinkedCategoryId($categoryId)
    {
        /* @var CategoryAttributes $categoryAttributes */
        $categoryAttributes = $this->getCategoryAttributesRepository()->findOneBy(['categoryId' => $categoryId]);
        if (!$categoryAttributes instanceof Category) {
            return false;
        }

        $linkedCategoryId = $categoryAttributes->getSwagLink();
        if ($linkedCategoryId === null) {
            return false;
        }

        /* @var Category $category */
        $category = $this->getCategoryRepository()->find($linkedCategoryId);
        if (!$category instanceof Category) {
            return false;
        }

        return $linkedCategoryId;
    }

    /**
     * Returns the base sort id for selected category
     *
     * @param $categoryId
     * @return bool
     */
    public function getCategoryBaseSort($categoryId)
    {
        /* @var CategoryAttributes $categoryAttributes */
        $categoryAttributes = $this->getCategoryAttributesRepository()->findOneBy(['categoryId' => $categoryId]);
        if (!$categoryAttributes instanceof Category) {
            return false;
        }

        $baseSortId = $categoryAttributes->getSwagBaseSort();
        if ($baseSortId === null) {
            return false;
        }

        return $baseSortId;
    }
}
