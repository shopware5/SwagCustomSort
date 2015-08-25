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

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;

class ControllerPath implements SubscriberInterface
{
    /**
     * @var string $bootstrapPath
     */
    protected $bootstrapPath;

    public function __construct($bootstrapPath)
    {
        $this->bootstrapPath = $bootstrapPath;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_CustomSort' => 'onGetCustomSortControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_CustomSort' => 'onGetCustomSortControllerPath'
        ];
    }

    /**
     * Register module template directory
     */
    protected function registerView()
    {
        Shopware()->Template()->addTemplateDir($this->bootstrapPath . 'Views/');
    }

    /**
    * This function is responsible to resolve the backend / frontend controller path.
    *
    * @param  \Enlight_Event_EventArgs $args
    * @return string
    */
    public function onGetCustomSortControllerPath(\Enlight_Event_EventArgs $args)
    {
        $this->registerView();

        switch ($args->getName()) {
            case 'Enlight_Controller_Dispatcher_ControllerPath_Backend_CustomSort':
                return $this->bootstrapPath . 'Controllers/Backend/CustomSort.php';
            case 'Enlight_Controller_Dispatcher_ControllerPath_Widgets_CustomSort':
                return $this->bootstrapPath . 'Controllers/Widgets/CustomSort.php';
        }
    }
}
