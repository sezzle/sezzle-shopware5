<?php

namespace Sezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use Sezzle\SezzleBundle\RequestType;
use Sezzle\SezzleBundle\RequestUri;
use Sezzle\SezzleBundle\Services\ClientService;
use Sezzle\SezzleBundle\Structs\Order\Capture;

class CaptureResource
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
     * @param Capture $capturePayload
     * @return array
     * @throws RequestException
     */
    public function create($orderUuid, Capture $capturePayload)
    {
        $url = sprintf(RequestUri::CAPTURE_RESOURCE, $orderUuid);
        return $this->clientService->sendRequest(RequestType::POST, $url, $capturePayload->toArray());
    }
}
