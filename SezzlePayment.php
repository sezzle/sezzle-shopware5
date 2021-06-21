<?php

namespace SezzlePayment;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use SezzlePayment\Components\PaymentMethodProvider;
use SezzlePayment\Setup\Installer;
use SezzlePayment\Setup\Uninstaller;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SezzlePayment extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('sezzle.plugin_dir', $this->getPath());
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        /**
         * @noinspection PhpParamsInspection
         */
        $installer = new Installer(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );
        $installer->install();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        /**
         * @noinspection PhpParamsInspection
         */
        (new Uninstaller($this->container->get('models')))->uninstall();
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        /**
         * @noinspection PhpParamsInspection
         */
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }

}
