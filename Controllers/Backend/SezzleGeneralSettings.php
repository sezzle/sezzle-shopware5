<?php

use Sezzle\Components\Backend\GatewayRegionService;
use Sezzle\Models\Settings\General as GeneralSettingsModel;
use Sezzle\SezzleBundle\Components\SettingsServiceInterface;

class Shopware_Controllers_Backend_SezzleGeneralSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = GeneralSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'general';

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
}
