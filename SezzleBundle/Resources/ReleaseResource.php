<?php

namespace SwagPaymentSezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\SezzleBundle\RequestType;
use SwagPaymentSezzle\SezzleBundle\RequestUri;
use SwagPaymentSezzle\SezzleBundle\Services\ClientService;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount;

class ReleaseResource
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
     * @param Amount $releasePayload
     * @return array
     * @throws RequestException
     */
    public function create($orderUuid, Amount $releasePayload)
    {
        $url = sprintf(RequestUri::RELEASE_RESOURCE, $orderUuid);
        return $this->clientService->sendRequest(RequestType::POST, $url, $releasePayload->toArray());
    }
}
