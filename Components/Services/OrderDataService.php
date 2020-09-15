<?php

namespace SwagPaymentSezzle\Components\Services;

use Doctrine\DBAL\Connection;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;
use SwagPaymentSezzle\SezzleBundle\PaymentType;
use SwagPaymentSezzle\SezzleBundle\Resources\TokenizeResource;
use SwagPaymentSezzle\SezzleBundle\Structs\Order;
use SwagPaymentSezzle\SezzleBundle\Structs\Tokenize;

class OrderDataService
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
     * @var TokenizeResource
     */
    private $tokenizeResource;

    public function __construct(
        Connection $dbalConnection,
        SettingsServiceInterface $settingsService,
        TokenizeResource $tokenizeResource
    )
    {
        $this->dbalConnection = $dbalConnection;
        $this->settingsService = $settingsService;
        $this->tokenizeResource = $tokenizeResource;
    }

    /**
     * @param string $orderNumber
     */
    public function setClearedDate($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.cleareddate', 'NOW()')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param int $orderStatusId
     */
    public function setOrderStatus($orderNumber, $orderStatusId)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.status', $orderStatusId)
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();
    }

    public function getOrder($orderNumber)
    {
        return $this->dbalConnection->createQueryBuilder()
            ->select('*')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->setParameters([
                ':orderNumber' => $orderNumber
            ])
            ->execute()->fetch();
    }

    /**
     * @param string $orderNumber
     * @param Order $order
     * @see PaymentType
     */
    public function applyPaymentAttributes($orderNumber, Order $order, $isFullCaptured = true)
    {

        $builder = $this->dbalConnection->createQueryBuilder();

        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        $subQuery = $this->dbalConnection->createQueryBuilder()
            ->select('o.id')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->getSQL();

        $capturedAmount = $isFullCaptured ? $order->getAuthorization()->getAuthorizationAmount()->getAmountInCents() : 0;
//        foreach ($order->getAuthorization()->getCaptures() as $item) {
//            $capturedAmount += $item->getAmount()->getAmountInCents();
//        }


        $parameters = [
            ':orderNumber' => $orderNumber,
            ':referenceId' => $order->getReferenceId(),
            ':orderUuid' => $order->getUuid(),
            ':authAmount' => $order->getAuthorization()->getAuthorizationAmount()->getAmountInCents()
        ];


        $builder->update('s_order_attributes', 'oa')
            ->set('oa.swag_sezzle_reference_id', ':referenceId')
            ->set('oa.swag_sezzle_order_uuid', ':orderUuid')
            ->set('oa.swag_sezzle_auth_amount', ':authAmount')
            ->where('oa.orderID = (' . $subQuery . ')');


        if ($capturedAmount > 0) {
            $builder->set('oa.swag_sezzle_captured_amount', ':capturedAmount');
            $parameters[':capturedAmount'] = $capturedAmount;

        }

        $builder->setParameters($parameters)
            ->execute();
    }

    public function applyTokenizeAttributes($orderNumber)
    {
        $shopwareSession = Shopware()->Session();

        if ($token = $shopwareSession->offsetGet('sezzle_token')
            && $tokenExpiration = $shopwareSession->offsetGet('sezzle_token_expiration')) {
            $tokenizeDetails = $this->tokenizeResource->get($token);
            /** @var Tokenize $tokenizeDetailsObj */
            $tokenizeDetailsObj = Tokenize::fromArray($tokenizeDetails);
            $builder = $this->dbalConnection->createQueryBuilder();

            $builder->update('s_order_attributes', 'oa')
                ->set('oa.swag_sezzle_customer_uuid', ':customerUuid')
                ->set('oa.swag_sezzle_customer_uuid_expiry', ':customerUuidExpiry')
                ->where('oa.orderNumber = :orderNumber')
                ->setParameters([
                    ':orderNumber' => $orderNumber,
                    ':customerUuid' => $tokenizeDetailsObj->getCustomer()->getUuid(),
                    ':customerUuidExpiry' => $tokenizeDetailsObj->getCustomer()->getExpiration()
                ])->execute();
        }
    }

    public function clearSezzleSessionData()
    {
        $shopwareSession = Shopware()->Session();
        $shopwareSession->offsetUnset('sezzle_token');
        $shopwareSession->offsetUnset('sezzle_token_expiration');
    }

}
