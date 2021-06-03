<?php

namespace SezzlePayment\SezzleBundle\Components;

interface LoggerServiceInterface
{
    /**
     * Adds a notification to the logfile.
     *
     * @param string $message
     */
    public function notify($message, array $context = []);

    /**
     * Adds a warning to the logfile.
     *
     * @param string $message
     */
    public function warning($message, array $context = []);

    /**
     * Adds an error to the logfile.
     *
     * @param string $message
     */
    public function error($message, array $context = []);
}
