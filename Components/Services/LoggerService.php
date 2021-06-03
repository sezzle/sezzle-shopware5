<?php

namespace SezzlePayment\Components\Services;

use Shopware\Components\Logger;
use SezzlePayment\SezzleBundle\Components\LoggerServiceInterface;
use SezzlePayment\SezzleBundle\Components\SettingsServiceInterface;

class LoggerService implements LoggerServiceInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SettingsServiceInterface
     */
    private $settings;

    public function __construct(Logger $baseLogger, SettingsServiceInterface $settings)
    {
        $this->logger = $baseLogger;
        $this->settings = $settings;
    }

    /**
     * @param string $message
     */
    public function warning($message, array $context = [])
    {
        if (!$this->settings->hasSettings()) {
            return;
        }

        if ((int) $this->settings->get('log_level') === 1) {
            $finalMessage = 'Sezzle: ' . $message;
            $this->logger->warning($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     */
    public function notify($message, array $context = [])
    {
        if (!$this->settings->hasSettings()) {
            return;
        }

        if ((int) $this->settings->get('log_level') === 1) {
            $finalMessage = 'Sezzle: ' . $message;
            $this->logger->notice($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     */
    public function error($message, array $context = [])
    {
        $finalMessage = 'Sezzle: ' . $message;
        $this->logger->error($finalMessage, $context);
    }
}
