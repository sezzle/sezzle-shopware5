<?php

namespace SwagPaymentSezzle\Setup;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use PDO;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Plugin\Plugin;
use Shopware_Components_Translation;
use SwagPaymentSezzle\Components\PaymentMethodProvider;

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
     * @var Connection
     */
    private $connection;
    /**
     * @var CrudService
     */
    private $attributeCrudService;
    /**
     * @var Shopware_Components_Translation
     */
    private $translation;
    /**
     * @var
     */
    private $bootstrapPath;

    /**
     * Installer constructor.
     * @param ModelManager $modelManager
     * @param Connection $connection
     * @param CrudService $attributeCrudService
     * @param Shopware_Components_Translation $translation
     * @param $bootstrapPath
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        CrudService $attributeCrudService,
        Shopware_Components_Translation $translation,
        $bootstrapPath
    )
    {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->attributeCrudService = $attributeCrudService;
        $this->translation = $translation;
        $this->bootstrapPath = $bootstrapPath;
    }

    /**
     * @return bool
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws InstallationException
     */
    public function install()
    {
        if ($this->isSezzlePluginInstalled()) {
            throw new InstallationException("Sezzle Plugin is already installed.");
        }

        $this->createDatabaseTables();
        $this->createSezzlePaymentMethod();
        $this->createAttributes();
        $this->writeTranslation();

        return true;
    }

    /**
     * @return bool
     */
    private function isSezzlePluginInstalled()
    {
        $isInstalled = $this->modelManager->getRepository(Plugin::class)->findOneBy([
            'name' => PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME,
            'active' => 1
        ]);

        return $isInstalled !== null;
    }

    /**
     * @throws DBALException
     */
    private function createDatabaseTables()
    {
        $sql = file_get_contents($this->bootstrapPath . '/Setup/Assets/tables.sql');

        $this->connection->query($sql);
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
                's_order_basket_attributes'
            ]
        );
    }

    /**
     *
     */
    public function createBasketAttributes()
    {
        $this->attributeCrudService->update('s_order_basket_attributes',
            'swag_sezzle_order_uuid',
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
        $this->attributeCrudService->update('s_order_attributes', 'swag_sezzle_payment_action', 'string');
        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_reference_id',
            'string',
            [
                'position' => -100,
                'displayInBackend' => true,
                'label' => 'Sezzle Order Reference ID',
                'helpText' => 'Sezzle Order Reference ID',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_order_uuid',
            'string',
            [
                'position' => -200,
                'displayInBackend' => true,
                'label' => 'Sezzle Order UUID',
                'helpText' => 'Sezzle Order UUID',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_auth_expiry',
            'datetime',
            [
                'position' => -300,
                'displayInBackend' => true,
                'label' => 'Sezzle Payment Auth Expiry',
                'helpText' => 'Sezzle Payment Auth Expiry',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_customer_uuid',
            'string',
            [
                'position' => -400,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID',
                'helpText' => 'Sezzle Customer UUID',
            ]);
        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_customer_uuid_expiry',
            'datetime',
            [
                'position' => -500,
                'displayInBackend' => true,
                'label' => 'Sezzle Customer UUID Expiry',
                'helpText' => 'Sezzle Customer UUID Expiry',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_auth_amount',
            'float',
            [
                'position' => -400,
                'displayInBackend' => true,
                'label' => 'Sezzle Authorized Amount',
                'helpText' => 'Sezzle Authorized Amount',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_captured_amount',
            'float',
            [
                'position' => -500,
                'displayInBackend' => true,
                'label' => 'Sezzle Captured Amount',
                'helpText' => 'Sezzle Captured Amount',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_refunded_amount',
            'float',
            [
                'position' => -600,
                'displayInBackend' => true,
                'label' => 'Sezzle Refunded Amount',
                'helpText' => 'Sezzle Refunded Amount',
            ]);

        $this->attributeCrudService->update('s_order_attributes',
            'swag_sezzle_released_amount',
            'float',
            [
                'position' => -700,
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
            'swag_sezzle_customer_uuid',
            'string',
            [
                'position' => -100,
                'displayInBackend' => false,
                'label' => 'Sezzle Customer UUID',
                'helpText' => 'Sezzle Customer UUID',
            ]);
        $this->attributeCrudService->update('s_user_attributes',
            'swag_sezzle_customer_uuid_expiry',
            'datetime',
            [
                'position' => -100,
                'displayInBackend' => false,
                'label' => 'Sezzle Customer UUID Expiry',
                'helpText' => 'Sezzle Customer UUID Expiry',
            ]);
        $this->attributeCrudService->update('s_user_attributes',
            'swag_sezzle_customer_uuid_status',
            'boolean',
            [
                'position' => -100,
                'displayInBackend' => false,
                'label' => 'Sezzle Customer UUID Status',
                'helpText' => 'Sezzle Customer UUID Status',
            ]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createSezzlePaymentMethod()
    {
        $existingPayment = $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME,
        ]);

        if ($existingPayment !== null) {
            //If the payment does already exist, we don't need to add it again.
            return;
        }

        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(-100);
        $payment->setName(PaymentMethodProvider::SEZZLE_PAYMENT_METHOD_NAME);
        $payment->setDescription($this->getUnifiedPaymentLogo());
        $payment->setAdditionalDescription($this->getAdditionalDescription());
        $payment->setAction('Sezzle');

        $this->modelManager->persist($payment);
        $this->modelManager->flush($payment);
    }

    /**
     * @return string
     */
    private function getUnifiedPaymentLogo()
    {
        return '<!-- Sezzle Logo -->'
            . '<a href="https://www.sezzle.com" target="_blank" rel="noopener">'
            . '<img src="https://d34uoa9py2cgca.cloudfront.net/branding/sezzle-logos/png/sezzle-logo-sm-100w.png" alt="Logo Sezzle">'
            . '</a><br><!-- Sezzle Logo -->';
    }

    /**
     * @return string
     */
    private function getAdditionalDescription()
    {
        return 'Pay later with 0% interest';
    }

    /**
     *
     */
    private function writeTranslation()
    {
        /** @var array $translationKeys */
        $translationKeys = $this->getTranslationKeys();

        $this->translation->write(
            2,
            'config_payment',
            $translationKeys['SwagPaymentSezzle'],
            [
                'description' => 'Sezzle',
                'additionalDescription' => 'Sezzle',
            ],
            true
        );
    }

    /**
     * @return array
     */
    private function getTranslationKeys()
    {
        return $this->modelManager->getDBALQueryBuilder()
            ->select('name, id')
            ->from('s_core_paymentmeans', 'pm')
            ->where("pm.name = 'SwagPaymentSezzle'")
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

}
