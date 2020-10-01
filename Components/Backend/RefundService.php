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
use SwagPaymentSezzle\SezzleBundle\Resources\RefundResource;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount;
use SwagPaymentSezzle\SezzleBundle\Util;

class RefundService
{
    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var RefundResource
     */
    private $refundResource;

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
        RefundResource $refundResource,
        PaymentStatusService $paymentStatusService,
        OrderStatusService $orderStatusService,
        OrderDataService $orderDataService,
        ModelManager $modelManager,
        PaymentActionValidator $paymentActionValidator
    )
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->refundResource = $refundResource;
        $this->paymentStatusService = $paymentStatusService;
        $this->orderStatusService = $orderStatusService;
        $this->orderDataService = $orderDataService;
        $this->modelManager = $modelManager;
        $this->paymentActionValidator = $paymentActionValidator;
    }

    /**
     * @param string $orderUUID
     * @param string $amountToRefund
     * @param string $currency
     * @return array
     */
    public function refundOrder($orderUUID, $amountToRefund, $currency)
    {
        $refundPayload = $this->createRefund($amountToRefund, $currency);

        try {
            if (!$this->paymentActionValidator->isAmountValid($orderUUID, $amountToRefund, 'DoRefund')) {
                throw new Exception("Invalid amount");
            }
            $refundData = $this->refundResource->create($orderUUID, $refundPayload);
            if (empty($refundData['uuid'])) {
                throw new Exception("Error refunding");
            }
            $this->paymentStatusService->updatePaymentStatus(
                $orderUUID,
                Status::PAYMENT_STATE_RE_CREDITING
            );
            $this->orderStatusService->updateOrderStatus(
                $orderUUID,
                Status::ORDER_STATE_IN_PROCESS
            );
            /** @var Order|null $orderModel */
            $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderUUID]);

            if (!($orderModel instanceof Order)) {
                throw new Exception('Order not found');
            }
            $attributesToUpdate = [
                'refundedAmount' => Util::formatToCurrency($refundPayload->getAmountInCents())
            ];
            $this->orderDataService->applyPaymentAttributes($orderModel->getNumber(), $attributesToUpdate);

            $viewParameter = ['success' => true];
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'refund order');

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
     *
     * @return Amount
     */
    private function createRefund($amountToCapture, $currency)
    {
        $requestParameters = new Amount();
        $requestParameters->setAmountInCents(Util::formatToCents($amountToCapture));
        $requestParameters->setCurrency($currency);

        return $requestParameters;
    }
}
