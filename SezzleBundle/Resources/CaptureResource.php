<?php

namespace SezzlePayment\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SezzlePayment\SezzleBundle\RequestType;
use SezzlePayment\SezzleBundle\RequestUri;
use SezzlePayment\SezzleBundle\Services\ClientService;
use SezzlePayment\SezzleBundle\Structs\Order\Capture;

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
