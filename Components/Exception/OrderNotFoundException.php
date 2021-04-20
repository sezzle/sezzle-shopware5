<?php

namespace Sezzle\Components\Exception;

class OrderNotFoundException extends \RuntimeException
{
    /**
     * OrderNotFoundException constructor.
     * @param string $parameter
     * @param string $value
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($parameter, $value, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Could not find order with search parameter "%s" and value "%s"', $parameter, $value);
        parent::__construct($message, $code, $previous);
    }
}
