<?php

namespace SwagPaymentSezzle\Components\Backend;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use SwagPaymentSezzle\Components\Exception\OrderNotFoundException;
use SwagPaymentSezzle\Components\ExceptionHandlerServiceInterface;
use SwagPaymentSezzle\Components\PaymentStatus;
use SwagPaymentSezzle\Components\Services\OrderDataService;
use SwagPaymentSezzle\Components\Services\PaymentStatusService;
use SwagPaymentSezzle\SezzleBundle\Resources\CaptureResource;
use SwagPaymentSezzle\SezzleBundle\Util;

class CaptureService
{
    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var CaptureResource
     */
    private $captureResource;

    /**
     * @var PaymentStatusService
     */
    private $paymentStatusService;
    /**
     * @var OrderDataService
     */
    private $orderDataService;
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandler,
        CaptureResource $captureResource,
        PaymentStatusService $paymentStatusService,
        OrderDataService $orderDataService,
        ModelManager $modelManager
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->captureResource = $captureResource;
        $this->paymentStatusService = $paymentStatusService;
        $this->orderDataService = $orderDataService;
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderId
     * @param string $amountToCapture
     * @param string $currency
     * @param bool   $isPartial
     *
     * @return array
     */
    public function captureOrder($orderId, $amountToCapture, $currency, $isPartial)
    {
        $capturePayload = $this->createCapture($amountToCapture, $currency, $isPartial);

        try {
            $captureData = $this->captureResource->create($orderId, $capturePayload);
            if (!empty($captureData['uuid'])) {
                $this->updateCapturePaymentStatus($orderId, $isPartial);
                /** @var Order|null $orderModel */
                $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderId]);

                if (!($orderModel instanceof Order)) {
                    throw new OrderNotFoundException('temporaryId', $orderId);
                }
                $attributesToUpdate = [
                    'capturedAmount' => Util::formatToCurrency($capturePayload->getCaptureAmount()->getAmountInCents())
                ];
                $this->orderDataService->applyPaymentAttributes($orderModel->getNumber(), $attributesToUpdate);
            }

            $viewParameter = ['success' => true];
        } catch (\Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'capture order');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param string $amountToCapture
     * @param string $currency
     * @param bool   $isPartial
     *
     * @return \SwagPaymentSezzle\SezzleBundle\Structs\Order\Capture
     */
    private function createCapture($amountToCapture, $currency, $isPartial)
    {
        $requestParameters = new \SwagPaymentSezzle\SezzleBundle\Structs\Order\Capture();

        $amount = new \SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount();
        $amount->setAmountInCents(Util::formatToCents($amountToCapture));
        $amount->setCurrency($currency);
        $requestParameters->setCaptureAmount($amount);
        $requestParameters->setPartialCapture($isPartial);

        return $requestParameters;
    }

    /**
     * @param $orderUuid
     * @param bool $isPartial
     */
    private function updateCapturePaymentStatus($orderUuid, $isPartial)
    {

            if ($isPartial) {
                $this->paymentStatusService->updatePaymentStatus(
                    $orderUuid,
                    PaymentStatus::PAYMENT_STATUS_PARTIALLY_PAID
                );
            } else {
                $this->paymentStatusService->updatePaymentStatus(
                    $orderUuid,
                    PaymentStatus::PAYMENT_STATUS_PAID
                );
            }

    }
}
