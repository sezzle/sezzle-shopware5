<?php

use Sezzle\Components\Backend\GatewayRegionService;
use Sezzle\Components\ExceptionHandlerServiceInterface;
use Sezzle\Models\Settings\General as GeneralSettingsModel;
use Sezzle\SezzleBundle\Components\SettingsServiceInterface;
use Sezzle\SezzleBundle\Services\ClientService;
use Shopware\Components\HttpClient\RequestException;

class Shopware_Controllers_Backend_SezzleSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = GeneralSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    //protected $alias = 'settings';

    /**
     * {@inheritdoc}
     */
    protected $alias = 'general';

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->clientService = $this->get('sezzle.client_service');

        parent::preDispatch();
    }

    /**
     * Initialize the REST api client to check if the credentials are correct
     */
    public function validateAPIAction()
    {
        $regions = ['US', 'EU'];
        $settings = $this->getSettings();
        $regionDetected = false;
        foreach ($regions as $region) {
            $settings['gateway_region'] = $region;
            if ($this->clientConfigured($settings)) {
                $regionDetected = true;
                break;
            }
        }

        if (!$regionDetected) {
            $this->View()->assign([
                'success' => false,
            ]);
            return;
        }
        $this->View()->assign('success', true);
    }

    public function createAction()
    {
        $requestParams = $this->Request()->getParams();
        /** @var GatewayRegionService $gatewayRegionService */
        $gatewayRegionService = $this->get('sezzle.backend.gateway_region_service');
        $gatewayRegion = $gatewayRegionService->get($this->getSettings());
        if (!$gatewayRegion) {
            $this->View()->assign(
                ['success' => false, 'message' => "Invalid API Keys."]
            );
            return;
        }
        $requestParams['gatewayRegion'] = $gatewayRegion;
        $this->View()->assign(
            $this->save($requestParams)
        );
    }

    public function updateAction()
    {
        $requestParams = $this->Request()->getParams();
        /** @var GatewayRegionService $gatewayRegionService */
        $gatewayRegionService = $this->get('sezzle.backend.gateway_region_service');
        $gatewayRegion = $gatewayRegionService->get($this->getSettings());
        if (!$gatewayRegion) {
            $this->View()->assign(
                ['success' => false, 'message' => "Invalid API Keys."]
            );
            return;
        }
        $requestParams['gatewayRegion'] = $gatewayRegion;
        $this->View()->assign(
            $this->save($requestParams)
        );
    }

    private function getSettings()
    {
        $request = $this->Request();
        return [
            'public_key' => $request->getParam('publicKey'),
            'private_key' => $request->getParam('privateKey'),
            'sandbox' => $request->getParam('sandbox', 'false') !== 'false',
            'shopId' => (int)$request->getParam('shopId'),
        ];
    }

    /**
     * @throws Exception
     */
    public function detailAction()
    {
        $shopId = (int)$this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('sezzle.settings_service');

        /** @var GeneralSettingsModel $settings */
        $settings = $settingsService->getSettings($shopId);

        if ($settings !== null) {
            $this->view->assign('general', $settings->toArray());
        }
    }

    /**
     * Checking if client can configured with the provided settings
     *
     * @param array $settings
     * @return bool
     */
    private function clientConfigured($settings = [])
    {
        try {
            $this->clientService->configure($settings);
            return true;
        } catch (RequestException $e) {
            return false;
        }

    }
}
