<?php

namespace SwagPaymentSezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\SezzleBundle\RequestType;
use SwagPaymentSezzle\SezzleBundle\RequestUri;
use SwagPaymentSezzle\SezzleBundle\Services\ClientService;

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
