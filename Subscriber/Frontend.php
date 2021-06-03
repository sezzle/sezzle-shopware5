<?php

namespace SezzlePayment\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use SezzlePayment\SezzleBundle\Components\SettingsServiceInterface;
use SezzlePayment\SezzleBundle\GatewayRegion;

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

    public function onPostDispatchSecure(Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->settingsService->hasSettings()) {
            return;
        }

        $active = (bool)$this->settingsService->get('active');
        if (!$active) {
            return;
        }

        $isWidgetActiveForPDP = (bool)$this->settingsService->get('enable_widget_pdp');
        $isWidgetActiveForCart = (bool)$this->settingsService->get('enable_widget_cart');
        $merchantUUID = $this->settingsService->get('merchant_uuid');
        $gatewayRegion = $this->settingsService->get('gateway_region');
        $widgetURL = sprintf(
            "https://widget.%s/v1/javascript/price-widget?uuid=%s",
            GatewayRegion::getSezzleDomain($gatewayRegion),
            $merchantUUID
        );

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        //Assign shop specific and configurable values to the view.
        // $view->assign('merchantUUID', $merchantUUID);
        $view->assign('isWidgetActiveForPDP', $isWidgetActiveForPDP);
        $view->assign('isWidgetActiveForCart', $isWidgetActiveForCart);
        $view->assign('widgetURL', $widgetURL);
    }

    /**
     * Adds the template directory to the TemplateManager
     */
    public function onCollectTemplateDir(Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views/';

        $args->setReturn($dirs);
    }
}

