<?php

namespace Sezzle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Sezzle\SezzleBundle\Components\SettingsServiceInterface;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @param string $pluginDir
     * @param SettingsServiceInterface $settingsService
     */
    public function __construct(
        $pluginDir,
        SettingsServiceInterface $settingsService
    ) {
        $this->pluginDir = $pluginDir;
        $this->settingsService = $settingsService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
            'Theme_Inheritance_Template_Directories_Collected' => 'onCollectTemplateDir',
        ];
    }

    public function onPostDispatchSecure(\Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->settingsService->hasSettings()) {
            return;
        }

        $active = (bool) $this->settingsService->get('active');
        if (!$active) {
            return;
        }

        $isWidgetActiveForPDP = (bool) $this->settingsService->get('enable_widget_pdp');
        $isWidgetActiveForCart = (bool) $this->settingsService->get('enable_widget_cart');
        $merchantUUID = (bool) $this->settingsService->get('merchant_uuid');

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        //Assign shop specific and configurable values to the view.
        $view->assign('merchantUUID', $merchantUUID);
        $view->assign('isWidgetActiveForPDP', $isWidgetActiveForPDP);
        $view->assign('isWidgetActiveForCart', $isWidgetActiveForCart);
    }

    /**
     * Adds the template directory to the TemplateManager
     */
    public function onCollectTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views/';

        $args->setReturn($dirs);
    }
}

