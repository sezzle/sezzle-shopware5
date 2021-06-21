<?php

namespace SezzlePayment\Components\Services;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SezzlePayment\Components\Exception\OrderNotFoundException;

class PaymentStatusService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * PaymentStatusService constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $parentPayment
     * @param int $paymentStateId
     * @throws ORMException
     * @throws OptimisticLockException
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
        if ($paymentStateId === Status::PAYMENT_STATE_COMPLETELY_PAID
            || $paymentStateId === Status::PAYMENT_STATE_PARTIALLY_PAID
        ) {
            $orderModel->setClearedDate(new DateTime());
        }

        $this->modelManager->flush($orderModel);
    }
}
