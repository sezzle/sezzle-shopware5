<?php

namespace SwagPaymentSezzle\Components\Backend;

use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentSezzle\Components\ExceptionHandlerServiceInterface;
use SwagPaymentSezzle\Components\Services\OrderDataService;
use SwagPaymentSezzle\Components\Services\OrderStatusService;
use SwagPaymentSezzle\Components\Services\Validation\PaymentActionValidator;
use SwagPaymentSezzle\Components\Services\PaymentStatusService;
use SwagPaymentSezzle\SezzleBundle\PaymentAction;
use SwagPaymentSezzle\SezzleBundle\Resources\CaptureResource;
use SwagPaymentSezzle\SezzleBundle\Structs\Order\Capture;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount;
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
    /**
     * @var OrderStatusService
     */
    private $orderStatusService;
    /**
     * @var PaymentActionValidator
     */
    private $paymentActionValidator;

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandler,
        CaptureResource $captureResource,
        PaymentStatusService $paymentStatusService,
        OrderStatusService $orderStatusService,
        OrderDataService $orderDataService,
        ModelManager $modelManager,
        PaymentActionValidator $paymentActionValidator
    )
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->captureResource = $captureResource;
        $this->paymentStatusService = $paymentStatusService;
        $this->orderStatusService = $orderStatusService;
        $this->orderDataService = $orderDataService;
        $this->modelManager = $modelManager;
        $this->paymentActionValidator = $paymentActionValidator;
    }

    /**
     * @param string $orderUUID
     * @param string $amountToCapture
     * @param string $currency
     * @param bool $isPartial
     * @param string $action
     *
     * @return array
     */
    public function captureOrder($orderUUID, $amountToCapture, $currency, $isPartial, $action = PaymentAction::AUTHORIZE)
    {
        $capturePayload = $this->createCapture($amountToCapture, $currency, $isPartial);

        try {
            if (!$this->paymentActionValidator->isAmountValid($orderUUID, $amountToCapture, 'DoCapture')) {
                throw new Exception("Invalid amount");
            }
            if (!$this->paymentActionValidator->isAuthValid($orderUUID, $action)) {
                throw new Exception("Auth expired");
            }
            $captureData = $this->captureResource->create($orderUUID, $capturePayload);
            if (empty($captureData['uuid'])) {
                throw new Exception("Error capturing");
            }
            $this->updateCapturePaymentStatus($orderUUID, $isPartial);
            $this->orderStatusService->updateOrderStatus(
                $orderUUID,
                Status::ORDER_STATE_IN_PROCESS
            );
            $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderUUID]);

            if (!($orderModel instanceof Order)) {
                throw new Exception('Order not found');
            }
            $attributesToUpdate = [
                'capturedAmount' => Util::formatToCurrency($capturePayload->getCaptureAmount()->getAmountInCents())
            ];
            $this->orderDataService->applyPaymentAttributes($orderModel->getNumber(), $attributesToUpdate);

            $viewParameter = ['success' => true];
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'capture order');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param float $amountToCapture
     * @param string $currency
     * @param bool $isPartial
     *
     * @return Capture
     */
    private function createCapture($amountToCapture, $currency, $isPartial)
    {
        $requestParameters = new Capture();

        $amount = new Amount();
        $amount->setAmountInCents(Util::formatToCents($amountToCapture));
        $amount->setCurrency($currency);
        $requestParameters->setCaptureAmount($amount);
        $requestParameters->setPartialCapture($isPartial);

        return $requestParameters;
    }

    /**
     * @param string $orderUuid
     * @param bool $isPartial
     */
    private function updateCapturePaymentStatus($orderUuid, $isPartial)
    {
        if ($isPartial) {
            $this->paymentStatusService->updatePaymentStatus(
                $orderUuid,
                Status::PAYMENT_STATE_PARTIALLY_PAID
            );
        } else {
            $this->paymentStatusService->updatePaymentStatus(
                $orderUuid,
                Status::PAYMENT_STATE_COMPLETELY_PAID
            );
        }
    }
}
