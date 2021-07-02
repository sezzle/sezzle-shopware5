<?php

namespace SezzlePayment\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SezzlePayment\SezzleBundle\RequestType;
use SezzlePayment\SezzleBundle\RequestUri;
use SezzlePayment\SezzleBundle\Services\ClientService;

class TokenizeResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param string $token
     * @return array
     * @throws RequestException
     */
    public function get($token)
    {
        $url = sprintf(RequestUri::TOKENIZE_RESOURCE, $token);
        return $this->clientService->sendRequest(RequestType::GET, $url);
    }
}
