<?php

use SwagPaymentSezzle\Components\DependencyProvider;
use SwagPaymentSezzle\Components\ErrorCodes;
use SwagPaymentSezzle\Components\PaymentBuilderParameters;
use SwagPaymentSezzle\Components\PaymentMethodProvider;
use SwagPaymentSezzle\Components\SessionBuilderParameters;
use SwagPaymentSezzle\SezzleBundle\PaymentType;
use SwagPaymentSezzle\SezzleBundle\Structs\Payment;
use SwagPaymentSezzle\SezzleBundle\Structs\Session;

class Shopware_Controllers_Frontend_Sezzle extends Shopware_Controllers_Frontend_Payment
{

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;
    /**
     * @var mixed|void
     */
    private $shopwareConfig;

    public function preDispatch()
    {
        $this->dependencyProvider = $this->get('sezzle.dependency_provider');
        $this->shopwareConfig = $this->get('config');
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
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $session->get('sUserGroupData', ['tax' => 1])['tax'];

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

            $response = $this->paymentResource->create($session);

            $responseStruct = Session::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        //Patch the address data into the payment.
        //This function is only being called for PayPal classic, therefore,
        //there is an additional action (patchAddressAction()) for the PayPal plus integration.
        /** @var PaymentAddressService $addressService */
        $addressService = $this->get('paypal_unified.payment_address_service');
        $addressPatch = new PaymentAddressPatch($addressService->getShippingAddress($userData));
        $payerInfoPatch = new PayerInfoPatch($addressService->getPayerInfo($userData));

        $useInContext = (bool) $this->Request()->getParam('useInContext', false);
        if ($useInContext) {
            $this->Front()->Plugins()->Json()->setRenderer();
            $this->View()->setTemplate();
        }

        try {
            $this->paymentResource->patch($responseStruct->getId(), [
                $addressPatch,
                $payerInfoPatch,
            ]);
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::ADDRESS_VALIDATION_ERROR, $exception);

            return;
        }

        if ($useInContext) {
            $this->View()->assign('paymentId', $responseStruct->getId());

            return;
        }

        $this->redirect($responseStruct->getLinks()[1]->getHref());
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
