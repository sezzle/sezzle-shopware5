<?php

namespace Sezzle\Components\Services\Validation;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Sezzle\SezzleBundle\PaymentAction;

class PaymentActionValidator
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * PaymentActionValidator constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(
        ModelManager $modelManager
    )
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderUUID
     * @param float $amount
     * @param string $method
     * @return bool
     */
    public function isAmountValid($orderUUID, $amount, $method)
    {
        /** @var Order|null $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderUUID]);

        if (!($orderModel instanceof Order)) {
            return false;
        }

        $amountAvailable = 0.00;
        switch ($method) {
            case 'DoCapture':
                $amountAvailable = $orderModel->getInvoiceAmount() - $orderModel->getAttribute()->getSezzleCapturedAmount();
                break;
            case 'DoRefund':
                $amountAvailable = $orderModel->getAttribute()->getSezzleCapturedAmount() - $orderModel->getAttribute()->getSezzleRefundedAmount();
                break;
            case 'DoRelease':
                $amountAvailable = $orderModel->getAttribute()->getSezzleAuthAmount() - $orderModel->getAttribute()->getSezzleCapturedAmount();
                break;
        }
        return $amount <= $amountAvailable;
    }

    /**
     * @param string $orderUUID
     * @param string $action
     * @return bool
     */
    public function isAuthValid($orderUUID, $action)
    {
        /** @var Order|null $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $orderUUID]);

        if (!($orderModel instanceof Order)) {
            return false;
        }

        if ($action === PaymentAction::AUTHORIZE_CAPTURE) {
            return true;
        }
        if ($action === PaymentAction::AUTHORIZE && !$orderModel->getAttribute()->getSezzleAuthExpiry()) {
            return false;
        }

        $authExpiryDatetime = $orderModel->getAttribute()->getSezzleAuthExpiry();
        $authExpiryDatetime = new \DateTime($authExpiryDatetime->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));

        $currentDatetime = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($authExpiryDatetime->getTimestamp() < $currentDatetime->getTimestamp()) {
            return false;
        }
        return true;
    }
}
