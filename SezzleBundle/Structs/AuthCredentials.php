<?php

namespace SezzlePayment\SezzleBundle\Structs;

class AuthCredentials
{
    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     * @return AuthCredentials
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
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
     * @return AuthCredentials
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    /**
     * @param array $data
     * @return AuthCredentials
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setPublicKey($data['public_key']);
        $result->setPrivateKey($data['private_key']);
        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'public_key' => $this->getPublicKey(),
            'private_key' => $this->getPrivateKey()
        ];
    }
}
