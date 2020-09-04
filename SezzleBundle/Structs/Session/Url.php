<?php

namespace SwagPaymentSezzle\SezzleBundle\Structs\Session;

class Url
{
    /**
     * @var string
     */
    private $href;

    /**
     * @var string
     */
    private $method;

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param array|null $data
     * @return Url
     */
    public static function fromArray(array $data = null)
    {
        $result = new self();

        if ($data === null) {
            return $result;
        }

        $result->setHref($data['href']);
        $result->setMethod($data['method']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'href' => $this->getHref(),
            'method' => $this->getMethod(),
        ];
    }
}
