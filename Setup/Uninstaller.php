<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SezzlePayment\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use SezzlePayment\Components\PaymentMethodProvider;

class Uninstaller
{
    /**
     * @var CrudService
     */
    private $attributeCrudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Uninstaller constructor.
     * @param CrudService $attributeCrudService
     * @param ModelManager $modelManager
     * @param Connection $connection
     */
    public function __construct(CrudService $attributeCrudService, ModelManager $modelManager, Connection $connection)
    {
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
    }

    /**
     * @param bool $safeMode
     */
    public function uninstall($safeMode)
    {
        $this->deactivatePayments();
        $this->removeAttributes();

        if (!$safeMode) {
            $this->removeSettingsTables();
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function deactivatePayments()
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->modelManager);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $paymentMethodProvider->setPaymentMethodActiveFlag(
            false
        );
    }

    /**
     *
     */
    private function removeAttributes()
    {
        if ($this->attributeCrudService->get('s_core_paymentmeans_attributes', 'sezzle_display_in_plus_iframe') !== null) {
            $this->attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'sezzle_display_in_plus_iframe'
            );
        }
        if ($this->attributeCrudService->get('s_core_paymentmeans_attributes', 'sezzle_iframe_payment_logo') !== null) {
            $this->attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'sezzle_iframe_payment_logo'
            );
        }
        $this->modelManager->generateAttributeModels(['s_core_paymentmeans_attributes']);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeSettingsTables()
    {
        $sql = 'DROP TABLE IF EXISTS `sezzle_settings_general`;';

        $this->connection->exec($sql);
    }
}
