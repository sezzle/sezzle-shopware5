<?php

namespace SwagPaymentSezzle\Components\Services;

use Doctrine\DBAL\Connection;
use SwagPaymentSezzle\Components\PaymentStatus;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsTable;
use SwagPaymentSezzle\SezzleBundle\PaymentType;
use SwagPaymentSezzle\SezzleBundle\Structs\Session;

class OrderDataService
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(
        Connection $dbalConnection,
        SettingsServiceInterface $settingsService
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->settingsService = $settingsService;
    }

    /**
     * @param string $orderNumber
     * @param int    $paymentStatusId
     *
     * @return bool
     *
     * @deprecated Deprecated since 2.0.1, will be removed in 3.0.0, no replacement implemented.
     * Core functionality will be used from now on.
     */
    public function applyPaymentStatus($orderNumber, $paymentStatusId)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.cleared', ':paymentStatus')
            ->where('o.ordernumber = :orderNumber')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':paymentStatus' => $paymentStatusId,
            ]);

        if ($paymentStatusId === PaymentStatus::PAYMENT_STATUS_PAID) {
            $builder->set('o.cleareddate', 'NOW()');
        }

        return $builder->execute() === 1;
    }

    /**
     * @param string $orderNumber
     */
    public function setClearedDate($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.cleareddate', 'NOW()')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param int    $orderStateId
     */
    public function setOrderState($orderNumber, $orderStateId)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.status', $orderStateId)
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param string $transactionId
     *
     * @return bool
     */
    public function applyTransactionId($orderNumber, $transactionId)
    {
        $result = $this->dbalConnection->createQueryBuilder()->update('s_order', 'o')
            ->set('o.transactionID', ':transactionId')
            ->where('o.ordernumber = :orderNumber')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':transactionId' => $transactionId,
            ])
            ->execute();

        return $result === 1;
    }

    /**
     * @param string $orderNumber
     */
    public function removeTransactionId($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.transactionID', "''")
            ->where('o.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param bool   $expressCheckout
     * @param bool   $smartPaymentButtons
     *
     * @see PaymentType
     */
    public function applyPaymentTypeAttribute($orderNumber, Payment $payment, $expressCheckout = false, $smartPaymentButtons = false)
    {
        $paymentType = PaymentType::PAYPAL_CLASSIC;
        $payer = $payment->getPayer();

        if ($expressCheckout) {
            $paymentType = PaymentType::PAYPAL_EXPRESS;
        } elseif ($smartPaymentButtons) {
            $paymentType = PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS;
        } elseif ($payer && $payment->getPayer()->getExternalSelectedFundingInstrumentType() === 'CREDIT') {
            $paymentType = PaymentType::PAYPAL_INSTALLMENTS;
        } elseif ($payment->getPaymentInstruction() !== null) {
            $paymentType = PaymentType::PAYPAL_INVOICE;
        } elseif ((bool) $this->settingsService->get('active', SettingsTable::PLUS)) {
            $paymentType = PaymentType::PAYPAL_PLUS;
        }

        $builder = $this->dbalConnection->createQueryBuilder();

        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        $subQuery = $this->dbalConnection->createQueryBuilder()
            ->select('o.id')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->getSQL();

        $builder->update('s_order_attributes', 'oa')
            ->set('oa.swag_paypal_unified_payment_type', ':paymentType')
            ->where('oa.orderID = (' . $subQuery . ')')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':paymentType' => $paymentType,
            ])->execute();
    }

    /**
     * @param int $orderNumber
     *
     * @return string
     */
    public function getTransactionId($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->select('o.transactionId')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber);

        return $builder->execute()->fetchColumn();
    }
}
