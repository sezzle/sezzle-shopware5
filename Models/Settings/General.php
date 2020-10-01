<?php

namespace SwagPaymentSezzle\Models\Settings;

use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="swag_payment_sezzle_settings_general")
 */
class General extends ModelEntity
{
    const MERCHANT_LOCATION_GERMANY = 'germany';
    const MERCHANT_LOCATION_OTHER = 'other';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="shop_id", type="string", nullable=false)
     */
    private $shopId;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var string
     * @ORM\Column(name="merchant_uuid", type="string")
     */
    private $merchantUuid;

    /**
     * @var string
     * @ORM\Column(name="public_key", type="string")
     */
    private $publicKey;

    /**
     * @var string
     * @ORM\Column(name="private_key", type="string")
     */
    private $privateKey;

    /**
     * @var bool
     * @ORM\Column(name="sandbox", type="boolean")
     */
    private $sandbox;

    /**
     * @var bool
     * @ORM\Column(name="tokenize", type="boolean")
     */
    private $tokenize;

    /**
     * @var string
     * @ORM\Column(name="payment_action", type="string")
     */
    private $paymentAction;

    /**
     * @var int
     * @ORM\Column(name="log_level", type="integer")
     */
    private $logLevel;

    /**
     * @var bool
     * @ORM\Column(name="display_errors", type="boolean", nullable=false)
     */
    private $displayErrors;

    /**
     * @var string
     * @ORM\Column(name="merchant_location", type="string", nullable=false)
     */
    private $merchantLocation;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getMerchantUuid()
    {
        return $this->merchantUuid;
    }

    /**
     * @param string $merchantUuid
     */
    public function setMerchantUuid($merchantUuid)
    {
        $this->merchantUuid = $merchantUuid;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @return bool
     */
    public function getSandbox()
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return bool
     */
    public function getTokenize()
    {
        return $this->tokenize;
    }

    /**
     * @param bool $tokenize
     */
    public function setTokenize($tokenize)
    {
        $this->tokenize = $tokenize;
    }

    /**
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->paymentAction;
    }

    /**
     * @param string $paymentAction
     */
    public function setPaymentAction($paymentAction)
    {
        $this->paymentAction = $paymentAction;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return bool
     */
    public function getDisplayErrors()
    {
        return $this->displayErrors;
    }

    /**
     * @param bool $displayErrors
     */
    public function setDisplayErrors($displayErrors)
    {
        $this->displayErrors = $displayErrors;
    }

    /**
     * @return string
     */
    public function getMerchantLocation()
    {
        return $this->merchantLocation;
    }

    /**
     * @param string $merchantLocation
     */
    public function setMerchantLocation($merchantLocation)
    {
        $this->merchantLocation = $merchantLocation;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
