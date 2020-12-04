<?php

namespace SwagPaymentSezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\SezzleBundle\RequestType;
use SwagPaymentSezzle\SezzleBundle\RequestUri;
use SwagPaymentSezzle\SezzleBundle\Services\ClientService;
use SwagPaymentSezzle\SezzleBundle\Structs\AuthCredentials;

class TokenResource
{
    /**
     * @var ClientService
     */
    private $client;

    public function __construct(ClientService $client)
    {
        $this->client = $client;
    }

    /**
     * @param AuthCredentials $credentials
     * @return array
     * @throws RequestException
     */
    public function get(AuthCredentials $credentials)
    {
        return $this->client->sendRequest(RequestType::POST, RequestUri::TOKEN_RESOURCE, $credentials->toArray(), false);
    }
}
