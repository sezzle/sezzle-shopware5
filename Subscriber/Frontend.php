<?php

namespace SezzlePayment\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use SezzlePayment\Components\Services\SettingsService;
use SezzlePayment\SezzleBundle\GatewayRegion;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;
    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @param string $pluginDir
     * @param SettingsService $settingsService
     */
    public function __construct(
        $pluginDir,
        SettingsService $settingsService
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
        if (!$this->settingsService->isActive()) {
            return;
        }

        $widgetURL = sprintf(
            "https://widget.%s/v1/javascript/price-widget?uuid=%s",
            GatewayRegion::getSezzleDomain($this->settingsService->getGatewayRegion()),
            $this->settingsService->getMerchantUuid()
        );

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();
        $view->assign('isWidgetActiveForPDP', $this->settingsService->isEnableWidgetPdp());
        $view->assign('isWidgetActiveForCart', $this->settingsService->isEnableWidgetCart());
        $view->assign('widgetURL', $widgetURL);
        $view->assign('sezzleWidgetLanguage', $this->settingsService->getLanguage());
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

