<?php

namespace SezzlePayment\Components\Services;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Exception;
use SezzlePayment\SezzleBundle\PaymentType;
use SezzlePayment\SezzleBundle\Resources\TokenizeResource;
use SezzlePayment\SezzleBundle\Structs\Tokenize;

class UserDataService
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var TokenizeResource
     */
    private $tokenizeResource;

    /**
     * UserDataService constructor.
     * @param Connection $dbalConnection
     * @param SettingsService $settingsService
     * @param TokenizeResource $tokenizeResource
     */
    public function __construct(
        Connection $dbalConnection,
        SettingsService $settingsService,
        TokenizeResource $tokenizeResource
    )
    {
        $this->dbalConnection = $dbalConnection;
        $this->settingsService = $settingsService;
        $this->tokenizeResource = $tokenizeResource;
    }

    /**
     * @see PaymentType
     */
    public function applyTokenizeAttributes()
    {
        $shopwareSession = Shopware()->Session();
        $userId = null;
        if (!empty($shopwareSession->sOrderVariables['sUserData']['additional']['user']['id'])) {
            $userId = $shopwareSession->sOrderVariables['sUserData']['additional']['user']['id'];
        }

        if (($token = $shopwareSession->offsetGet('sezzle_token'))
            && ($tokenExpiration = $shopwareSession->offsetGet('sezzle_token_expiration'))) {
            $tokenizeDetails = $this->tokenizeResource->get($token);
            /** @var Tokenize $tokenizeDetailsObj */
            $tokenizeDetailsObj = Tokenize::fromArray($tokenizeDetails);
            if ($userId) {
                $builder = $this->dbalConnection->createQueryBuilder();

                $builder->update('s_user_attributes', 'ua')
                    ->set('ua.sezzle_customer_uuid', ':customerUuid')
                    ->set('ua.sezzle_customer_uuid_status', ':customerUuidStatus')
                    ->set('ua.sezzle_customer_uuid_expiry', ':customerUuidExpiry')
                    ->where('ua.userID = :userID')
                    ->setParameters([
                        ':userID' => $userId,
                        ':customerUuid' => $tokenizeDetailsObj->getCustomer()->getUuid(),
                        ':customerUuidStatus' => true,
                        ':customerUuidExpiry' => $tokenizeDetailsObj->getCustomer()->getExpiration()
                    ])->execute();
            }
        }


    }

    /**
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function isCustomerUuidValid($userId)
    {
        $dateTimeNow = new DateTime('now', new DateTimeZone('UTC'));


        $customerUuidExpiry = $this->getValueByKey($userId, 'customer_uuid_expiry');

        if (!$customerUuidExpiry) {
            $this->deleteTokenizeRecord($userId);
            return false;
        }

        $customerUuidExpiry = new DateTime($customerUuidExpiry, new DateTimeZone('UTC'));

        if ($customerUuidExpiry->getTimestamp() < $dateTimeNow->getTimestamp()) {
            $this->deleteTokenizeRecord($userId);
            return false;
        }

        return true;

    }

    public function deleteTokenizeRecord($userId)
    {
        if ($userId) {
            $builder = $this->dbalConnection->createQueryBuilder();

            $builder->update('s_user_attributes', 'ua')
                ->set('ua.sezzle_customer_uuid', ':customerUuid')
                ->set('ua.sezzle_customer_uuid_status', ':customerUuidStatus')
                ->set('ua.sezzle_customer_uuid_expiry', ':customerUuidExpiry')
                ->where('ua.userID = :userID')
                ->setParameters([
                    ':userID' => $userId,
                    ':customerUuid' => null,
                    ':customerUuidStatus' => false,
                    ':customerUuidExpiry' => null
                ])->execute();
        }

    }

    /**
     * @param $userId
     * @param $key
     * @return bool|string
     */
    public function getValueByKey($userId, $key)
    {
        $attribute = sprintf("sezzle_%s", $key);
        //return !empty($userData['additional']['user'][$attribute]) ? $userData['additional']['user'][$attribute] : null;


        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        return $this->dbalConnection->createQueryBuilder()
            ->select('ua.' . $attribute)
            ->from('s_user_attributes', 'ua')
            ->where('ua.userID = :userId')
            ->setParameters([
                ':userId' => $userId
            ])
            ->execute()->fetchColumn(0);
    }

}
