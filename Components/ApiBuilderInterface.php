<?php

namespace SwagPaymentSezzle\Components;

use SwagPaymentSezzle\SezzleBundle\Structs\CustomerOrder;
use SwagPaymentSezzle\SezzleBundle\Structs\Order\Capture;
use SwagPaymentSezzle\SezzleBundle\Structs\Session;

/**
 * Interface ApiBuilderInterface
 * @package SwagPaymentSezzle\Components
 */
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

    /**
     * @param ApiBuilderParameters $params
     * @return Capture
     */
    public function getCapturePayload(ApiBuilderParameters $params);

    /**
     * @param ApiBuilderParameters $params
     * @return CustomerOrder
     */
    public function getCustomerOrderPayload(ApiBuilderParameters $params);
}
