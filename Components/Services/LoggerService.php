<?php

namespace SezzlePayment\Components\Services;

use Shopware\Components\Logger;

class LoggerService
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SettingsService
     */
    private $settings;

    public function __construct(Logger $baseLogger, SettingsService $settings)
    {
        $this->logger   = $baseLogger;
        $this->settings = $settings;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = [])
    {
        if ($this->settings->getLogLevel() === 'all') {
            $finalMessage = 'Sezzle: ' . $message;
            $this->logger->warning($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notify($message, array $context = [])
    {
        if ($this->settings->getLogLevel() === 'all') {
            $finalMessage = 'Sezzle: ' . $message;
            $this->logger->debug($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = [])
    {
        $finalMessage = 'Sezzle: ' . $message;
        $this->logger->error($finalMessage, $context);
    }
}
