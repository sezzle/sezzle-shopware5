<?php

namespace SwagPaymentSezzle\Components\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentSezzle\Components\Exception\OrderNotFoundException;
use SwagPaymentSezzle\Components\PaymentStatus;

class PaymentStatusService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $parentPayment
     * @param int    $paymentStateId
     */
    public function updatePaymentStatus($parentPayment, $paymentStateId)
    {
        /** @var Order|null $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $parentPayment]);

        if (!($orderModel instanceof Order)) {
            throw new OrderNotFoundException('temporaryId', $parentPayment);
        }

        /** @var Status|null $orderStatusModel */
        $orderStatusModel = $this->modelManager->getRepository(Status::class)->find($paymentStateId);

        $orderModel->setPaymentStatus($orderStatusModel);
        if ($paymentStateId === PaymentStatus::PAYMENT_STATUS_PAID
            || $paymentStateId === PaymentStatus::PAYMENT_STATUS_PARTIALLY_PAID
        ) {
            $orderModel->setClearedDate(new \DateTime());
        }

        $this->modelManager->flush($orderModel);
    }
}
