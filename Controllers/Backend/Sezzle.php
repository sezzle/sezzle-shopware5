<?php

use SezzlePayment\Components\Services\SettingsService;
use SezzlePayment\SezzleBundle\GatewayRegion;
use SezzlePayment\SezzleBundle\Services\ClientService;
use SezzlePayment\SezzleBundle\Structs\AuthCredentials;
use SezzlePayment\SezzleBundle\TransactionMode;
use Shopware\Models\Order\Order;
use SezzlePayment\Components\Backend\CaptureService;
use SezzlePayment\Components\Backend\RefundService;
use SezzlePayment\Components\Backend\ReleaseService;

//TODO add subshop functionality

class Shopware_Controllers_Backend_Sezzle extends Shopware_Controllers_Backend_Application implements \Shopware\Components\CSRFWhitelistAware
{
    /**
     * @var string
     */
    protected $model = Order::class;

    /**
     * @var string
     */
    protected $alias = 'sOrder';

    /**
     * @throws Exception
     */
    public function captureOrderAction()
    {
        $orderUUID       = $this->Request()->getParam('id');
        $amountToCapture = $this->Request()->getParam('amount');
        $currency        = $this->Request()->getParam('currency');
        $isPartial       = $this->Request()->getParam('isPartial') == "true";

        /** @var CaptureService $captureService */
        $captureService = $this->get('sezzle.backend.capture_service');
        $viewParameter  = $captureService->captureOrder($orderUUID, $amountToCapture, $currency, $isPartial);

        $this->View()->assign($viewParameter);
    }

    /**
     * @throws Exception
     */
    public function refundOrderAction()
    {
        $orderUUID      = $this->Request()->getParam('id');
        $amountToRefund = $this->Request()->getParam('amount');
        $currency       = $this->Request()->getParam('currency');

        /** @var RefundService $refundService */
        $refundService = $this->get('sezzle.backend.refund_service');
        $viewParameter = $refundService->refundOrder($orderUUID, $amountToRefund, $currency);

        $this->View()->assign($viewParameter);
    }

    /**
     * @throws Exception
     */
    public function releaseOrderAction()
    {
        $orderUUID       = $this->Request()->getParam('id');
        $amountToRelease = $this->Request()->getParam('amount');
        $currency        = $this->Request()->getParam('currency');

        /** @var ReleaseService $releaseService */
        $releaseService = $this->get('sezzle.backend.release_service');
        $viewParameter  = $releaseService->releaseOrder($orderUUID, $amountToRelease, $currency);

        $this->View()->assign($viewParameter);
    }

    public function validateCredentialsAction()
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->get('sezzle.settings_service');
        /** @var ClientService $settingsService */
        $clientService = $this->get('sezzle.client_service');

        $success = true;
        try {
            $clientService->configure([
                'sandbox' => $settingsService->get('sandbox'),
                'public_key' => $settingsService->get('public_key'),
                'private_key' => $settingsService->get('private_key'),
                'gateway_region' => $settingsService->get('gateway_region')
            ]);
        }catch(Exception $e){
            $success = false;
        }

        /** @var Enlight_Controller_Plugins_Json_Bootstrap $jsonPlugin */
        $jsonPlugin = $this->Front()->Plugins()->Json();
        $jsonPlugin->setRenderer();
        $this->view->assign('success', $success);
    }

    public function getWhitelistedCSRFActions()
    {
        return ['validateCredentials'];
    }
}
