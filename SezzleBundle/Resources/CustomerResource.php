<?php

namespace Sezzle\SezzleBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use Sezzle\SezzleBundle\RequestType;
use Sezzle\SezzleBundle\RequestUri;
use Sezzle\SezzleBundle\Services\ClientService;
use Sezzle\SezzleBundle\Structs\CustomerOrder;

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
