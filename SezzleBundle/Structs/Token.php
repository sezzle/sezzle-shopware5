<?php

namespace SwagPaymentSezzle\SezzleBundle\Structs;

use DateTime;

class Token
{
    /**
     * Scopes expressed in the form of resource URL endpoints. The value of the scope parameter
     * is expressed as a list of space-delimited, case-sensitive strings.
     *
     * @var string
     */
    private $token;

    /**
     * The access token issued by PayPal. After the access token
     * expires (see $expiresIn), you must request a new access token.
     *
     * @var string
     */
    private $expirationDate;

    /**
     * The type of the token issued as described in OAuth2.0 RFC6749,
     * Section 7.1. Value is case insensitive.
     *
     * @var string
     */
    private $merchantUuid;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param string $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
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
     * @param array $data
     * @return Token
     */
    public static function fromArray(array $data)
    {
        $token = new self();

        $token->setToken($data['token']);
        $token->setExpirationDate($data['expiration_date']);
        $token->setMerchantUuid($data['merchant_uuid']);

        return $token;
    }
}
