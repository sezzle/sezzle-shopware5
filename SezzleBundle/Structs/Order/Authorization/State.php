<?php

namespace SwagPaymentSezzle\SezzleBundle\Structs\Order\Authorization;

use SwagPaymentSezzle\SezzleBundle\Structs\Order\Authorization;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount;

class State
{
    /**
     * @var string
     */
    public $uuid; //String
    /**
     * @var Amount
     */
    public $amount; //

    /**
     * @return string
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }


    /**
     * @return Amount
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount(Amount $amount) {
        $this->amount = $amount;
    }

    /**
     * @param array $data
     * @return State
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setUuid($data['uuid']);
        $result->setAmount(Amount::fromArray($data['amount']));

        return $result;
    }

}
