<?php

namespace SezzlePayment\SezzleBundle\Structs;

use SezzlePayment\SezzleBundle\Structs\Session\Customer;
use SezzlePayment\SezzleBundle\Structs\Session\Order;
use SezzlePayment\SezzleBundle\Structs\Session\Tokenize;
use SezzlePayment\SezzleBundle\Structs\Session\Url;

class Session
{
    /**
     * @var Url
     */
    private $cancelUrl;

    /**
     * @var Url
     */
    private $completeUrl;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Tokenize
     */
    private $tokenize;

    /**
     * @return Url
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param Url $cancelUrl
     */
    public function setCancelUrl(Url $cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * @return Url
     */
    public function getCompleteUrl()
    {
        return $this->completeUrl;
    }

    /**
     * @param Url $completeUrl
     */
    public function setCompleteUrl(Url $completeUrl)
    {
        $this->completeUrl = $completeUrl;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Tokenize
     */
    public function getTokenize()
    {
        return $this->tokenize;
    }

    /**
     * @param Tokenize $tokenize
     */
    public function setTokenize(Tokenize $tokenize)
    {
        $this->tokenize = $tokenize;
    }

    /**
     * @param array $data
     * @return Session
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

//        $result->setCancelUrl($data['cancel_url']);
//        $result->setCompleteUrl($data['complete_url']);
//        $result->setCustomer($data['customer']);

        if (array_key_exists('order', $data)) {
            $result->setOrder(Order::fromArray($data['order']));
        }

        if (array_key_exists('tokenize', $data)) {
            $result->setTokenize(Tokenize::fromArray($data['tokenize']));
        }


        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'cancel_url' => $this->getCancelUrl()->toArray(),
            'complete_url' => $this->getCompleteUrl()->toArray(),
            'customer' => $this->getCustomer()->toArray(),
            'order' => $this->getOrder()->toArray()
        ];
    }
}
