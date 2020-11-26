<?php

namespace SwagPaymentSezzle\Components\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentSezzle\Components\Exception\OrderNotFoundException;

class OrderStatusService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * OrderStatusService constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderUUID
     * @param int $orderState
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateOrderStatus($orderUUID, $orderState)
    {
        /** @var Order|null $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderUUID]);

        if (!($orderModel instanceof Order)) {
            throw new OrderNotFoundException('temporaryId', $orderUUID);
        }

        /** @var Status|null $orderStatusModel */
        $orderStatusModel = $this->modelManager->getRepository(Status::class)->find($orderState);

        $orderModel->setOrderStatus($orderStatusModel);
        $this->modelManager->flush($orderModel);
    }
}
