<?php

namespace SwagPaymentSezzle\SezzleBundle;

class RequestUri
{
    const SESSION_RESOURCE = 'session';
    const TOKEN_RESOURCE = 'authentication';
    const REFUND_RESOURCE = 'order/%s/refund';
    const RELEASE_RESOURCE = 'order/%s/release';
    const CAPTURE_RESOURCE = 'order/%s/capture';
    const CUSTOMER_ORDER_RESOURCE = 'customer/%s/order';
    const TOKENIZE_RESOURCE = 'token/%s/session';
    const ORDER_RESOURCE = 'order';
}
