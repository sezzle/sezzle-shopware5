<?php


namespace SwagPaymentSezzle\SezzleBundle\Structs\Order;


use SwagPaymentSezzle\SezzleBundle\Structs\Session;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order;
use SwagPaymentSezzle\SezzleBundle\Structs\Session\Order\Amount;

/**
 * Class Capture
 * @package SwagPaymentSezzle\SezzleBundle\Structs\Order
 */
class Capture
{
    /**
     * @var string
     */
    public $uuid;

    /**
     * @var Amount
     */
    public $captureAmount;
    /**
     * @var bool
     */
    public $partialCapture;

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
    public function getCaptureAmount()
    {
        return $this->captureAmount;
    }

    /**
     * @param Amount $captureAmount
     */
    public function setCaptureAmount(Amount $captureAmount)
    {
        $this->captureAmount = $captureAmount;
    }

    /**
     * @return bool
     */
    public function isPartialCapture()
    {
        return $this->partialCapture;
    }

    /**
     * @param bool $partialCapture
     */
    public function setPartialCapture(bool $partialCapture)
    {
        $this->partialCapture = $partialCapture;
    }

    /**
     * @param array $data
     * @return Capture
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setUuid($data['uuid']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'capture_amount' => $this->getCaptureAmount()->toArray(),
            'partial_capture' => $this->isPartialCapture()
        ];
    }



}
