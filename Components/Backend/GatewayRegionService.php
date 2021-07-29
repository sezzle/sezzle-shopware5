<?php

namespace SezzlePayment\Components\Backend;

use SezzlePayment\SezzleBundle\GatewayRegion;
use SezzlePayment\Components\Services\SettingsService;

class GatewayRegionService
{
    /** @var SettingsService */
    private $settingsService;

    /**
     * @var GatewayRegion
     */
    private $gatewayRegion;

    /**
     * GatewayRegionService constructor.
     * @param SettingsService $settingsService
     * @param GatewayRegion $gatewayRegion
     */
    public function __construct(
        SettingsService $settingsService,
        GatewayRegion $gatewayRegion
    ) {
        $this->settingsService = $settingsService;
        $this->gatewayRegion = $gatewayRegion;
    }

    /**
     * Get Gateway Region
     *
     * @param array $settings
     * @return string
     */
    public function get($settings = [])
    {
        $storedGatewayRegion = $this->settingsService->getGatewayRegion();
        if ($storedGatewayRegion && !$this->hasKeysConfigurationChanged($settings)) {
            return $storedGatewayRegion;
        }

        return $this->gatewayRegion->getRegion($settings);
    }

    /**
     * Checking API Keys Configuration has changed or not
     *
     * @param array $settings
     * @return bool
     */
    private function hasKeysConfigurationChanged($settings = [])
    {
        // stored data
        $storedApiMode = (bool)$this->settingsService->isSandbox();
        $storedPublicKey = $this->settingsService->getPublicKey();
        $storedPrivateKey = $this->settingsService->getPrivateKey();

        // input data
        $apiMode = $settings['sandbox'];
        $publicKey = $settings['public_key'];
        $privateKey = $settings['private_key'];

        return !($storedPublicKey === $publicKey
            && $storedPrivateKey === $privateKey
            && $storedApiMode === $apiMode);
    }
}
