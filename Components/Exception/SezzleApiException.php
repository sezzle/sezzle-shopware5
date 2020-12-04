<?php

namespace SwagPaymentSezzle\Components\Exception;

class SezzleApiException extends \Exception
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string $message
     */
    public function __construct($name, $message)
    {
        $this->name = $name;

        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCompleteMessage()
    {
        return $this->getMessage() . ' [' . $this->getName() . ']';
    }
}
