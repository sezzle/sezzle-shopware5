<?php

namespace SwagPaymentSezzle\Components;

use SwagPaymentSezzle\SezzleBundle\Structs\Session;

interface ApiBuilderInterface
{
    const CUSTOMER_GROUP_USE_GROSS_PRICES = 'customerGroupUseGrossPrices';

    /**
     * The function returns an array with all parameters that are expected by the Sezzle API.
     *
     * @param ApiBuilderParameters $params
     * @return Session
     */
    public function getSession(ApiBuilderParameters $params);

    public function getCapturePayload(ApiBuilderParameters $params);
    public function getCustomerOrderPayload(ApiBuilderParameters $params);
}
