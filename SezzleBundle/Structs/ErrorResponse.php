<?php

namespace SezzlePayment\SezzleBundle\Structs;

class ErrorResponse
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $debugUuid;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getDebugUuid()
    {
        return $this->debugUuid;
    }

    /**
     * @param string $debugUuid
     */
    public function setDebugUuid($debugUuid)
    {
        $this->debugUuid = $debugUuid;
    }

    /**
     * @return ErrorResponse|null
     */
    public static function fromArray(array $data)
    {
        if (!$data) {
            return null;
        }

        $result = new self();
        $result->setCode($data['code']);
        $result->setLocation($data['location']);
        $result->setMessage($data['message']);
        $result->setDebugUuid($data['debug_uuid']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
