<?php

namespace SwagPaymentSezzle\Components\Backend;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use SwagPaymentSezzle\Components\Exception\OrderNotFoundException;
use SwagPaymentSezzle\Components\ExceptionHandlerServiceInterface;
use SwagPaymentSezzle\Components\PaymentStatus;
use SwagPaymentSezzle\Components\Services\OrderDataService;
use SwagPaymentSezzle\Components\Services\PaymentStatusService;
use SwagPaymentSezzle\SezzleBundle\Resources\RefundResource;
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

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandler,
        RefundResource $refundResource,
        PaymentStatusService $paymentStatusService,
        OrderDataService $orderDataService,
        ModelManager $modelManager
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->refundResource = $refundResource;
        $this->paymentStatusService = $paymentStatusService;
        $this->orderDataService = $orderDataService;
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderId
     * @param string $amountToRefund
     * @param string $currency
     * @return array
     */
    public function refundOrder($orderId, $amountToRefund, $currency)
    {
        $refundPayload = $this->createRefund($amountToRefund, $currency);

        try {
            $refundData = $this->refundResource->create($orderId, $refundPayload);
            if (!empty($refundData['uuid'])) {
                $this->paymentStatusService->updatePaymentStatus(
                    $orderId,
                    PaymentStatus::PAYMENT_STATUS_REFUNDED
                );
                /** @var Order|null $orderModel */
                $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderId]);

                if (!($orderModel instanceof Order)) {
                    throw new OrderNotFoundException('temporaryId', $orderId);
                }
                $attributesToUpdate = [
                    'refundedAmount' => Util::formatToCurrency($refundPayload->getAmountInCents())
                ];
                $this->orderDataService->applyPaymentAttributes($orderModel->getNumber(), $attributesToUpdate);
            }

            $viewParameter = ['success' => true];
        } catch (\Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'refund order');

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
     *
     * @return \SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount
     */
    private function createRefund($amountToCapture, $currency)
    {
        $requestParameters = new \SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount();
        $requestParameters->setAmountInCents(Util::formatToCents($amountToCapture));
        $requestParameters->setCurrency($currency);

        return $requestParameters;
    }
}
