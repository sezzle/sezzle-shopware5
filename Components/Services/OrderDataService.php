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
    /**
     * @var UserDataService
     */
    private $userDataService;

    /**
     * OrderDataService constructor.
     * @param Connection $dbalConnection
     * @param SettingsServiceInterface $settingsService
     * @param TokenizeResource $tokenizeResource
     * @param UserDataService $userDataService
     */
    public function __construct(
        Connection $dbalConnection,
        SettingsServiceInterface $settingsService,
        TokenizeResource $tokenizeResource,
        UserDataService $userDataService
    )
    {
        $this->dbalConnection = $dbalConnection;
        $this->settingsService = $settingsService;
        $this->tokenizeResource = $tokenizeResource;
        $this->userDataService = $userDataService;
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

    /**
     * @param string $orderNumber
     * @return mixed
     */
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
     * @param array $data
     * @see PaymentType
     */
    public function applyPaymentAttributes($orderNumber, $data)
    {

        if (empty($data)) {
            return;
        }

        $builder = $this->dbalConnection->createQueryBuilder();

        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        $subQuery = $this->dbalConnection->createQueryBuilder()
            ->select('o.id')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->getSQL();
        $parameters = [
            ':orderNumber' => $orderNumber
        ];


        $builder->update('s_order_attributes', 'oa');

        foreach ($data as $key => $value) {
            if ($value >= 0) {
                switch ($key) {
                    case 'referenceId':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_reference_id', ':referenceId');
                        break;
                    case 'orderUuid':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_order_uuid', ':orderUuid');
                        break;
                    case 'authAmount':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_auth_amount', ':authAmount');
                        break;
                    case 'capturedAmount':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_captured_amount', ':capturedAmount');
                        break;
                    case 'refundedAmount':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_refunded_amount', ':refundedAmount');
                        break;
                    case 'releasedAmount':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_released_amount', ':releasedAmount');
                        break;
                    case 'paymentAction':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_payment_action', ':paymentAction');
                        break;
                    case 'authExpiry':
                        $parameters[':' . $key] = $value;
                        $builder->set('oa.swag_sezzle_auth_expiry', ':authExpiry');
                        break;
                }

            }
        }

        $builder->where('oa.orderID = (' . $subQuery . ')')
            ->setParameters($parameters)
            ->execute();

    }

    /**
     * @param string $orderNumber
     */
    public function applyTokenizeAttributes($orderNumber)
    {
        $userId = $this->dbalConnection->createQueryBuilder()
            ->select('o.userID')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute()->fetchColumn(0);
        if (!$userId) {
            return;
        }

        $customerUuid = $this->userDataService->getValueByKey($userId, 'customer_uuid');
        $customerUuidExpiry = $this->userDataService->getValueByKey($userId, 'customer_uuid_expiry');
        if (!$customerUuid || !$customerUuidExpiry) {
            return;
        }
        $builder = $this->dbalConnection->createQueryBuilder();

        $subQuery = $this->dbalConnection->createQueryBuilder()
            ->select('o.id')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->getSQL();

        $builder->update('s_order_attributes', 'oa')
            ->set('oa.swag_sezzle_customer_uuid', ':customerUuid')
            ->set('oa.swag_sezzle_customer_uuid_expiry', ':customerUuidExpiry')
            ->where('oa.orderID = (' . $subQuery . ')')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':customerUuid' => $customerUuid,
                ':customerUuidExpiry' => $customerUuidExpiry
            ])->execute();
    }

    /**
     *
     */
    public function clearSezzleSessionData()
    {
        $shopwareSession = Shopware()->Session();
        $shopwareSession->offsetUnset('sezzle_token');
        $shopwareSession->offsetUnset('sezzle_token_expiration');
    }

}
