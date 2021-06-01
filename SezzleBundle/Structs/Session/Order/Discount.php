<?php

namespace Sezzle\SezzleBundle\Structs\Session\Order;

class Discount
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount(Amount $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param array|null $data
     * @return Discount
     */
    public static function fromArray(array $data = null)
    {
        $result = new self();

        if ($data === null) {
            return $result;
        }

        $result->setName($data['name']);
        $result->setAmount($data['amount']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'amount' => $this->getAmount()->toArray(),
        ];
    }
}
