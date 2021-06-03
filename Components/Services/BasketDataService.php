<?php

namespace SezzlePayment\Components\Services;

use Doctrine\DBAL\Connection;
use SezzlePayment\SezzleBundle\Components\SettingsServiceInterface;
use SezzlePayment\SezzleBundle\PaymentType;

class BasketDataService
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * BasketDataService constructor.
     * @param Connection $dbalConnection
     * @param SettingsServiceInterface $settingsService
     */
    public function __construct(
        Connection $dbalConnection,
        SettingsServiceInterface $settingsService
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->settingsService = $settingsService;
    }

    /**
     * @param int $basketId
     * @param string $orderUuid
     * @see PaymentType
     */
    public function applyOrderUuidAttribute($basketId, $orderUuid)
    {

        $builder = $this->dbalConnection->createQueryBuilder();

        $builder->update('s_order_basket_attributes', 'ba')
            ->set('ba.sezzle_order_uuid', ':orderUuid')
            ->where('ba.basketId = :basketId')
            ->setParameters([
                ':basketId' => $basketId,
                ':orderUuid' => $orderUuid,
            ])->execute();
    }

    /**
     * @param array $basket
     * @param string $key
     * @return bool|string
     */
    public function getValueByKey($basket, $key)
    {
        echo "<pre>";
        $attribute = sprintf("sezzle_%s", $key);
        $basketId = function ($basket) {
            foreach ($basket['content'] as $lineItem) {
                return $lineItem['id'];
            }
            return null;
        };


        return $this->dbalConnection->createQueryBuilder()
            ->select('ba.'.$attribute)
            ->from('s_order_basket_attributes', 'ba')
            ->where('ba.basketID = :basketID')
            ->setParameters([
                ':basketID' => $basketId($basket),
            ])
            ->execute()->fetchColumn(0);
    }

}
