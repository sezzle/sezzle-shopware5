<?php

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Order\Order;
use SezzlePayment\Components\Backend\CaptureService;
use SezzlePayment\Components\Backend\RefundService;
use SezzlePayment\Components\Backend\ReleaseService;

//TODO add subshop functionality

class Shopware_Controllers_Backend_Sezzle extends Shopware_Controllers_Backend_Application implements CSRFWhitelistAware
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
        $orderUUID = $this->Request()->getParam('id');
        $amountToCapture = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');
        $isPartial = $this->Request()->getParam('isPartial') == "true";

        /** @var CaptureService $captureService */
        $captureService = $this->get('sezzle.backend.capture_service');
        $viewParameter = $captureService->captureOrder($orderUUID, $amountToCapture, $currency, $isPartial);

        $this->View()->assign($viewParameter);
    }

    /**
     * @throws Exception
     */
    public function refundOrderAction()
    {
        $orderUUID = $this->Request()->getParam('id');
        $amountToRefund = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');

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
        $orderUUID = $this->Request()->getParam('id');
        $amountToRelease = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');

        /** @var ReleaseService $releaseService */
        $releaseService = $this->get('sezzle.backend.release_service');
        $viewParameter = $releaseService->releaseOrder($orderUUID, $amountToRelease, $currency);

        $this->View()->assign($viewParameter);
    }

    public function getWhitelistedCSRFActions()
    {
        return ['validateCredentials'];
    }
}
