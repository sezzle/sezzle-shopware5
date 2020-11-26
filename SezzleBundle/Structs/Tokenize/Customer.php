<?php

namespace SwagPaymentSezzle\SezzleBundle\Structs\Tokenize;

use DateTime;

class Customer
{
    /**
     * Scopes expressed in the form of resource URL endpoints. The value of the scope parameter
     * is expressed as a list of space-delimited, case-sensitive strings.
     *
     * @var string
     */
    private $uuid;

    /**
     * The access token issued by PayPal. After the access token
     * expires (see $expiresIn), you must request a new access token.
     *
     * @var string
     */
    private $expiration;

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
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param string $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }



    /**
     * @param array $data
     * @return Customer
     */
    public static function fromArray(array $data)
    {
        $token = new self();

        $token->setUuid($data['uuid']);
        $token->setExpiration($data['expiration']);

        return $token;
    }
}
