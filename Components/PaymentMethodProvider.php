<?php

namespace SezzlePayment\Components;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;

class PaymentMethodProvider
{
    /**
     * The technical name of the sezzle payment method.
     */
    const SEZZLE_PAYMENT_METHOD_NAME = 'Sezzle';

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager = null)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @return Payment|null
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     *
     */
    public function getPaymentMethodModel()
    {
        /** @var Payment|null $payment */
        $payment = $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => self::SEZZLE_PAYMENT_METHOD_NAME,
        ]);

        return $payment;
    }

    /**
     * @param bool $active
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     */
    public function setPaymentMethodActiveFlag($active)
    {
        $paymentMethod = $this->getPaymentMethodModel();
        if ($paymentMethod) {
            $paymentMethod->setActive($active);

            $this->modelManager->persist($paymentMethod);
            $this->modelManager->flush($paymentMethod);
        }
    }

    /**
     * @param Connection $connection
     * @return bool
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     *
     */
    public function getPaymentMethodActiveFlag(Connection $connection)
    {
        $sql = 'SELECT `active` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (bool) $connection->fetchColumn($sql, [
            ':paymentName' => self::SEZZLE_PAYMENT_METHOD_NAME,
        ]);
    }

    /**
     * @param Connection $connection
     * @return int
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     *
     */
    public function getPaymentId(Connection $connection)
    {
        $sql = 'SELECT `id` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (int) $connection->fetchColumn($sql, [
            ':paymentName' => self::SEZZLE_PAYMENT_METHOD_NAME,
        ]);
    }
}
