<?php

namespace SwagPaymentSezzle\Components\Backend;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use SwagPaymentSezzle\Components\Exception\OrderNotFoundException;
use SwagPaymentSezzle\Components\ExceptionHandlerServiceInterface;
use SwagPaymentSezzle\Components\Services\OrderDataService;
use SwagPaymentSezzle\Components\Services\PaymentStatusService;
use SwagPaymentSezzle\SezzleBundle\Resources\ReleaseResource;
use SwagPaymentSezzle\SezzleBundle\Util;

class ReleaseService
{
    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var ReleaseResource
     */
    private $releaseResource;

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
        ReleaseResource $releaseResource,
        PaymentStatusService $paymentStatusService,
        OrderDataService $orderDataService,
        ModelManager $modelManager
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->releaseResource = $releaseResource;
        $this->paymentStatusService = $paymentStatusService;
        $this->orderDataService = $orderDataService;
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderId
     * @param string $amountToRelease
     * @param string $currency
     * @return array
     */
    public function releaseOrder($orderId, $amountToRelease, $currency)
    {
        $releasePayload = $this->createRelease($amountToRelease, $currency);

        try {
            $captureData = $this->releaseResource->create($orderId, $releasePayload);
            if (!empty($captureData['uuid'])) {
                $this->paymentStatusService->updatePaymentStatus(
                    $orderId,
                    \SwagPaymentPayPalUnified\Components\PaymentStatus::PAYMENT_STATUS_CANCELLED
                );
                /** @var Order|null $orderModel */
                $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderId]);

                if (!($orderModel instanceof Order)) {
                    throw new OrderNotFoundException('temporaryId', $orderId);
                }
                $attributesToUpdate = [
                    'releasedAmount' => Util::formatToCurrency($releasePayload->getAmountInCents())
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
     * @param $amount
     * @param string $currency
     * @return  \SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount
     */
    private function createRelease($amount, $currency)
    {
        $requestParameters = new \SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount();
        $requestParameters->setAmountInCents(Util::formatToCents($amount));
        $requestParameters->setCurrency($currency);

        return $requestParameters;
    }
}
