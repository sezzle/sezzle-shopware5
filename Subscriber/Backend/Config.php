<?php

namespace SezzlePayment\Subscriber\Backend;

use Exception;
use SezzlePayment\Components\ConfigReader;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_Request;
use SezzlePayment\Components\Backend\GatewayRegionService;
use SezzlePayment\Components\ConfigSetter;
use Shopware\Components\Logger;

/**
 * Subscriber for the Backend events.
 *
 * @package SezzlePayment\Subscriber\Backend
 */
class Config implements SubscriberInterface
{

    /** @var  ConfigReader */
    protected $configReader;

    /**
     * @var GatewayRegionService
     */
    private $gatewayRegionService;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ConfigSetter
     */
    private $configSetter;

    /**
     * @param ConfigReader $configReader
     * @param GatewayRegionService $gatewayRegionService
     * @param ConfigSetter $configSetter
     * @param Logger $logger
     */
    public function __construct(
        ConfigReader $configReader,
        GatewayRegionService $gatewayRegionService,
        ConfigSetter $configSetter,
        Logger $logger
    ) {
        $this->configReader = $configReader;
        $this->gatewayRegionService = $gatewayRegionService;
        $this->configSetter = $configSetter;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend_Config' => [
                ['setGatewayRegion']
            ]
        ];
    }

    /**
     * Set gateway region
     *
     * @param Enlight_Controller_ActionEventArgs $args
     * @throws Exception
     */
    public function setGatewayRegion(Enlight_Controller_ActionEventArgs $args)
    {
        /** @var Enlight_Controller_Request_Request $request */
        $request = $args->getRequest();
        if ($request->getActionName() !== 'saveForm' || $request->getParam('name') !== 'SezzlePayment') {
            return;
        }

        $elements = $request->getParam('elements');

        $settings = [
            "public_key" => $this->configReader->get($elements, 'public_key'),
            "private_key" => $this->configReader->get($elements, 'private_key'),
            "sandbox" => (bool)$this->configReader->get($elements, 'sandbox')
        ];

        $gatewayRegion = $this->gatewayRegionService->get($settings);
        if (!$gatewayRegion) {
            $errMsg = "Unable to validate keys.";
            $this->logger->error($errMsg);
            throw new Exception($errMsg);
        }
        $this->logger->info("Keys has been validated. Gateway region: " . $gatewayRegion);

        $responseElements = $this->configSetter->setConfigData($elements, "gateway_region", $gatewayRegion);

        $request->setParam('elements', $responseElements);
        $args->set('request', $request);
    }
}
