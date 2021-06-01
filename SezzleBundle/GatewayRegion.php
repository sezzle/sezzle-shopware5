<?php


namespace Sezzle\SezzleBundle;


use Sezzle\SezzleBundle\Components\GatewayRegionInterface;
use Sezzle\SezzleBundle\Services\ClientService;
use Shopware\Components\HttpClient\RequestException;

/**
 * Class GatewayRegion
 * @package Sezzle\SezzleBundle
 */
class GatewayRegion implements GatewayRegionInterface
{
    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * GatewayRegion constructor.
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @var string[]
     */
    private static $supportedGatewayRegions = ['US', 'EU'];

    /**
     * Get sezzle domain
     *
     * @param string $gatewayRegion
     * @return string
     */
    public static function getSezzleDomain($gatewayRegion = '')
    {
        $region = $gatewayRegion === self::defaultRegion() ? '' : "$gatewayRegion.";
        return sprintf(BaseURL::SEZZLE_DOMAIN, strtolower($region));
    }

    /**
     * Get gateway url
     *
     * @param string $apiMode
     * @param string $apiVersion
     * @param string $gatewayRegion
     * @return string
     */
    public static function getGatewayUrl($apiMode, $apiVersion, $gatewayRegion = '')
    {
        $sezzleDomain = self::getSezzleDomain($gatewayRegion);
        $env = $apiMode === TransactionMode::SANDBOX ? 'sandbox.' : '';
        return sprintf(BaseURL::GATEWAY_URL, $env, $sezzleDomain, $apiVersion);
    }

    /**
     * Default region
     *
     * @return string
     */
    public static function defaultRegion()
    {
        return self::$supportedGatewayRegions[0];
    }

    /**
     * @inheritDoc
     */
    public function getRegion($settings = [])
    {
        $gatewayRegion = '';
        foreach (self::$supportedGatewayRegions as $region) {
            $settings['gateway_region'] = $region;
            try {
                $this->clientService->configure($settings);
                return $region;
            } catch (RequestException $e) {
                continue;
            }
        }
        return $gatewayRegion;
    }
}
