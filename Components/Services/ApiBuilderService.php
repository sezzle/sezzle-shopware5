<?php

namespace SezzlePayment\Components\Services;

use Shopware\Components\Cart\PaymentTokenService;
use Shopware\Components\Routing\RouterInterface;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SezzlePayment\Components\DependencyProvider;
use SezzlePayment\Components\ApiBuilderInterface;
use SezzlePayment\Components\ApiBuilderParameters;
use SezzlePayment\SezzleBundle\Components\SettingsServiceInterface;
use SezzlePayment\SezzleBundle\Structs\CustomerOrder;
use SezzlePayment\SezzleBundle\Structs\Order\Capture;
use SezzlePayment\SezzleBundle\Structs\Session;
use SezzlePayment\SezzleBundle\Util;

/**
 * Class ApiBuilderService
 * @package SezzlePayment\Components\Services
 */
class ApiBuilderService implements ApiBuilderInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var SettingsServiceInterface
     */
    protected $settings;

    /**
     * @var ApiBuilderParameters
     */
    protected $requestParams;

    /**
     * @var DependencyProvider
     */
    protected $dependencyProvider;

    /**
     * @var array
     */
    private $basketData;

    /**
     * @var array
     */
    private $order;

    /*
     * @var array
     */
    private $userData;

    /*
     * @var array
     */
    private $userProfile;

    /*
     * @var array
     */
    private $userBillingAddress;

    /*
     * @var array
     */
    private $userShippingAddress;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * ApiBuilderService constructor.
     * @param RouterInterface $router
     * @param SettingsServiceInterface $settingsService
     * @param SnippetManager $snippetManager
     * @param DependencyProvider $dependencyProvider
     */
    public function __construct(
        RouterInterface $router,
        SettingsServiceInterface $settingsService,
        SnippetManager $snippetManager,
        DependencyProvider $dependencyProvider
    ) {
        $this->router = $router;
        $this->settings = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession(ApiBuilderParameters $params)
    {
        $this->requestParams = $params;
        $this->basketData = $params->getBasketData();

        $this->userData = $params->getUserData();



        $this->userProfile = !empty($this->userData) && isset($this->userData['additional']['user']) ?
            $this->userData['additional']['user'] : null;



        $this->userBillingAddress = !empty($this->userData) && isset($this->userData['billingaddress']) ?
            $this->userData['billingaddress'] : null;
        $this->userShippingAddress = !empty($this->userData) && isset($this->userData['shippingaddress']) ?
            $this->userData['shippingaddress'] : null;




        $requestParameters = new Session();

        $cancelUrl = new Session\Url();
        $cancelUrl->setHref($this->getRedirectUrl('cancel'));

        $completeUrl = new Session\Url();
        $completeUrl->setHref($this->getRedirectUrl('complete'));

        $isTokenizationAllowed = (bool) $this->settings->get('tokenize');

        $customer = new Session\Customer();
        $customer->setEmail($this->userProfile['email']);
        $customer->setFirstName($this->userProfile['firstname']);
        $customer->setLastName($this->userProfile['lastname']);
        $customer->setPhone($this->userBillingAddress['phone']);
        $customer->setDob($this->userProfile['birthday']);
        $customer->setTokenize($isTokenizationAllowed);

        $billingAddress = new Session\Customer\Address();
        $billingAddress->setName(sprintf('%s %s', $this->userBillingAddress['firstname'], $this->userBillingAddress['lastname']));
        $billingAddress->setStreet($this->userBillingAddress['street']);
        $billingAddress->setState($this->userBillingAddress['state']);
        $billingAddress->setCity($this->userBillingAddress['city']);
        $billingAddress->setCountryCode($this->userData['additional']['country']['countryiso']);
        $billingAddress->setPhoneNumber($this->userBillingAddress['phone']);
        $billingAddress->setPostalCode($this->userBillingAddress['zipcode']);
        $customer->setBillingAddress($billingAddress);

        $shippingAddress = new Session\Customer\Address();
        $shippingAddress->setName(sprintf('%s %s', $this->userShippingAddress['firstname'], $this->userShippingAddress['lastname']));
        $shippingAddress->setStreet($this->userShippingAddress['street']);
        $shippingAddress->setState($this->userShippingAddress['state']);
        $shippingAddress->setCity($this->userShippingAddress['city']);
        $shippingAddress->setCountryCode($this->userData['additional']['countryShipping']['countryiso']);
        $shippingAddress->setPhoneNumber($this->userShippingAddress['phone']);
        $shippingAddress->setPostalCode($this->userShippingAddress['zipcode']);
        $customer->setShippingAddress($shippingAddress);

        $order = new Session\Order();
        $order->setIntent("AUTH");
        $order->setDescription("Shopware Order");
        $order->setReferenceId($params->getBasketUniqueId());
        $order->setRequiresShippingInfo(false);




        $orderAmount = new Session\Order\Amount();
        $orderAmount->setAmountInCents(Util::formatToCents($this->getTotalAmount()));
        $orderAmount->setCurrency($this->basketData['sCurrencyName']);
        $order->setOrderAmount($orderAmount);

        $taxAmount = new Session\Order\Amount();
        $taxAmount->setAmountInCents(Util::formatToCents($this->basketData['sAmountTax']));
        $taxAmount->setCurrency($this->basketData['sCurrencyName']);
        $order->setTaxAmount($taxAmount);

        $order->setItems($this->getItemList());

        $shippingCosts = new Session\Order\Amount();
        $shippingCosts->setAmountInCents(Util::formatToCents($this->basketData['sShippingcosts']));
        $shippingCosts->setCurrency($this->basketData['sCurrencyName']);
        $order->setShippingAmount($shippingCosts);

        $requestParameters->setCancelUrl($cancelUrl);
        $requestParameters->setCompleteUrl($completeUrl);
        $requestParameters->setCustomer($customer);
        $requestParameters->setOrder($order);

        return $requestParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapturePayload(ApiBuilderParameters $params)
    {
        $this->order = $params->getOrder();

        $requestParameters = new Capture();

        $amount = new Session\Order\Amount();
        $amount->setAmountInCents(Util::formatToCents($this->order['invoice_amount']));
        $amount->setCurrency($this->order['currency']);
        $requestParameters->setCaptureAmount($amount);
        $requestParameters->setPartialCapture(false);

        return $requestParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerOrderPayload(ApiBuilderParameters $params)
    {
        $this->basketData = $params->getBasketData();
        $this->userData = $params->getUserData();

        $requestParameters = new CustomerOrder();

        $requestParameters->setIntent("AUTH");
        $requestParameters->setReferenceId($params->getBasketUniqueId());

        $amount = new Session\Order\Amount();
        $amount->setAmountInCents(Util::formatToCents($this->getTotalAmount()));
        $amount->setCurrency($this->basketData['sCurrencyName']);
        $requestParameters->setOrderAmount($amount);

        return $requestParameters;
    }

    /**
     * @return float
     */
    private function getTotalAmount()
    {
        if (!$this->useNetPriceCalculation()) {
            return $this->showGrossPrices()
                ? $this->formatPrice($this->basketData['AmountNumeric']) //Case 1: Show gross prices in shopware and don't exclude country tax
                : $this->formatPrice($this->basketData['AmountWithTaxNumeric']); //Case 2: Show net prices in shopware and don't exclude country tax
        }

        //Case 3: No tax handling at all, just use the net amounts.
        return $this->formatPrice($this->basketData['AmountNetNumeric']);
    }

    /**
     * @return Session\Order\Item[]
     */
    private function getItemList()
    {
        $list = [];
        /** @var array $basketContent */
        $basketContent = $this->basketData['content'];

        foreach ($basketContent as $key => $basketItem) {
            $sku = $basketItem['ordernumber'];
            $name = $basketItem['articlename'];
            $quantity = (int) $basketItem['quantity'];

            $price = $this->showGrossPrices() === true
                ? $this->formatPrice($basketItem['price'])
                : $this->formatPrice($basketItem['netprice']);

            $item = new Session\Order\Item();
            $item->setName($name);
            $item->setQuantity($quantity);

            if ($sku !== null && $sku !== '') {
                $item->setSku($sku);
            }

            $itemAmount = new Session\Order\Amount();
            $itemAmount->setAmountInCents(Util::formatToCents($price));
            $itemAmount->setCurrency($this->basketData['sCurrencyName']);
            $item->setPrice($itemAmount);

            $list[$key] = $item->toArray();
        }

        return $list;
    }

    /**
     * @param string $action
     *
     * @return false|string
     */
    private function getRedirectUrl($action)
    {
        $routingParameters = [
            'controller' => 'Sezzle',
            'action' => $action,
            'forceSecure' => true,
        ];

        // Shopware 5.3+ supports cart validation.
        if ($this->requestParams->getBasketUniqueId()) {
            $routingParameters['basketId'] = $this->requestParams->getBasketUniqueId();
        }

        // Shopware 5.6+ supports session restoring
        $token = $this->requestParams->getPaymentToken();
        if ($token !== null) {
            $routingParameters[PaymentTokenService::TYPE_PAYMENT_TOKEN] = $token;
        }

        return $this->router->assemble($routingParameters);
    }

    /**
     * Returns a value indicating whether or not the current customer
     * uses the net price instead of the gross price.
     *
     * @return bool
     */
    private function showGrossPrices()
    {
        return (bool) $this->userData['additional']['show_net'];
    }

    /**
     * Returns a value indicating whether or not only the net prices without
     * any tax should be used in the total amount object.
     *
     * @return bool
     */
    private function useNetPriceCalculation()
    {
        if (!empty($this->userData['additional']['countryShipping']['taxfree'])) {
            return true;
        }

        if (empty($this->userData['additional']['countryShipping']['taxfree_ustid'])) {
            return false;
        }

        if (empty($this->userData['shippingaddress']['ustid'])
            && !empty($this->userData['billingaddress']['ustid'])
            && !empty($this->userData['additional']['country']['taxfree_ustid'])) {
            return true;
        }

        if (empty($this->userData['shippingaddress']['ustid'])) {
            return false;
        }

        if ($this->userData[self::CUSTOMER_GROUP_USE_GROSS_PRICES]) {
            return false;
        }

        return true;
    }

    /**
     * @param float|string $price
     *
     * @return float
     */
    private function formatPrice($price)
    {
        return round((float) str_replace(',', '.', $price), 2);
    }

}
