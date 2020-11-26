<?php

namespace SwagPaymentSezzle\SezzleBundle\Services;

use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\HttpClient\GuzzleHttpClient as GuzzleClient;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\Components\DependencyProvider;
use SwagPaymentSezzle\SezzleBundle\BaseURL;
use SwagPaymentSezzle\SezzleBundle\Components\LoggerServiceInterface;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;
use SwagPaymentSezzle\SezzleBundle\RequestType;
use SwagPaymentSezzle\SezzleBundle\Structs\AuthCredentials;
use SwagPaymentSezzle\SezzleBundle\Structs\Token;

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
     * @var LoggerServiceInterface
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
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * ClientService constructor.
     * @param SettingsServiceInterface $settingsService
     * @param TokenService $tokenService
     * @param LoggerServiceInterface $logger
     * @param GuzzleFactory $factory
     * @param DependencyProvider $dependencyProvider
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        TokenService $tokenService,
        LoggerServiceInterface $logger,
        GuzzleFactory $factory,
        DependencyProvider $dependencyProvider
    ) {
        $this->settingsService = $settingsService;
        $this->tokenService = $tokenService;
        $this->logger = $logger;
        $this->client = new GuzzleClient($factory);

        $shop = $dependencyProvider->getShop();

        //Backend does not have any active shop. In order to authenticate there, please use
        //the "configure()"-function instead.
        if ($shop === null || !$this->settingsService->hasSettings() || !$this->settingsService->get('active')) {
            return;
        }

        $this->shopId = $shop->getId();

        $environment = (bool) $this->settingsService->get('sandbox');
        $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;
    }

    /**
     * @param array $settings
     * @throws RequestException
     */
    public function configure(array $settings)
    {
        $this->shopId = $settings['shopId'];
        $environment = $settings['sandbox'];
        $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

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
            $environment = (bool) $this->settingsService->get('sandbox');
            $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

            //Create authentication
            $credentials = new AuthCredentials();
            $credentials->setPublicKey($this->settingsService->get('public_key'));
            $credentials->setPrivateKey($this->settingsService->get('private_key'));
            $this->createAuthentication($credentials);
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
                throw new \RuntimeException('An unsupported request type was provided. The type was: ' . $type);
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
        } catch (\Exception $e) {
            $this->logger->error('Could not create authentication - unknown exception', [
                'message' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ]);
        }
    }
}
