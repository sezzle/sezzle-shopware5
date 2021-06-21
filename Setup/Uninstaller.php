<?php

namespace SezzlePayment\Setup;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Shopware\Components\Model\ModelManager;
use SezzlePayment\Components\PaymentMethodProvider;

class Uninstaller
{

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Uninstaller constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function uninstall()
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->modelManager);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
    }

}
