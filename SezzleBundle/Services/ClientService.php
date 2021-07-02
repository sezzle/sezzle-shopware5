<?php

namespace SezzlePayment\SezzleBundle\Services;

use Exception;
use RuntimeException;
use SezzlePayment\Components\Services\LoggerService;
use SezzlePayment\Components\Services\SettingsService;
use SezzlePayment\SezzleBundle\GatewayRegion;
use SezzlePayment\SezzleBundle\TransactionMode;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\HttpClient\GuzzleHttpClient as GuzzleClient;
use Shopware\Components\HttpClient\RequestException;
use SezzlePayment\Components\DependencyProvider;
use SezzlePayment\SezzleBundle\RequestType;
use SezzlePayment\SezzleBundle\Structs\AuthCredentials;
use SezzlePayment\SezzleBundle\Structs\Token;

class ClientService
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var TokenService
     */
    private $tokenService;

    /**
     * @var LoggerService
     */
    private $logger;

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /*
    private static $supportedGatewayRegions = [
        'US' => 'https://d34uoa9py2cgca.cloudfront.net/branding/sezzle-logos/sezzle-pay-over-time-no-interest@2x.png',
        'EU' => 'https://media.eu.sezzle.com/payment-method/assets/sezzle.png'
    ];
    */

    /**
     * ClientService constructor.
     * @param SettingsService $settingsService
     * @param TokenService $tokenService
     * @param LoggerService $logger
     * @param GuzzleFactory $factory
     * @param DependencyProvider $dependencyProvider
     */
    public function __construct(
        SettingsService $settingsService,
        TokenService $tokenService,
        LoggerService $logger,
        GuzzleFactory $factory,
        DependencyProvider $dependencyProvider
    ) {
        $this->settingsService = $settingsService;
        $this->tokenService = $tokenService;
        $this->logger = $logger;
        $this->client = new GuzzleClient($factory);

        $shop = $dependencyProvider->getShop();
        $this->shopId = $shop->getId();

        $gatewayRegion = $this->settingsService->get('gateway_region');
        $apiMode = (bool)$this->settingsService->get('sandbox')
            ? TransactionMode::SANDBOX
            : TransactionMode::LIVE;
        $this->baseUrl = GatewayRegion::getGatewayUrl($apiMode, 'v2', $gatewayRegion);
    }

    /**
     * Configure the client with auth
     *
     * @param array $settings
     * @throws RequestException
     */
    public function configure(array $settings)
    {
        if (!$this->shopId && isset($settings['shopId'])) {
            $this->shopId = $settings['shopId'];
        }
        $environment = (bool)$settings['sandbox'];
        $apiMode = $environment ? TransactionMode::SANDBOX : TransactionMode::LIVE;

        $this->baseUrl = GatewayRegion::getGatewayUrl($apiMode, 'v2', $settings['gateway_region']);

        //Create authentication
        $credentials = new AuthCredentials();
        $credentials->setPublicKey($settings['public_key']);
        $credentials->setPrivateKey($settings['private_key']);
        $this->createAuthentication($credentials);
    }

    /**
     * Sends a request and returns the response.
     * The type can be obtained from RequestType.php
     *
     * @param string $type
     * @param string $resourceUri
     * @param array $data
     * @param bool $tokenRequired
     * @return array
     * @throws RequestException
     */
    public function sendRequest($type, $resourceUri, array $data = [], $tokenRequired = true)
    {
        if (!$this->getHeader('Authorization') && $tokenRequired) {
            $this->configure([
                'sandbox' => $this->settingsService->get('sandbox'),
                'public_key' => $this->settingsService->get('public_key'),
                'private_key' => $this->settingsService->get('private_key'),
                'gateway_region' => $this->settingsService->get('gateway_region')
            ]);
        }

        $resourceUri = $this->baseUrl . $resourceUri;

        if (!empty($data)) {
            $data = json_encode($data);
            $this->setHeader('Content-Type', 'application/json');
        }

        $this->logger->notify('Sending request [' . $type . '] to ' . $resourceUri, ['payload' => $data]);

        switch ($type) {
            case RequestType::POST:
                $response = $this->client->post($resourceUri, $this->headers, $data)->getBody();
                break;

            case RequestType::GET:
                $response = $this->client->get($resourceUri, $this->headers)->getBody();
                break;

            case RequestType::PATCH:
                $response = $this->client->patch($resourceUri, $this->headers, $data)->getBody();
                break;

            case RequestType::PUT:
                $response = $this->client->put($resourceUri, $this->headers, $data)->getBody();
                break;

            default:
                throw new RuntimeException('An unsupported request type was provided. The type was: ' . $type);
        }

        $this->logger->notify('Received data from ' . $resourceUri, ['payload' => $response]);

        return json_decode($response, true);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * @param string $partnerId
     */
    public function setPartnerAttributionId($partnerId)
    {
        $this->setHeader('PayPal-Partner-Attribution-Id', $partnerId);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getHeader($key)
    {
        return $this->headers[$key];
    }

    /**
     * Creates the authentication header for the PayPal API.
     * If there is no cached token yet, it will be generated on the fly.
     *
     * @param AuthCredentials $credentials
     * @throws RequestException
     * @throws Exception
     */
    private function createAuthentication(AuthCredentials $credentials)
    {
        try {
            /** @var Token $cachedToken */
            $token = $this->tokenService->getToken($this, $credentials, $this->shopId);
            $this->setHeader('Authorization', 'Bearer ' . $token->getToken());
        } catch (RequestException $requestException) {
            $this->logger->error('Could not create authentication - request exception', [
                'payload' => $requestException->getBody(),
                'message' => $requestException->getMessage(),
            ]);

            throw $requestException;
        } catch (Exception $e) {
            $this->logger->error('Could not create authentication - unknown exception', [
                'message' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
