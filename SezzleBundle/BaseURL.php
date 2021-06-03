<?php

namespace SezzlePayment\SezzleBundle;

class BaseURL
{
    const SANDBOX = 'https://sandbox.gateway.sezzle.com/v2/';
    const LIVE = 'https://gateway.sezzle.com/v2/';

    const GATEWAY_URL = "https://%sgateway.%s/%s/";
    const SEZZLE_DOMAIN = "%ssezzle.com";
}
