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
     * @throws Exception
     */
    public function captureOrderAction()
    {
        $this->registerShopResource();

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
        $this->registerShopResource();

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
        $this->registerShopResource();

        $orderUUID = $this->Request()->getParam('id');
        $amountToRelease = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');

        /** @var ReleaseService $releaseService */
        $releaseService = $this->get('sezzle.backend.release_service');
        $viewParameter = $releaseService->releaseOrder($orderUUID, $amountToRelease, $currency);

        $this->View()->assign($viewParameter);
    }

    /**
     * @throws Exception
     */
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

        $this->get('sezzle.settings_service')->refreshDependencies();
    }
}
