<?php

class Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns the plugin label which is displayed in the plugin information and
     * in the Plugin Manager.
     * @return string
     */
    public function getLabel()
    {
        return 'Custom sorting';
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
     * @throws Enlight_Exception
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvents();
        $this->createDatabase();
        $this->createAttributes();
        $this->createMenu();
        $this->createForm($this->Form());

        return array('success' => true, 'invalidateCache' => array('backend'));
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
        $subscribers = array(
            new \Shopware\SwagCustomSort\Subscriber\Resource(Shopware()->Container()),
            new \Shopware\SwagCustomSort\Subscriber\ControllerPath($this),
            new \Shopware\SwagCustomSort\Subscriber\Frontend($this)
        );

        foreach ($subscribers as $subscriber) {
            $this->Application()->Events()->addSubscriber($subscriber);
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
        $parent = $this->Menu()->findOneBy(array('label' => 'Artikel'));

        $this->createMenuItem(
            array(
                'label' => 'Custom sort',
                'controller' => 'CustomSort',
                'action' => 'Index',
                'active' => 1,
                'class' => 'sprite-blue-document-text-image',
                'parent' => $parent,
                'position' => 6,
            )
        );
    }

    /**
     * Creates the plugin database tables over the doctrine schema tool.
     */
    public function createDatabase()
    {
        $em = $this->Application()->Models();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);

        $classes = array(
            $em->getClassMetadata('Shopware\CustomModels\CustomSort\ArticleSort'),
        );

        try {
            $tool->createSchema($classes);
        } catch(\Doctrine\ORM\Tools\ToolsException $e) {
            //
        }
    }

    public function createAttributes()
    {
        $em = $this->Application()->Models();
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
            'tinyint(1)',
            true,
            0
        );

        $em->generateAttributeModels(array(
            's_categories_attributes'
        ));
    }

    protected function createForm(Shopware\Models\Config\Form $form)
    {
        $form->setElement('text', 'swagCustomSortName',
            array(
                'label' => 'Name',
                'value' => NULL,
                'description' => 'The new sort, will be visible in the frontend under this name option.'
            )
        );

        $this->addFormTranslations(
            array('en_GB' => array(
                'swagCustomSortName' => array(
                    'label' => 'Name',
                    'description' => 'The new sort, will be visible in the frontend under this name option.'
                )
            ))
        );
    }
}