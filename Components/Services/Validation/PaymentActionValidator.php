<?php

namespace SwagPaymentSezzle\Components\Services\Validation;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use SwagPaymentSezzle\SezzleBundle\PaymentAction;

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

        switch ($method) {
            case 'DoCapture':
                $amountAvailableToCapture = $orderModel->getInvoiceAmount() - $orderModel->getAttribute()->getSwagSezzleCapturedAmount();
                if ($amount > $amountAvailableToCapture) {
                    return false;
                }
                break;
            case 'DoRefund':
                $amountAvailableToRefund = $orderModel->getAttribute()->getSwagSezzleCapturedAmount() - $orderModel->getAttribute()->getSwagSezzleRefundedAmount();
                if ($amount > $amountAvailableToRefund) {
                    return false;
                }
                break;
            case 'DoRelease':
                $amountAvailableToRelease = $orderModel->getAttribute()->getSwagSezzleAuthAmount() - $orderModel->getAttribute()->getSwagSezzleCapturedAmount();
                if ($amount > $amountAvailableToRelease) {
                    return false;
                }
                break;
        }
        return true;
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
        if ($action === PaymentAction::AUTHORIZE && !$orderModel->getAttribute()->getSwagSezzleAuthExpiry()) {
            return false;
        }

        $authExpiryDatetime = $orderModel->getAttribute()->getSwagSezzleAuthExpiry();

        $currentDatetime = new \DateTime();

        if ($authExpiryDatetime->getTimestamp() < $currentDatetime->getTimestamp()) {
            return false;
        }
        return true;
    }
}
