<?php

namespace SezzlePayment\Components;

use SezzlePayment\Components\Exception\SezzleApiException;

interface ExceptionHandlerServiceInterface
{
    /**
     * @param \Exception $e
     * @param string $currentAction
     *
     * @return SezzleApiException The error message and name extracted from the exception
     */
    public function handle(\Exception $e, $currentAction);
}
