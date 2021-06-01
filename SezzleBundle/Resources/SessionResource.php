<?php

namespace Sezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use Sezzle\SezzleBundle\RequestType;
use Sezzle\SezzleBundle\RequestUri;
use Sezzle\SezzleBundle\Services\ClientService;
use Sezzle\SezzleBundle\Structs\Session;

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
