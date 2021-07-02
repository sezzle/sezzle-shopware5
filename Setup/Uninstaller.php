<?php

namespace SezzlePayment\Setup;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use SezzlePayment\Components\PaymentMethodProvider;

class Uninstaller
{

    /**
     * @var ModelManager
     */
    private $modelManager;
    /**
     * @var CrudService
     */
    private $attributeCrudService;

    /**
     * Uninstaller constructor.
     * @param ModelManager $modelManager
     * @param CrudService $attributeCrudService
     */
    public function __construct(
        ModelManager $modelManager,
        CrudService $attributeCrudService
    )
    {
        $this->modelManager = $modelManager;
        $this->attributeCrudService = $attributeCrudService;
    }

    /**
     */
    public function uninstall($keepUserData)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->modelManager);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        if(!$keepUserData) {
            $this->removeAttributes();
        }
    }

    /**
     *
     */
    private function removeAttributes()
    {
        $this->attributeCrudService->delete('s_order_basket_attributes', 'sezzle_order_uuid');

        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_payment_action');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_reference_id');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_order_uuid');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_auth_expiry');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_customer_uuid');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_customer_uuid_expiry');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_auth_amount');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_captured_amount');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_refunded_amount');
        $this->attributeCrudService->delete('s_order_attributes', 'sezzle_released_amount');

        $this->attributeCrudService->delete('s_user_attributes', 'sezzle_customer_uuid');
        $this->attributeCrudService->delete('s_user_attributes', 'sezzle_customer_uuid_expiry');
        $this->attributeCrudService->delete('s_user_attributes', 'sezzle_customer_uuid_status');

        $this->modelManager->generateAttributeModels(
            [
                's_order_attributes',
                's_user_attributes',
                's_order_basket_attributes',
            ]
        );
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
    }

}
