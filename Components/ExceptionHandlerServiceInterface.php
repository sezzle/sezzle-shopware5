<?php

namespace Sezzle\Components;

use Sezzle\Components\Exception\SezzleApiException;

interface ExceptionHandlerServiceInterface
{
    /**
     * @param string $currentAction
     *
     * @return SezzleApiException The error message and name extracted from the exception
     */
    public function handle(\Exception $e, $currentAction);
}
