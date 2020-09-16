<?php

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

//    /**
//     * @var ExceptionHandlerServiceInterface
//     */
//    private $exceptionHandler;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->settingsService = $this->get('sezzle.settings_service');
        $this->clientService = $this->get('sezzle.client_service');
        //$this->exceptionHandler = $this->get('paypal_unified.exception_handler_service');

        parent::preDispatch();
    }

    /**
     * This action handles the register webhook request.
     * It configures the RestClient to the provided credentials and announces
     * a wildcard webhook to the PayPal API.
     */
//    public function registerWebhookAction()
//    {
//        //Generate URL
//        /** @var Enlight_Controller_Router $router */
//        $router = $this->get('front')->Router();
//        $url = $router->assemble([
//            'module' => 'frontend',
//            'controller' => 'PaypalUnifiedWebhook',
//            'action' => 'execute',
//            'forceSecure' => 1,
//        ]);
//        $url = str_replace('http://', 'https://', $url);
//
//        try {
//            $this->configureClient();
//
//            $webhookResource = $this->get('paypal_unified.webhook_resource');
//            $webhookResource->create($url, ['*']);
//        } catch (Exception $e) {
//            $error = $this->exceptionHandler->handle($e, 'register webhooks');
//
//            if ($error->getName() === ExceptionHandlerService::WEBHOOK_ALREADY_EXISTS_ERROR) {
//                $this->View()->assign([
//                    'success' => true,
//                    'url' => $url,
//                ]);
//
//                return;
//            }
//
//            $this->View()->assign([
//                'success' => false,
//                'message' => $error->getCompleteMessage(),
//            ]);
//
//            return;
//        }
//
//        $this->View()->assign([
//            'success' => true,
//            'url' => $url,
//        ]);
//    }
//
    /**
     * Initialize the REST api client to check if the credentials are correct
     */
    public function validateAPIAction()
    {
        try {
            $this->configureClient();
            $this->View()->assign('success', true);
        } catch (Exception $e) {
            //$error = $this->exceptionHandler->handle($e, 'validate API credentials');

            $this->View()->assign([
                'success' => false
            ]);
        }
    }
//
//    /**
//     * Makes a test request against the installments endpoint to test if the installments integration is available
//     */
//    public function testInstallmentsAvailabilityAction()
//    {
//        $installmentsRequestService = $this->get('paypal_unified.installments.installments_request_service');
//
//        try {
//            $this->configureClient();
//            $response = $installmentsRequestService->getList(200.0);
//            $financingResponse = FinancingResponse::fromArray($response['financing_options'][0]);
//        } catch (Exception $e) {
//            $error = $this->exceptionHandler->handle($e, 'get installments financing options');
//
//            $this->View()->assign([
//                'success' => false,
//                'message' => $error->getCompleteMessage(),
//            ]);
//
//            return;
//        }
//
//        if ($financingResponse->getQualifyingFinancingOptions()) {
//            $this->View()->assign('success', true);
//
//            return;
//        }
//
//        $this->View()->assign('success', false);
//    }

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
