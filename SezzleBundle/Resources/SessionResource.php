<?php

namespace SwagPaymentSezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\SezzleBundle\RequestType;
use SwagPaymentSezzle\SezzleBundle\RequestUri;
use SwagPaymentSezzle\SezzleBundle\Services\ClientService;
use SwagPaymentSezzle\SezzleBundle\Structs\Session;

class SessionResource
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
     * @param Session $session
     * @return array
     * @throws RequestException
     */
    public function create(Session $session)
    {
        return $this->clientService->sendRequest(RequestType::POST, RequestUri::SESSION_RESOURCE, $session->toArray());
    }

    /**
     * @param string $sessionId
     *
     * @throws RequestException
     *
     * @return array
     */
    public function get($sessionId)
    {
        return $this->clientService->sendRequest(RequestType::GET, RequestUri::SESSION_RESOURCE . '/' . $sessionId);
    }
}
