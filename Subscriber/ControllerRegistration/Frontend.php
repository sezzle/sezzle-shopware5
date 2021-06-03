<?php

namespace SezzlePayment\Subscriber\ControllerRegistration;

use Enlight\Event\SubscriberInterface;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Sezzle' => 'onGetSezzleControllerPath',
        ];
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Frontend_Sezzle event.
     * Returns the path to the frontend controller.
     *
     * @return string
     */
    public function onGetSezzleControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/Sezzle.php';
    }
}
