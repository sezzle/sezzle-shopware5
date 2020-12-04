<?php

namespace SwagPaymentSezzle\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use SwagPaymentSezzle\Components\PaymentMethodProvider;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;

class PaymentMeans implements SubscriberInterface
{
    /**
     * @var int
     */
    private $sezzlePaymentId;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection,
        SettingsServiceInterface $settingsService,
        \Enlight_Components_Session_Namespace $session
    ) {
        $this->connection = $connection;
        $paymentMethodProvider = new PaymentMethodProvider();
        $this->sezzlePaymentId = $paymentMethodProvider->getPaymentId($connection);
        $this->settingsService = $settingsService;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter' => 'onFilterPaymentMeans',
        ];
    }

    public function onFilterPaymentMeans(\Enlight_Event_EventArgs $args)
    {
        /** @var array $availableMethods */
        $availableMethods = $args->getReturn();

        foreach ($availableMethods as $index => $paymentMethod) {
            if ((int) $paymentMethod['id'] === $this->sezzlePaymentId
                && (!$this->settingsService->hasSettings() || !$this->settingsService->get('active'))
            ) {
                //Force unset the payment method, because it's not available without any settings.
                unset($availableMethods[$index]);
            }
        }

        $args->setReturn($availableMethods);
    }
}
