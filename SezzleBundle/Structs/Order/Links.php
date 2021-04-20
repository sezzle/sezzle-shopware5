<?php


namespace Sezzle\SezzleBundle\Structs\Order;


use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Link;

class Links
{
    /**
     * @var string
     */
    public $href; //String
    /**
     * @var string
     */
    public $method; //String
    /**
     * @var string
     */
    public $rel; //String

    /**
     * @return string
     */
    public function getHref() {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref($href) {
        $this->href = $href;
    }


    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }


    /**
     * @return string
     */
    public function getRel() {
        return $this->rel;
    }

    /**
     * @param string $rel
     */
    public function setRel($rel) {
        $this->rel = $rel;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'href' => $this->getHref(),
            'rel' => $this->getRel(),
            'method' => $this->getMethod(),
        ];
    }

    /**
     * @param array $data
     * @return Links
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->setHref($data['href']);
        $result->setRel($data['rel']);
        $result->setMethod($data['method']);

        return $result;
    }


}
