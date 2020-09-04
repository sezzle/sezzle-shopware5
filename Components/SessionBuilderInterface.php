<?php

namespace SwagPaymentSezzle\Components;

use SwagPaymentSezzle\SezzleBundle\Structs\Session;

interface SessionBuilderInterface
{
    const CUSTOMER_GROUP_USE_GROSS_PRICES = 'customerGroupUseGrossPrices';

    /**
     * The function returns an array with all parameters that are expected by the Sezzle API.
     *
     * @param SessionBuilderParameters $params
     * @return Session
     */
    public function getSession(SessionBuilderParameters $params);
}
