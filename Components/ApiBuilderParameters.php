<?php

namespace SezzlePayment\Components;

/**
 * Class ApiBuilderParameters
 * @package SezzlePayment\Components
 */
class ApiBuilderParameters
{
    /**
     * @var array
     */
    private $userData;

    /**
     * @var array
     */
    private $basketData;

    /**
     * @var string
     */
    private $basketUniqueId;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string
     */
    private $paymentToken;

    /**
     * @var array
     */
    private $order;

    /**
     * @return array
     */
    public function getUserData()
    {
        return $this->userData;
    }

    /**
     * @param array $userData
     */
    public function setUserData($userData)
    {
        $this->userData = $userData;
    }

    /**
     * @return array
     */
    public function getBasketData()
    {
        return $this->basketData;
    }

    /**
     * @param array $basketData
     */
    public function setBasketData($basketData)
    {
        $this->basketData = $basketData;
    }

    /**
     * @return string
     */
    public function getBasketUniqueId()
    {
        return $this->basketUniqueId;
    }

    /**
     * @param string $basketUniqueId
     */
    public function setBasketUniqueId($basketUniqueId)
    {
        $this->basketUniqueId = $basketUniqueId;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return string|null
     */
    public function getPaymentToken()
    {
        return $this->paymentToken;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param array $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @param string|null $paymentToken
     */
    public function setPaymentToken($paymentToken)
    {
        $this->paymentToken = $paymentToken;
    }
}
