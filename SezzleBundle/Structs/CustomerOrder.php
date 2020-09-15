<?php

namespace SwagPaymentSezzle\SezzleBundle\Structs;

use SwagPaymentSezzle\SezzleBundle\Structs\Order\Authorization;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount;

class CustomerOrder
{
    /**
     * @var string
     */
    private $intent;
    /**
     * @var string
     */
    private $referenceId;
    /**
     * @var Amount
     */
    private $orderAmount;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return Amount
     */
    public function getOrderAmount()
    {
        return $this->orderAmount;
    }

    /**
     * @param Amount $orderAmount
     */
    public function setOrderAmount(Amount $orderAmount)
    {
        $this->orderAmount = $orderAmount;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return Authorization
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param Authorization $authorization
     */
    public function setAuthorization(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }






    /**
     * @param array $data
     * @return CustomerOrder
     */
    public static function fromArray(array $data)
    {
        $result = new self();

        $result->setUuid($data['uuid']);
        if (array_key_exists('authorization', $data)) {
            $result->setAuthorization(Authorization::fromArray($data['authorization']));
        }
        $result->setIntent($data['customer']);
        if (array_key_exists('order_amount', $data)) {
            $result->setOrderAmount(Amount::fromArray($data['order_amount']));
        }
        $result->setReferenceId($data['customer']);


        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'intent' => $this->getIntent(),
            'reference_id' => $this->getReferenceId(),
            'order_amount' => $this->getOrderAmount()->toArray()
        ];
    }
}
