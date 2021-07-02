<?php

namespace SezzlePayment\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SezzlePayment\SezzleBundle\RequestType;
use SezzlePayment\SezzleBundle\RequestUri;
use SezzlePayment\SezzleBundle\Services\ClientService;
use SezzlePayment\SezzleBundle\Structs\AuthCredentials;

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
