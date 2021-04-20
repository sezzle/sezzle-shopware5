<?php

namespace Sezzle;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Sezzle\Components\PaymentMethodProvider;
use Sezzle\Setup\Installer;
use Sezzle\Setup\Uninstaller;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Sezzle extends Plugin
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
        $translation = $this->container->has('translation')
            ? $this->container->get('translation')
            : new \Shopware_Components_Translation();

        $installer = new Installer(
            $this->container->get('models'),
            $this->container->get('dbal_connection'),
            $this->container->get('shopware_attribute.crud_service'),
            $translation,
            $this->getPath()
        );
        $installer->install();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        $uninstaller = new Uninstaller(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models'),
            $this->container->get('dbal_connection')
        );
        $uninstaller->uninstall($context->keepUserData());

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);

        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }

}
