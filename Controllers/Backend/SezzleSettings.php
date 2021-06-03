<?php

use SezzlePayment\Components\Backend\GatewayRegionService;
use SezzlePayment\Components\ExceptionHandlerServiceInterface;
use SezzlePayment\Models\Settings\General as GeneralSettingsModel;
use SezzlePayment\SezzleBundle\Components\SettingsServiceInterface;
use SezzlePayment\SezzleBundle\Services\ClientService;
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
     * Validate API Keys
     */
    public function validateAPIAction()
    {
        try {
            $gatewayRegion = $this->getGatewayRegion();
            $this->View()->assign([
                'success' => $gatewayRegion,
            ]);
        } catch (Exception $e) {
            $this->View()->assign(
                ['success' => false, 'message' => "Something went wrong while validating the keys."]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function createAction()
    {
        try {
            $requestParams = $this->Request()->getParams();
            $gatewayRegion = $this->getGatewayRegion();
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
        } catch (Exception $e) {
            $this->View()->assign(
                ['success' => false, 'message' => "Something went wrong while saving the settings."]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function updateAction()
    {
        try {
            $requestParams = $this->Request()->getParams();
            $gatewayRegion = $this->getGatewayRegion();
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
        } catch (Exception $e) {
            $this->View()->assign(
                ['success' => false, 'message' => "Something went wrong while saving the settings."]
            );
        }
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
     * Get gateway region
     *
     * @return string
     * @throws Exception
     */
    private function getGatewayRegion()
    {
        /** @var GatewayRegionService $gatewayRegionService */
        $gatewayRegionService = $this->get('sezzle.backend.gateway_region_service');
        return $gatewayRegionService->get($this->getSettings());
    }

    /**
     * Get settings
     *
     * @return array
     */
    private function getSettings()
    {
        $request = $this->Request();
        return [
            'public_key' => $request->getParam('publicKey'),
            'private_key' => $request->getParam('privateKey'),
            'sandbox' => $request->getParam('sandbox'),
            'shopId' => (int)$request->getParam('shopId'),
        ];
    }
}
