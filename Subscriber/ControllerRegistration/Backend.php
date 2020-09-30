<?php

namespace SwagPaymentSezzle\Subscriber\ControllerRegistration;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;

class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    /**
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory, Enlight_Template_Manager $template)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Sezzle' => 'onGetBackendControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SezzleSettings' => 'onGetBackendSettingsControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SezzleGeneralSettings' => 'onGetBackendGeneralSettingsControllerPath'
        ];
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_SezzleSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendControllerPath()
    {
        $this->template->addTemplateDir($this->pluginDirectory . '/Resources/views/');

        return $this->pluginDirectory . '/Controllers/Backend/Sezzle.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_SezzleSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendSettingsControllerPath()
    {
        $this->template->addTemplateDir($this->pluginDirectory . '/Resources/views/');

        return $this->pluginDirectory . '/Controllers/Backend/SezzleSettings.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_SezzleGeneralSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendGeneralSettingsControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Backend/SezzleGeneralSettings.php';
    }
}
