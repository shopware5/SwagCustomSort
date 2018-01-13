<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager as TemplateManager;

/**
 * Class ControllerPath
 */
class ControllerPath implements SubscriberInterface
{
    /**
     * @var string
     */
    private $bootstrapPath;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @param string          $bootstrapPath
     * @param TemplateManager $templateManager
     */
    public function __construct($bootstrapPath, TemplateManager $templateManager)
    {
        $this->bootstrapPath = $bootstrapPath;
        $this->templateManager = $templateManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_CustomSort' => 'onGetCustomSortControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_CustomSort' => 'onGetCustomSortControllerPath',
        ];
    }

    /**
     * This function is responsible to resolve the backend / frontend controller path.
     *
     * @param \Enlight_Event_EventArgs $args
     *
     * @return string
     */
    public function onGetCustomSortControllerPath(\Enlight_Event_EventArgs $args)
    {
        $this->templateManager->addTemplateDir($this->bootstrapPath . 'Views/');

        switch ($args->getName()) {
            case 'Enlight_Controller_Dispatcher_ControllerPath_Backend_CustomSort':
                return $this->bootstrapPath . 'Controllers/Backend/CustomSort.php';
            case 'Enlight_Controller_Dispatcher_ControllerPath_Widgets_CustomSort':
                return $this->bootstrapPath . 'Controllers/Widgets/CustomSort.php';
        }
    }
}
