<?php

namespace SezzlePayment\SezzleBundle\Structs\Session;

use SezzlePayment\SezzleBundle\Structs\Session\Order\Amount;
use SezzlePayment\SezzleBundle\Structs\Session\Order\Discount;
use SezzlePayment\SezzleBundle\Structs\Session\Order\Item;

class Order
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
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $requiresShippingInfo;

    /**
     * @var Item[]
     */
    private $items;

    /**
     * @var Discount[]
     */
    private $discounts;

    /**
     * @var Amount
     */
    private $shippingAmount;

    /**
     * @var Amount
     */
    private $taxAmount;

    /**
     * @var Amount
     */
    private $orderAmount;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $checkoutUrl;

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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isRequiresShippingInfo()
    {
        return $this->requiresShippingInfo;
    }

    /**
     * @param bool $requiresShippingInfo
     */
    public function setRequiresShippingInfo($requiresShippingInfo)
    {
        $this->requiresShippingInfo = $requiresShippingInfo;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return Discount[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param Discount[] $discounts
     */
    public function setDiscounts(array $discounts)
    {
        $this->discounts = $discounts;
    }

    /**
     * @return Amount
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @param Amount $shippingAmount
     */
    public function setShippingAmount(Amount $shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;
    }

    /**
     * @return Amount
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param Amount $taxAmount
     */
    public function setTaxAmount(Amount $taxAmount)
    {
        $this->taxAmount = $taxAmount;
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
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->checkoutUrl;
    }

    /**
     * @param string $checkoutUrl
     */
    public function setCheckoutUrl($checkoutUrl)
    {
        $this->checkoutUrl = $checkoutUrl;
    }

    /**
     * @param array|null $data
     * @return Order
     */
    public static function fromArray(array $data = null)
    {
        $result = new self();

        if ($data === null) {
            return $result;
        }

//        $result->setIntent($data['intent']);
//        $result->setReferenceId($data['reference_id']);
//        $result->setDescription($data['description']);
//        $result->setRequiresShippingInfo($data['requires_shipping_info']);
//        $result->setItems($data['items']);
//        $result->setDiscounts($data['discounts']);
//        $result->setShippingAmount($data['shipping_amount']);
//        $result->setTaxAmount($data['tax_amount']);
//        $result->setOrderAmount($data['order_amount']);

        $result->setCheckoutUrl($data['checkout_url']);
        $result->setUuid($data['uuid']);

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
            'description' => $this->getDescription(),
            'requires_shipping_info' => $this->isRequiresShippingInfo(),
            'items' => $this->getItems(),
            'discounts' => $this->getDiscounts(),
            'shipping_amount' => $this->getShippingAmount()->toArray(),
            'tax_amount' => $this->getTaxAmount()->toArray(),
            'order_amount' => $this->getOrderAmount()->toArray()
        ];
    }
}
