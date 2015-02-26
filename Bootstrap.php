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
            new \Shopware\CustomSort\Subscriber\ControllerPath($this)
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
        $this->Application()->Loader()->registerNamespace('Shopware\CustomSort', $this->Path());
    }

    /**
     * Creates the backend menu item.
     */
    public function createMenu()
    {
        $parent = $this->Menu()->findOneBy(array('label' => 'Items'));

        $this->createMenuItem(
            array(
                'label' => 'Custom sort',
                'controller' => 'Custom sort',
                'action' => 'Index',
                'active' => 1,
                'class' => 'sprite-blue-document-text-image',
                'parent' => $parent,
                'position' => 6,
            )
        );
    }

}