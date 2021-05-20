<?php

namespace Sezzle\SezzleBundle\Structs\Order;

use Sezzle\SezzleBundle\Structs\Order\Authorization\State;
use Sezzle\SezzleBundle\Structs\Session\Order\Amount;

class Authorization
{
    /**
     * @var Amount
     */
    public $authorizationAmount; //AuthorizationAmount
    /**
     * @var bool
     */
    public $approved; //boolean
    /**
     * @var string
     */
    public $expiration; //Date
    /**
     * @var Authorization\State[]
     */
    public $releases; //array(Release)
    /**
     * @var Authorization\State[]
     */
    public $captures; //array(Capture)
    /**
     * @var Authorization\State[]
     */
    public $refunds; //array(Refund)

    /**
     * @return Amount
     */
    public function getAuthorizationAmount() {
        return $this->authorizationAmount;
    }

    /**
     * @param Amount $authorizationAmount
     */
    public function setAuthorizationAmount(Amount $authorizationAmount) {
        $this->authorizationAmount = $authorizationAmount;
    }


    /**
     * @return bool
     */
    public function isApproved() {
        return $this->approved;
    }

    /**
     * @param bool $approved
     */
    public function setApproved($approved) {
        $this->approved = $approved;
    }


    /**
     * @return string
     */
    public function getExpiration() {
        return $this->expiration;
    }

    /**
     * @param string $expiration
     */
    public function setExpiration($expiration) {
        $this->expiration = $expiration;
    }


    /**
     * @return Authorization\State[]
     */
    public function getReleases() {
        return $this->releases;
    }

    /**
     * @param Authorization\State[] $releases
     */
    public function setReleases(array $releases) {
        $this->releases = $releases;
    }


    /**
     * @return Authorization\State[]
     */
    public function getCaptures() {
        return $this->captures;
    }

    /**
     * @param Authorization\State[] $captures
     */
    public function setCaptures(array $captures) {
        $this->captures = $captures;
    }


    /**
     * @return Authorization\State[]
     */
    public function getRefunds() {
        return $this->refunds;
    }

    /**
     * @param Authorization\State[] $refunds
     */
    public function setRefunds(array $refunds) {
        $this->refunds = $refunds;
    }

    /**
     * @param array $data
     * @return Authorization
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setExpiration($data['expiration']);
        $result->setApproved($data['approved']);
        if (array_key_exists('authorization_amount', $data)) {
            $result->setAuthorizationAmount(Amount::fromArray($data['authorization_amount']));
        }

        $captures = [];
        foreach ($data['captures'] as $item) {
            $captures[] = State::fromArray($item);
        }

        $result->setCaptures($captures);

        $releases = [];
        foreach ($data['releases'] as $item) {
            $releases[] = State::fromArray($item);
        }
        $result->setReleases($releases);

        $refunds = [];
        foreach ($data['refunds'] as $item) {
            $refunds[] = State::fromArray($item);
        }
        $result->setRefunds($refunds);

        return $result;
    }

}
