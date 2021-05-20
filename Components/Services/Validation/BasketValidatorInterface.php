<?php

namespace Sezzle\Components\Services\Validation;

use Sezzle\SezzleBundle\Structs\Order;

interface BasketValidatorInterface
{
    /**
     * Validates the basket using the shopware basket and the session response from Sezzle
     *
     * @return bool
     */
    public function validate(array $basket, array $customer, Order $order);
}
