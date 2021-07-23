<?php

namespace SezzlePayment\Components;

use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Payment\Payment;

class PaymentMethodProvider
{
    /**
     * The technical name of the sezzle payment method.
     */
    const SEZZLE_PAYMENT_METHOD_NAME = 'Sezzle';
    const SEZZLE_LOGO = 'https://d34uoa9py2cgca.cloudfront.net/branding/sezzle-logos/png/sezzle-logo-sm-100w.png'; //TODO check if this is valid for all time (use local?)
    const SEZZLE_ADDITIONAL_DESCRIPTION = ''; //'Pay later with 0% interest'; //TODO german (add translation?)

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
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
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     */
    public function setPaymentMethodActiveFlag($active)
    {
        try {
            $paymentMethod = $this->getPaymentMethodModel();
            if ($paymentMethod) {
                $paymentMethod->setActive($active);
                $this->connectShippingMethods();
                $this->modelManager->persist($paymentMethod);
                $this->modelManager->flush($paymentMethod);
            }
        } catch (Exception $e) {

        }
    }

    /**
     * @return bool
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     *
     */
    public function getPaymentMethodActiveFlag()
    {
        $sql = 'SELECT `active` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (bool)$this->modelManager->getConnection()->fetchColumn($sql, [
            ':paymentName' => self::SEZZLE_PAYMENT_METHOD_NAME,
        ]);
    }

    /**
     * @return int
     * @see PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME
     *
     */
    public function getPaymentId()
    {
        $sql = 'SELECT `id` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (int)$this->modelManager->getConnection()->fetchColumn($sql, [
            ':paymentName' => self::SEZZLE_PAYMENT_METHOD_NAME,
        ]);
    }

    public function createPaymentMethod()
    {
        if ($this->getPaymentId()) {
            return;
        }

        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(-100);
        $payment->setName(PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME);
        $payment->setDescription('Sezzle');
        $payment->setAdditionalDescription($this->getAdditionalDescription());
        $payment->setAction('Sezzle');

        $this->modelManager->persist($payment);
        $this->modelManager->flush($payment);

    }


    /**
     * @return string
     */
    /*
    private function getUnifiedPaymentLogo()
    {
        return '<img src="' . self::SEZZLE_LOGO . '" alt="Logo Sezzle" class="sezzle-payment-logo" />';
    }
    */

    /**
     * @return string
     */
    private function getAdditionalDescription()
    {
        return self::SEZZLE_ADDITIONAL_DESCRIPTION;
    }

    /**
     * Connect to all shipping methods
     */
    public function connectShippingMethods()
    {
        try {
            $paymentMethod = $this->getPaymentMethodModel();
            $dispatchRepository = $this->modelManager->getRepository(Dispatch::class);
            /** @var Dispatch[] $methods */
            $methods = $dispatchRepository->findAll();

            foreach ($methods as $method) {
                foreach ($method->getPayments() as $payment) {
                    if ($payment->getId() === $paymentMethod->getId()) {
                        //cancel if connection already exists
                        return;
                    }
                }
            }

            foreach ($methods as $method) {
                foreach ($method->getPayments() as $payment) {
                    if ($payment->getId() === $paymentMethod->getId()) {
                        continue 2;
                    }
                }
                $method->getPayments()->add($paymentMethod);
                $this->modelManager->persist($method);
                $this->modelManager->flush($method);
            }

        } catch (Exception $e) {

        }
    }
}
