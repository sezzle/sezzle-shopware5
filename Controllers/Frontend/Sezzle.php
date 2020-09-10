<?php

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\Components\DependencyProvider;
use SwagPaymentSezzle\Components\ErrorCodes;
use SwagPaymentSezzle\Components\PaymentMethodProvider;
use SwagPaymentSezzle\Components\PaymentStatus;
use SwagPaymentSezzle\Components\Services\OrderDataService;
use SwagPaymentSezzle\Components\Services\SettingsService;
use SwagPaymentSezzle\Components\SessionBuilderParameters;
use SwagPaymentSezzle\SezzleBundle\PaymentAction;
use SwagPaymentSezzle\SezzleBundle\PaymentType;
use SwagPaymentSezzle\SezzleBundle\Resources\CaptureResource;
use SwagPaymentSezzle\SezzleBundle\Resources\SessionResource;
use SwagPaymentSezzle\SezzleBundle\Structs\Session;

class Shopware_Controllers_Frontend_Sezzle extends Shopware_Controllers_Frontend_Payment
{

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;
    /**
     * @var Shopware_Components_Config
     */
    private $shopwareConfig;
    /**
     * @var SessionResource
     */
    private $sessionResource;
    /**
     * @var SettingsService
     */
    private $settingsService;
    /**
     * @var CaptureResource
     */
    private $captureResource;

    public function preDispatch()
    {
        $this->sessionResource = $this->get('sezzle.session_resource');
        $this->captureResource = $this->get('sezzle.capture_resource');
        $this->dependencyProvider = $this->get('sezzle.dependency_provider');
        $this->shopwareConfig = $this->get('config');
        $this->settingsService = $this->get('sezzle.settings_service');
    }

    /**
     * Index action of the payment. The only thing to do here is to forward to the gateway action.
     */
    public function indexAction()
    {
        $this->forward('gateway');
    }

    /**
     * The gateway to Sezzle. The payment will be created and the user will be redirected to the Sezzle site.
     * @throws Exception
     */
    public function gatewayAction()
    {
        $session = $this->dependencyProvider->getSession();
        $orderData = $session->get('sOrderVariables');


        if ($orderData === null) {
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }

        if ($this->noDispatchForOrder()) {
            $this->handleError(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            return;
        }

        $userData = $orderData['sUserData'];
        //$userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $session->get('sUserGroupData', ['tax' => 1])['tax'];

        try {
            //Query all information
            $basketData = $orderData['sBasket'];
            $selectedPaymentName = $orderData['sPayment']['name'];

            $requestParams = new SessionBuilderParameters();
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);
            $requestParams->setPaymentToken($this->dependencyProvider->createPaymentToken());

            // If supported, add the basket signature feature
            if ($this->container->has('basket_signature_generator')) {
                $basketUniqueId = $this->persistBasket();
                $requestParams->setBasketUniqueId($basketUniqueId);
            }

            /** @var Session $session */
            $session = null;

            // For generic PayPal payments like Sezzle,
            // a different parameter than in installments for the payment creation is needed
            if ($selectedPaymentName === PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME) {
                $requestParams->setPaymentType(PaymentType::SEZZLE);
                $session = $this->get('sezzle.session_builder_service')->getSession($requestParams);
            }

//            if ($selectedPaymentName === PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME) {
//                $requestParams->setPaymentType(PaymentType::PAYPAL_CLASSIC);
//                $payment = $this->get('paypal_unified.payment_builder_service')->getPayment($requestParams);
//            } elseif ($selectedPaymentName === PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME) {
//                $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_INSTALLMENTS);
//                $requestParams->setPaymentType(PaymentType::PAYPAL_INSTALLMENTS);
//                $payment = $this->get('paypal_unified.installments.payment_builder_service')->getPayment($requestParams);
//            }

            $response = $this->sessionResource->create($session);

            $responseStruct = Session::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        $this->redirect($responseStruct->getOrder()->getCheckoutUrl());
    }

    public function completeAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $request = $this->Request();
        $sessionUuid = $request->getParam('sessionUuid');
        $basketId = $request->getParam('basketId');

        //Basket validation with shopware 5.2 support
        if (!$this->container->has('basket_signature_generator')
        ) {
            //For shopware < 5.3 and for whitelisted basket ids
            try {
                $session = $this->sessionResource->get($sessionUuid);
            } catch (RequestException $exception) {
                $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $exception);

                return;
            }

            $basketValid = $this->validateBasketSimple(Session::fromArray($session));
        } else {
            //For shopware > 5.3
            $basketValid = $this->validateBasketExtended($basketId);
        }

        if (!$basketValid) {
            $this->handleError(ErrorCodes::BASKET_VALIDATION_ERROR);

            return;
        }

        $orderNumber = $this->saveOrder($sessionUuid, $sessionUuid, PaymentStatus::PAYMENT_STATUS_OPEN);

        // if the order number should be send to PayPal do it before the execute
//        if ($sendOrderNumber) {
//            $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
//            $patchOrderNumber = $this->settingsService->get('order_number_prefix') . $orderNumber;
//
//            /** @var PaymentOrderNumberPatch $paymentPatch */
//            $paymentPatch = new PaymentOrderNumberPatch($patchOrderNumber);
//
//            try {
//                $this->paymentResource->patch($paymentId, [$paymentPatch]);
//            } catch (RequestException $exception) {
//                $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $exception);
//
//                return;
//            }
//        }

        $payerId = $request->getParam('PayerID');
        /** @var OrderDataService $orderDataService */
        $orderDataService = $this->get('sezzle.order_data_service');
        try {
            $paymentAction = $this->settingsService->get('payment_action');

            if ($paymentAction === PaymentAction::AUTHORIZE_CAPTURE) {
                $captureResponse = $this->captureResource->create();
                if ($captureResponse === null) {
                    $this->handleError(ErrorCodes::COMMUNICATION_FAILURE);
                    return;
                }
                $this->savePaymentStatus($relatedResourceId, $paymentId, PaymentStatus::PAYMENT_STATUS_PAID);
                $orderDataService->setClearedDate($orderNumber);
            }
        } catch (RequestException $exception) {
            $orderDataService->setOrderState($orderNumber, PaymentStatus::PAYMENT_STATUS_OPEN);
            //$orderDataService->removeTransactionId($orderNumber);
            $errorCode = ErrorCodes::COMMUNICATION_FAILURE;
            $this->handleError($errorCode, $exception);

            return;
        }

        /** @var Payment $response */
        $response = Payment::fromArray($executionResponse);

        // if the order number is not sent to PayPal, save the order here
        if (!$sendOrderNumber) {
            $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
        }

        /** @var RelatedResource $relatedResource */
        $relatedResource = $response->getTransactions()->getRelatedResources()->getResources()[0];

        //Use TXN-ID instead of the PaymentId
        $relatedResourceId = $relatedResource->getId();
        if (!$orderDataService->applyTransactionId($orderNumber, $relatedResourceId)) {
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }

        // apply the payment status if its completed by PayPal
        $paymentState = $relatedResource->getState();
        if ($paymentState === PaymentStatus::PAYMENT_COMPLETED) {
            $this->savePaymentStatus($relatedResourceId, $paymentId, PaymentStatus::PAYMENT_STATUS_PAID);
            $orderDataService->setClearedDate($orderNumber);
        }

        // Save payment instructions from PayPal to database.
        // if the instruction is of type MANUAL_BANK_TRANSFER the instructions are not required,
        // since they don't have to be displayed on the invoice document
        $instructions = $response->getPaymentInstruction();
        if ($instructions && $instructions->getType() === PaymentInstructionType::INVOICE) {
            /** @var PaymentInstructionService $instructionService */
            $instructionService = $this->get('paypal_unified.payment_instruction_service');
            $instructionService->createInstructions($orderNumber, $instructions);
        }

        $orderDataService->applyPaymentTypeAttribute($orderNumber, $response, $isExpressCheckout, $isSpbCheckout);

        $redirectParameter = [
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $paymentId,
        ];

        if ($isExpressCheckout) {
            $redirectParameter['expressCheckout'] = true;
        }

        $this->dependencyProvider->getSession()->offsetUnset('sComment');

        // Done, redirect to the finish page
        $this->redirect($redirectParameter);

    }

    /**
     * This action will be executed if the user cancels the payment on the PayPal page.
     * It will redirect to the payment selection.
     * @throws Exception
     */
    public function cancelAction()
    {
        $this->handleError(ErrorCodes::CANCELED_BY_USER);
    }

    /**
     * This method handles the redirection to the shippingPayment action if an error has occurred during the payment process.
     * If the order number was sent before, the method will redirect to the finish page.
     *
     * @param int $code
     * @param Exception|null $exception
     * @param bool $redirectToFinishAction
     * @throws Exception
     * @see ErrorCodes
     *
     */
    private function handleError($code, Exception $exception = null, $redirectToFinishAction = false)
    {
        /** @var string $message */
        $message = null;
        $name = null;

//        if ($exception) {
//            /** @var ExceptionHandlerServiceInterface $exceptionHandler */
//            $exceptionHandler = $this->get('paypal_unified.exception_handler_service');
//            $error = $exceptionHandler->handle($exception, 'process checkout');
//
//            if ($this->settingsService->hasSettings() && $this->settingsService->get('display_errors')) {
//                $message = $error->getMessage();
//                $name = $error->getName();
//            }
//        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->Front()->Plugins()->Json()->setRenderer();
            $view = $this->View();
            $view->setTemplate();

            $view->assign('errorCode', $code);
            if ($name !== null) {
                $view->assign([
                    'sezzle_error_name' => $name,
                    'sezzle_error_message' => $message,
                ]);
            }

            return;
        }

        $redirectData = [
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'sezzle_error_code' => $code,
        ];

        if ($redirectToFinishAction) {
            $redirectData['action'] = 'finish';
        }

        if ($name !== null) {
            $redirectData['sezzle_error_name'] = $name;
            $redirectData['sezzle_error_message'] = $message;
        }

        $this->redirect($redirectData);
    }

    /**
     * @return bool
     */
    private function noDispatchForOrder()
    {
        $session = $this->dependencyProvider->getSession();

        return !empty($this->shopwareConfig->get('premiumShippingNoOrder'))
            && (empty($session->get('sDispatch')) || empty($session->get('sCountry')));
    }
}
