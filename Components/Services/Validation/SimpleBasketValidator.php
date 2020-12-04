<?php

namespace SwagPaymentSezzle\Components\Services\Validation;

use SwagPaymentSezzle\SezzleBundle\Structs\Order;

class SimpleBasketValidator implements BasketValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $basket, array $customer, Order $order)
    {
        if ($customer['additional']['charge_vat']) {
            $basketAmount = number_format($basket['AmountNumeric'], 2);
        } else {
            $basketAmount = number_format($basket['AmountNetNumeric'], 2);
        }
        $paymentAmount = number_format($order->getOrderAmount()->getAmountInCents(), 2);

        if ($customer['additional']['charge_vat'] && $basket['AmountWithTaxNumeric']) {
            $basketAmount = number_format($basket['AmountWithTaxNumeric'], 2);
        }

        return $basketAmount === $paymentAmount;
    }
}
