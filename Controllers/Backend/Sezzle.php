<?php

use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use SwagPaymentSezzle\Components\Backend\CaptureService;
use SwagPaymentSezzle\Components\Backend\RefundService;
use SwagPaymentSezzle\Components\Backend\ReleaseService;

class Shopware_Controllers_Backend_Sezzle extends Shopware_Controllers_Backend_Application
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
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        //$this->exceptionHandler = $this->get('paypal_unified.exception_handler_service');

        parent::preDispatch();
    }

    public function captureOrderAction()
    {
        $this->registerShopResource();

        $orderId = $this->Request()->getParam('id');
        $amountToCapture = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');
        $isPartial = $this->Request()->getParam('isPartial') === '1';

        /** @var CaptureService $captureService */
        $captureService = $this->get('sezzle.backend.capture_service');
        $viewParameter = $captureService->captureOrder($orderId, $amountToCapture, $currency, $isPartial);

        $this->View()->assign($viewParameter);
    }

    public function refundOrderAction()
    {
        $this->registerShopResource();

        $orderId = $this->Request()->getParam('id');
        $amountToCapture = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');



        /** @var RefundService $refundService */
        $refundService = $this->get('sezzle.backend.refund_service');
        $viewParameter = $refundService->refundOrder($orderId, $amountToCapture, $currency);

        $this->View()->assign($viewParameter);
    }

    public function releaseOrderAction()
    {
        $this->registerShopResource();

        $orderId = $this->Request()->getParam('id');
        $amountToCapture = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');

        /** @var ReleaseService $releaseService */
        $releaseService = $this->get('sezzle.backend.release_service');
        $viewParameter = $releaseService->releaseOrder($orderId, $amountToCapture, $currency);

        $this->View()->assign($viewParameter);
    }

    private function registerShopResource()
    {
        $shopId = (int) $this->Request()->getParam('shopId');
        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->get('models')->getRepository(Shop::class);

        $shop = $shopRepository->getActiveById($shopId);
        if ($shop === null) {
            $shop = $shopRepository->getActiveDefault();
        }

        if ($this->container->has('shopware.components.shop_registration_service')) {
            $this->get('shopware.components.shop_registration_service')->registerResources($shop);
        } else {
            $shop->registerResources();
        }

        $this->get('paypal_unified.settings_service')->refreshDependencies();
    }
}
