<?php

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\Components\DependencyProvider;
use SwagPaymentSezzle\Components\ErrorCodes;
use SwagPaymentSezzle\Components\OrderStatus;
use SwagPaymentSezzle\Components\PaymentMethodProvider;
use SwagPaymentSezzle\Components\PaymentStatus;
use SwagPaymentSezzle\Components\Services\BasketDataService;
use SwagPaymentSezzle\Components\Services\OrderDataService;
use SwagPaymentSezzle\Components\Services\SettingsService;
use SwagPaymentSezzle\Components\Services\UserDataService;
use SwagPaymentSezzle\Components\Services\Validation\BasketValidatorInterface;
use SwagPaymentSezzle\Components\ApiBuilderParameters;
use SwagPaymentSezzle\SezzleBundle\PaymentAction;
use SwagPaymentSezzle\SezzleBundle\PaymentType;
use SwagPaymentSezzle\SezzleBundle\Resources\CaptureResource;
use SwagPaymentSezzle\SezzleBundle\Resources\CustomerResource;
use SwagPaymentSezzle\SezzleBundle\Resources\OrderResource;
use SwagPaymentSezzle\SezzleBundle\Resources\SessionResource;
use SwagPaymentSezzle\SezzleBundle\Structs\CustomerOrder;
use SwagPaymentSezzle\SezzleBundle\Structs\Order;
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
    /**
     * @var OrderResource
     */
    private $orderResource;
    /**
     * @var CustomerResource
     */
    private $customerResource;

    public function preDispatch()
    {
        $this->sessionResource = $this->get('sezzle.session_resource');
        $this->captureResource = $this->get('sezzle.capture_resource');
        $this->orderResource = $this->get('sezzle.order_resource');
        $this->customerResource = $this->get('sezzle.customer_resource');
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
//        $shopwareSession = $this->dependencyProvider->getSession();
//        $orderData = $shopwareSession->get('sOrderVariables');
//        $userData = $orderData['sUserData'];
//        /** @var UserDataService $userDataService */
//        $userDataService = $this->get('sezzle.user_data_service');
//        if ($userDataService->getValueByKey($userData, 'sezzle_token')) {
//            $shopwareSession->offsetSet('sezzle_token', $userDataService->getValueByKey($userData, 'sezzle_token'));
//            $shopwareSession->offsetSet('sezzle_token_expiration', $userDataService->getValueByKey($userData, 'sezzle_token_expiration'));
//        }

    }

    /**
     * The gateway to Sezzle. The payment will be created and the user will be redirected to the Sezzle site.
     * @throws Exception
     */
    public function gatewayAction()
    {
        $shopwareSession = $this->dependencyProvider->getSession();
        $orderData = $shopwareSession->get('sOrderVariables');


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

            $requestParams = new ApiBuilderParameters();
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);
            $requestParams->setPaymentToken($this->dependencyProvider->createPaymentToken());
            $basketUniqueId = null;

            // If supported, add the basket signature feature
            if ($this->container->has('basket_signature_generator')) {
                $basketUniqueId = $this->persistBasket();
                $requestParams->setBasketUniqueId($basketUniqueId);
            }

            /** @var Session $session */
            $session = null;
            /** @var CustomerOrder $customerOrder */
            $customerOrder = null;
            $customerUuid = null;

            // For generic PayPal payments like Sezzle,
            // a different parameter than in installments for the payment creation is needed
            if ($selectedPaymentName === PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME) {
                $requestParams->setPaymentType(PaymentType::SEZZLE);
                /** @var UserDataService $userDataService */
                $userDataService = $this->get('sezzle.user_data_service');
                if ($customerUuid = $userDataService->getValueByKey($userData, 'customer_uuid')) {
                    $customerOrder = $this->get('sezzle.api_builder_service')->getCustomerOrderPayload($requestParams);
                } else {
                    $session = $this->get('sezzle.api_builder_service')->getSession($requestParams);
                }
            }

            if ($customerOrder) {
                $response = $this->customerResource->create($customerUuid, $customerOrder);
                $responseStruct = CustomerOrder::fromArray($response);
                $orderUuid = $responseStruct->getUuid();
            } else {

                $response = $this->sessionResource->create($session);
                $responseStruct = Session::fromArray($response);
                $orderUuid = $responseStruct->getOrder()->getUuid();
            }


            /** @var BasketDataService $basketDataService */
            $basketDataService = $this->get('sezzle.basket_data_service');
            foreach ($this->getBasket()['content'] as $lineItem) {
                $basketDataService->applyOrderUuidAttribute($lineItem['id'], $orderUuid);
            }

            if ($customerOrder) {
                $this->forward(
                    'complete',
                    null,
                    null,
                    ['basketId' => $basketUniqueId]
                );
                return;
            }

            if ($tokenizeData = $responseStruct->getTokenize()) {
                $shopwareSession->offsetSet('sezzle_token', $tokenizeData->getToken());
                $shopwareSession->offsetSet('sezzle_token_expiration', $tokenizeData->getExpiration());
            }

        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        $this->redirect($responseStruct->getOrder()->getCheckoutUrl());
    }

    public function tokenizeAction()
    {

    }

    public function completeAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $request = $this->Request();
        $basketUniqueId = $request->getParam('basketId');

        if ($customerUuid = $request->getParam('customer-uuid')) {
            /** @var UserDataService $userDataService */
            $userDataService = $this->get('sezzle.user_data_service');
            $userDataService->applyTokenizeAttributes();
        }

        /** @var BasketDataService $basketDataService */
        $basketDataService = $this->get('sezzle.basket_data_service');
        $orderUuid = $basketDataService->getValueByKey($this->getBasket(), 'order_uuid');

        if (!$orderUuid) {
            $this->handleError(ErrorCodes::BASKET_VALIDATION_ERROR);

            return;
        }

        try {
            $sezzleOrder = $this->orderResource->get($orderUuid);
        } catch (RequestException $e) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $e);

            return;
        }

        if (!isset($sezzleOrder['authorization'])) {
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }


        //Basket validation with shopware 5.2 support
        if (!$this->container->has('basket_signature_generator')
        ) {
            //For shopware < 5.3 and for whitelisted basket ids
            $basketValid = $this->validateBasketSimple(Order::fromArray($sezzleOrder));

        } else {
            //For shopware > 5.3
            $basketValid = $this->validateBasketExtended($basketUniqueId);
        }

        if (!$basketValid) {
            $this->handleError(ErrorCodes::BASKET_VALIDATION_ERROR);

            return;
        }

        $orderNumber = $this->saveOrder($orderUuid, $orderUuid, PaymentStatus::PAYMENT_STATUS_OPEN);

        if (!$orderNumber) {
            $this->handleError(ErrorCodes::UNKNOWN);

            return;
        }

        /** @var Order $sezzleOrderObj */
        $sezzleOrderObj = Order::fromArray($sezzleOrder);
        $orderUuid = $sezzleOrderObj->getUuid();

        /** @var OrderDataService $orderDataService */
        $orderDataService = $this->get('sezzle.order_data_service');
        try {
            $paymentAction = $this->settingsService->get('payment_action');

            echo $paymentAction;

            if ($paymentAction === PaymentAction::AUTHORIZE_CAPTURE) {
                $requestParams = new ApiBuilderParameters();
                $requestParams->setOrder($orderDataService->getOrder($orderNumber));
                $capturePayload = $this->get('sezzle.api_builder_service')->getCapturePayload($requestParams);
                $captureResponse = $this->captureResource->create($orderUuid, $capturePayload);
                $captureObject = Order\Capture::fromArray($captureResponse);
                if ($captureResponse === null) {
                    $this->handleError(ErrorCodes::COMMUNICATION_FAILURE);
                    return;
                }
                $this->savePaymentStatus($orderUuid, $orderUuid, PaymentStatus::PAYMENT_STATUS_PAID);
                $this->orderResource->update($orderUuid, ['reference_id' => $orderNumber]);
                $orderDataService->setOrderStatus($orderNumber, OrderStatus::IN_PROGRESS);
                $orderDataService->setClearedDate($orderNumber);
            }
        } catch (RequestException $exception) {
            $orderDataService->setOrderStatus($orderNumber, OrderStatus::OPEN);
            //$orderDataService->removeTransactionId($orderNumber);
            $errorCode = ErrorCodes::COMMUNICATION_FAILURE;
            $this->handleError($errorCode, $exception);

            return;
        }

        $orderDataService->applyPaymentAttributes($orderNumber, $sezzleOrderObj, true);

//        if ($customerUuid) {
//            $orderDataService->applyTokenizeAttributes($orderNumber);
//        }

        $redirectParameter = [
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $orderUuid,
        ];

        $this->dependencyProvider->getSession()->offsetUnset('sComment');

        $orderDataService->clearSezzleSessionData();

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
     * @param string|null $basketId
     *
     * @return bool
     */
    private function validateBasketExtended($basketId = null)
    {
        //Shopware 5.3 installed but no basket id that can be validated.
        if ($basketId === null) {
            return false;
        }

        //New validation for Shopware 5.3.X
        try {
            $basket = $this->loadBasketFromSignature($basketId);
            $this->verifyBasketSignature($basketId, $basket);

            return true;
        } catch (RuntimeException $ex) {
            return false;
        }
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function validateBasketSimple(Order $order)
    {
        /** @var BasketValidatorInterface $legacyValidator */
        $legacyValidator = $this->get('sezzle.simple_basket_validator');

        $basket = $this->getBasket();
        $customer = $this->getUser();
        if ($basket === null || $customer === null) {
            return false;
        }

        return $legacyValidator->validate($this->getBasket(), $this->getUser(), $order);
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
