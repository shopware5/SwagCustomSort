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

use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\SwagCustomSort\Subscriber\Backend;
use Shopware\SwagCustomSort\Subscriber\ControllerPath;
use Shopware\SwagCustomSort\Subscriber\Frontend;
use Shopware\SwagCustomSort\Subscriber\Resource;
use Shopware\SwagCustomSort\Subscriber\Sort;

/**
 * Class Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap
 */
class Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns the plugin label which is displayed in the plugin information and
     * in the Plugin Manager.
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Individuelle Sortierung';
    }

    /**
     * Returns the version of the plugin as a string
     *
     * @return string|void
     * @throws Exception
     */
    public function getVersion()
    {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Install Plugin / Add Events
     *
     * @return bool
     * @throws Exception
     */
    public function install()
    {
        if (!$this->assertVersionGreaterThen('5.0.0')) {
            throw new \Exception('This plugin requires Shopware 5.0.0 or a later version');
        }

        $this->subscribeEvents();
        $this->createDatabase();
        $this->createAttributes();
        $this->createMenu();
        $this->createForm($this->Form());

        return true;
    }

    /**
     * Standard plugin enable method
     *
     * @return array
     */
    public function enable()
    {
        $sql = "UPDATE s_core_menu SET active = 1 WHERE controller = 'CustomSort';";
        Shopware()->Db()->query($sql);

        return ['success' => true, 'invalidateCache' => ['backend']];
    }

    /**
     * Standard plugin disable method
     *
     * @return array
     */
    public function disable()
    {
        $sql = "UPDATE s_core_menu SET active = 0 WHERE controller = 'CustomSort';";
        Shopware()->Db()->query($sql);

        return ['success' => true, 'invalidateCache' => ['backend']];
    }

    /**
     * Registers all necessary events.
     */
    public function subscribeEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Front_StartDispatch', 'onStartDispatch');
    }

    /**
     * Main entry point for the bonus system: Registers various subscribers to hook into shopware
     */
    public function onStartDispatch()
    {
        $subscribers = [
            new Resource($this->get('models'), $this->get('config')),
            new ControllerPath($this->Path(), $this->get('template')),
            new Frontend($this),
            new Backend($this, $this->get('models')),
            new Sort($this)
        ];

        foreach ($subscribers as $subscriber) {
            $this->get('events')->addSubscriber($subscriber);
        }
    }

    /**
     * Method to always register the custom models and the namespace for the auto-loading
     */
    public function afterInit()
    {
        $this->registerCustomModels();
        $this->Application()->Loader()->registerNamespace('Shopware\SwagCustomSort', $this->Path());
    }

    /**
     * Creates the backend menu item.
     */
    public function createMenu()
    {
        $parent = $this->Menu()->findOneBy(['label' => 'Artikel']);

        $this->createMenuItem(
            [
                'label' => 'Custom sort',
                'controller' => 'CustomSort',
                'action' => 'Index',
                'active' => 0,
                'class' => 'sprite-blue-document-text-image',
                'parent' => $parent,
                'position' => 6,
            ]
        );
    }

    /**
     * Creates the plugin database tables over the doctrine schema tool.
     */
    public function createDatabase()
    {
        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = $this->get('models');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);

        $classes = [
            $em->getClassMetadata('Shopware\CustomModels\CustomSort\ArticleSort'),
        ];

        try {
            $tool->createSchema($classes);
        } catch (\Doctrine\ORM\Tools\ToolsException $e) {
            //
        }
    }

    /**
     * creates necessary attributes for categories
     */
    public function createAttributes()
    {
        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = $this->get('models');

        $em->addAttribute(
            's_categories_attributes',
            'swag',
            'link',
            'int(11)',
            true,
            null
        );
        $em->addAttribute(
            's_categories_attributes',
            'swag',
            'show_by_default',
            'int(1)',
            true,
            0
        );
        $em->addAttribute(
            's_categories_attributes',
            'swag',
            'deleted_position',
            'int(11)',
            true,
            null
        );

        $em->addAttribute(
            's_categories_attributes',
            'swag',
            'base_sort',
            'int(11)',
            true,
            null
        );

        $em->generateAttributeModels(['s_categories_attributes']);
    }

    /**
     * @param Form $form
     */
    protected function createForm(Form $form)
    {
        $form->setElement(
            'text',
            'swagCustomSortName',
            [
                'label' => 'Name',
                'value' => 'Individuelle Sortierung',
                'description' => 'Die neue Sortierung ist unter diesem Namen im Frontend sichtbar.',
                'required' => true,
                'scope' => Element::SCOPE_SHOP
            ]
        );

        $this->addFormTranslations(
            [
                'en_GB' => [
                    'swagCustomSortName' => [
                        'label' => 'Name',
                        'description' => 'The new sort will be visible in the frontend under this name.',
                        'value' => 'Custom Sorting'
                    ]
                ]
            ]
        );
    }
}
