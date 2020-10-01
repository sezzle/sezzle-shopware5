<?php

use SwagPaymentSezzle\Components\ExceptionHandlerServiceInterface;
use SwagPaymentSezzle\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;
use SwagPaymentSezzle\SezzleBundle\Services\ClientService;

class Shopware_Controllers_Backend_SezzleSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = GeneralSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'settings';

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->settingsService = $this->get('sezzle.settings_service');
        $this->clientService = $this->get('sezzle.client_service');
        $this->exceptionHandler = $this->get('sezzle.exception_handler_service');

        parent::preDispatch();
    }

    /**
     * Initialize the REST api client to check if the credentials are correct
     */
    public function validateAPIAction()
    {
        try {
            $this->configureClient();
            $this->View()->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'validate API credentials');

            $this->View()->assign([
                'success' => false,
                'message' => $error->getCompleteMessage()
            ]);
        }
    }

    /**
     *
     */
    private function configureClient()
    {
        $request = $this->Request();
        $shopId = (int) $request->getParam('shopId');
        $publicKey = $request->getParam('publicKey');
        $sandbox = $request->getParam('sandbox', 'false') !== 'false';
        $privateKey = $request->getParam('privateKey');

        $this->clientService->configure([
            'public_key' => $publicKey,
            'private_key' => $privateKey,
            'sandbox' => $sandbox,
            'shopId' => $shopId,
        ]);
    }
}
