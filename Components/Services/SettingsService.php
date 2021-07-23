<?php

namespace SezzlePayment\Components\Services;

use Exception;
use Shopware\Models\Shop\DetachedShop;
use SezzlePayment\Components\DependencyProvider;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config;

class SettingsService
{
    /**
     * @var DetachedShop
     */
    private $shop;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;
    /**
     * @var Shopware_Components_Config
     */
    private $configComponent;
    /**
     * @var mixed|object|null
     */
    private $cShop;

    /**
     * SettingsService constructor.
     * @param Shopware_Components_Config $configComponent
     * @param DependencyProvider $dependencyProvider
     */
    public function __construct(
        Shopware_Components_Config $configComponent,
        DependencyProvider $dependencyProvider
    )
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->configComponent    = $configComponent;
        $this->shop               = $this->dependencyProvider->getShop();
    }

    /**
     * @param $key
     * @param string $namespace
     *
     * @return mixed|null
     */
    public function get($key, $namespace = 'SezzlePayment')
    {
        return $this->configComponent->getByNamespace($namespace, $key);
    }

    public function setShop($shop = null)
    {
        if ($shop === null) {
            $shop = $this->dependencyProvider->getMainShop();
        } elseif (!is_object($shop)) {
            /** @var Repository $shopRepository */
            $shopRepository = Shopware()->Container()->get('models')->getRepository(Shop::class);
            $shop           = $shopRepository->find($shop);
        }
        $this->cShop = $shop;
        $this->configComponent->setShop($shop);
    }

    public function getLanguage()
    {
        $supportedLanguages = [
            'en' => 'en-GB',
            'de' => 'de-DE',
            'fr' => 'fr-FR',
        ];
        try {
            $shopLocale = Shopware()->Shop()->getLocale()->getLocale();

            foreach ($supportedLanguages as $shortCode => $longCode) {
                if (stripos($shopLocale, $shortCode) === 0) {
                    return $longCode;
                }
            }
        } catch (Exception $e) {

        }
        return 'de-DE';
    }




    public function isActive(){
        return true; //TODO depend on payment status
    }

    public function getMerchantUuid()
    {
        return $this->get('merchant_uuid');
    }

    public function getPublicKey()
    {
        return $this->get('public_key');
    }

    public function getPrivateKey()
    {
        return $this->get('private_key');
    }

    public function isSandbox()
    {
        return (bool)$this->get('sandbox');
    }

    public function isTokenize()
    {
        return (bool)$this->get('tokenize');
    }

    public function getPaymentAction()
    {
        return $this->get('payment_action');
    }

    public function getLogLevel()
    {
        return $this->get('log_level');
    }

    public function isDisplayErrors()
    {
        return (bool)$this->get('display_errors');
    }

    public function getGatewayRegion()
    {
        return $this->get('gateway_region');
    }

    public function isEnableWidgetPdp(){
        return (bool)$this->get('enable_widget_pdp');
    }

    public function isEnableWidgetCart(){
        return (bool)$this->get('enable_widget_cart');
    }
}
