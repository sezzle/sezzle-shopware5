<?php

namespace SezzlePayment\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SezzlePayment\SezzleBundle\RequestType;
use SezzlePayment\SezzleBundle\RequestUri;
use SezzlePayment\SezzleBundle\Services\ClientService;
use SezzlePayment\SezzleBundle\Structs\CustomerOrder;

class CustomerResource
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
     * @param string $customerUuid
     * @param CustomerOrder $customerOrderPayload
     * @return array
     * @throws RequestException
     */
    public function create($customerUuid, CustomerOrder $customerOrderPayload)
    {
        $url = sprintf(RequestUri::CUSTOMER_ORDER_RESOURCE, $customerUuid);
        return $this->clientService->sendRequest(RequestType::POST, $url, $customerOrderPayload->toArray());
    }
}
