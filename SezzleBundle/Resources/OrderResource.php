<?php

namespace Sezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use Sezzle\SezzleBundle\RequestType;
use Sezzle\SezzleBundle\RequestUri;
use Sezzle\SezzleBundle\Services\ClientService;

class OrderResource
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
     * @param string $orderUuid
     * @return array
     * @throws RequestException
     */
    public function get($orderUuid)
    {
        return $this->clientService->sendRequest(RequestType::GET, RequestUri::ORDER_RESOURCE . '/' . $orderUuid);
    }

    public function update($orderUuid, $request)
    {
        return $this->clientService->sendRequest(RequestType::PATCH, RequestUri::ORDER_RESOURCE . '/' . $orderUuid, $request);
    }
}
