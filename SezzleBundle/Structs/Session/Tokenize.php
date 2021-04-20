<?php

namespace Sezzle\SezzleBundle\Structs\Session;

use DateTime;
use Sezzle\SezzleBundle\Structs\Order\Links;

class Tokenize
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTime
     */
    private $expiration;

    /**
     * @var string
     */
    private $approvalUrl;

    /**
     * @var Links[]
     */
    private $links;

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
     * @return string
     */
    public function getApprovalUrl()
    {
        return $this->approvalUrl;
    }

    /**
     * @param string $approvalUrl
     */
    public function setApprovalUrl($approvalUrl)
    {
        $this->approvalUrl = $approvalUrl;
    }

    /**
     * @return Links[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param Links[] $links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * @param array|null $data
     * @return Tokenize
     */
    public static function fromArray(array $data = null)
    {
        $result = new self();

        if ($data === null) {
            return $result;
        }

        $result->setToken($data['token']);
        $result->setExpiration($data['expiration']);

        return $result;
    }
}
