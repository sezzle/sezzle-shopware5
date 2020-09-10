<?php

namespace SwagPaymentSezzle\SezzleBundle;

class RequestUri
{
    const SESSION_RESOURCE = 'session';
    const WEBHOOK_RESOURCE = 'notifications/webhooks';
    const TOKEN_RESOURCE = 'authentication';
    const SALE_RESOURCE = 'payments/sale';
    const REFUND_RESOURCE = 'payments/refund';
    const AUTHORIZATION_RESOURCE = 'payments/authorization';
    const CAPTURE_RESOURCE = 'order/%s/capture';
    const ORDER_RESOURCE = 'payments/orders';
    const FINANCING_RESOURCE = 'credit/calculated-financing-options';
}
