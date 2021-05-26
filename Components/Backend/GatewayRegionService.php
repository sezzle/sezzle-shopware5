<?php

namespace Sezzle\Components\Backend;

use Sezzle\SezzleBundle\Components\GatewayRegionInterface;
use Sezzle\SezzleBundle\Components\SettingsServiceInterface;

class GatewayRegionService
{
    /** @var SettingsServiceInterface */
    private $settingsService;

    /**
     * @var GatewayRegionInterface
     */
    private $gatewayRegion;

    /**
     * GatewayRegionService constructor.
     * @param SettingsServiceInterface $settingsService
     * @param GatewayRegionInterface $gatewayRegion
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        GatewayRegionInterface $gatewayRegion
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
        $storedGatewayRegion = $this->settingsService->hasSettings()
            ? $this->settingsService->get("gateway_region")
            : '';
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
        $storedApiMode = $this->settingsService->get('sandbox') ? 'sandbox' : 'production';
        $storedPublicKey = $this->settingsService->get('public_key');
        $storedPrivateKey = $this->settingsService->get('private_key');

        // input data
        $apiMode = $settings['sandbox'] ? 'sandbox' : 'production';
        $publicKey = $settings['public_key'];
        $privateKey = $settings['private_key'];

        // return stored region if the key elements match
        if ($storedPublicKey === $publicKey
            && $storedPrivateKey === $privateKey
            && $storedApiMode === $apiMode) {
            return false;
        }
        return true;
    }

}
