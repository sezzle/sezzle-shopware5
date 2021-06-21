<?php

namespace SezzlePayment\Setup;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use SezzlePayment\Components\PaymentMethodProvider;

/**
 * Class Installer
 */
class Installer
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
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * Installer constructor.
     * @param ModelManager $modelManager
     * @param CrudService $attributeCrudService
     */
    public function __construct(
        ModelManager $modelManager,
        CrudService $attributeCrudService
    )
    {
        $this->modelManager          = $modelManager;
        $this->attributeCrudService  = $attributeCrudService;
        $this->paymentMethodProvider = new PaymentMethodProvider($modelManager);
    }

    /**
     * @return bool
     */
    public function install()
    {
        $this->paymentMethodProvider->createPaymentMethod();
        $this->createAttributes();
        return true;
    }

    /**
     *
     */
    private function createAttributes()
    {
        $this->createUserAttributes();
        $this->createOrderAttributes();
        $this->createBasketAttributes();
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

    /**
     *
     */
    public function createBasketAttributes()
    {
        $this->attributeCrudService->update('s_order_basket_attributes',
            'sezzle_order_uuid',
            'string',
            [
                'position' => -100,
                'displayInBackend' => false,
                'label' => 'Sezzle Order UUID',
                'helpText' => 'Sezzle Order UUID',
            ]);
    }

    /**
     *
     */
    private function createOrderAttributes()
    {
        $this->attributeCrudService->update('s_order_attributes', 'sezzle_payment_action', 'string');
        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_reference_id',
            'string',
            [
                'position' => 150,
                'displayInBackend' => true,
                'label' => 'Sezzle Order Reference ID',
                'helpText' => 'Sezzle Order Reference ID',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_order_uuid',
            'string',
            [
                'position' => 151,
                'displayInBackend' => true,
                'label' => 'Sezzle Order UUID',
                'helpText' => 'Sezzle Order UUID',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_auth_expiry',
            'datetime',
            [
                'position' => 152,
                'displayInBackend' => true,
                'label' => 'Sezzle Payment Auth Expiry',
                'helpText' => 'Sezzle Payment Auth Expiry',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_customer_uuid',
            'string',
            [
                'position' => 153,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID',
                'helpText' => 'Sezzle Customer UUID',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_customer_uuid_expiry',
            'datetime',
            [
                'position' => 154,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID Expiry',
                'helpText' => 'Sezzle Customer UUID Expiry',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_auth_amount',
            'float',
            [
                'position' => 155,
                'displayInBackend' => true,
                'label' => 'Sezzle Authorized Amount',
                'helpText' => 'Sezzle Authorized Amount',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_captured_amount',
            'float',
            [
                'position' => 156,
                'displayInBackend' => true,
                'label' => 'Sezzle Captured Amount',
                'helpText' => 'Sezzle Captured Amount',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_refunded_amount',
            'float',
            [
                'position' => 157,
                'displayInBackend' => true,
                'label' => 'Sezzle Refunded Amount',
                'helpText' => 'Sezzle Refunded Amount',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'sezzle_released_amount',
            'float',
            [
                'position' => 158,
                'displayInBackend' => true,
                'label' => 'Sezzle Released Amount',
                'helpText' => 'Sezzle Released Amount',
            ]);
    }

    /**
     *
     */
    private function createUserAttributes()
    {
        $this->attributeCrudService->update('s_user_attributes',
            'sezzle_customer_uuid',
            'string',
            [
                'position' => 55,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID',
                'helpText' => 'Sezzle Customer UUID',
            ]);
        $this->attributeCrudService->update('s_user_attributes',
            'sezzle_customer_uuid_expiry',
            'datetime',
            [
                'position' => 56,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID Expiry',
                'helpText' => 'Sezzle Customer UUID Expiry',
            ]);
        $this->attributeCrudService->update('s_user_attributes',
            'sezzle_customer_uuid_status',
            'boolean',
            [
                'position' => 57,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID Status',
                'helpText' => 'Sezzle Customer UUID Status',
            ]);
    }

}
