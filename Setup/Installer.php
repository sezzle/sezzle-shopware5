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
        $this->attributeCrudService->update('s_order_attributes', 'swag_sezzle_payment_action', 'string');
        $this->modelManager->generateAttributeModels(['s_order_attributes', 's_core_paymentmeans_attributes']);
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
        $payment->setDescription('Sezzle');
        $payment->setAdditionalDescription('Sezzle');
        $payment->setAction('Sezzle');

        $this->modelManager->persist($payment);
        $this->modelManager->flush($payment);
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
