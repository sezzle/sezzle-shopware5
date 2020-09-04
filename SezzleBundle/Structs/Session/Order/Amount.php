<?php

namespace SwagPaymentSezzle\SezzleBundle\Structs\Session\Order;

class Amount
{
    /**
     * @var int
     */
    private $amountInCents;

    /**
     * @var string
     */
    private $curreny;

    /**
     * @return int
     */
    public function getAmountInCents()
    {
        return $this->amountInCents;
    }

    /**
     * @param int $amountInCents
     */
    public function setAmountInCents($amountInCents)
    {
        $this->amountInCents = $amountInCents;
    }

    /**
     * @return string
     */
    public function getCurreny()
    {
        return $this->curreny;
    }

    /**
     * @param string $curreny
     */
    public function setCurreny($curreny)
    {
        $this->curreny = $curreny;
    }

    /**
     * @param array|null $data
     * @return Amount
     */
    public static function fromArray(array $data = null)
    {
        $result = new self();

        if ($data === null) {
            return $result;
        }

        $result->setAmountInCents($data['amount_in_cents']);
        $result->setCurreny($data['currency']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'amount_in_cents' => $this->getAmountInCents(),
            'currency' => $this->getCurreny(),
        ];
    }
}
