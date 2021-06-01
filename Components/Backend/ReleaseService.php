<?php

namespace Sezzle\Components\Backend;

use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Sezzle\Components\ExceptionHandlerServiceInterface;
use Sezzle\Components\Services\OrderDataService;
use Sezzle\Components\Services\OrderStatusService;
use Sezzle\Components\Services\Validation\PaymentActionValidator;
use Sezzle\Components\Services\PaymentStatusService;
use Sezzle\SezzleBundle\Resources\ReleaseResource;
use Sezzle\SezzleBundle\Structs\Session\Order\Amount;
use Sezzle\SezzleBundle\Util;

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
        ReleaseResource $releaseResource,
        PaymentStatusService $paymentStatusService,
        OrderStatusService $orderStatusService,
        OrderDataService $orderDataService,
        ModelManager $modelManager,
        PaymentActionValidator $paymentActionValidator
    )
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->releaseResource = $releaseResource;
        $this->paymentStatusService = $paymentStatusService;
        $this->orderStatusService = $orderStatusService;
        $this->orderDataService = $orderDataService;
        $this->modelManager = $modelManager;
        $this->paymentActionValidator = $paymentActionValidator;
    }

    /**
     * @param string $orderUUID
     * @param string $amountToRelease
     * @param string $currency
     * @return array
     */
    public function releaseOrder($orderUUID, $amountToRelease, $currency)
    {
        $releasePayload = $this->createRelease($amountToRelease, $currency);

        try {
            if (!$this->paymentActionValidator->isAmountValid($orderUUID, $amountToRelease, 'DoRelease')) {
                throw new Exception("Invalid amount");
            }
            $captureData = $this->releaseResource->create($orderUUID, $releasePayload);
            if (empty($captureData['uuid'])) {
                throw new Exception("Error releasing");
            }
            $this->orderStatusService->updateOrderStatus(
                $orderUUID,
                Status::ORDER_STATE_IN_PROCESS
            );
            /** @var Order|null $orderModel */
            $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderUUID]);

            if (!($orderModel instanceof Order)) {
                throw new Exception('Order not found');
            }

            if ($orderModel->getAttribute()->getSezzleAuthAmount() == $amountToRelease) {
                $this->paymentStatusService->updatePaymentStatus(
                    $orderUUID,
                    Status::PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED
                );
            }
            $prevReleasedAmount = $orderModel->getAttribute()->getSezzleReleasedAmount();
            $newReleasedAmount = Util::formatToCurrency($releasePayload->getAmountInCents());
            $attributesToUpdate = [
                'authAmount' => $orderModel->getAttribute()->getSezzleAuthAmount() - $newReleasedAmount,
                'releasedAmount' => $prevReleasedAmount + $newReleasedAmount
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
     * @param float $amount
     * @param string $currency
     * @return  Amount
     */
    private function createRelease($amount, $currency)
    {
        $requestParameters = new Amount();
        $requestParameters->setAmountInCents(Util::formatToCents($amount));
        $requestParameters->setCurrency($currency);

        return $requestParameters;
    }
}
