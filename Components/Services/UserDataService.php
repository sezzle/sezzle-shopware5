<?php

namespace SwagPaymentSezzle\Components\Services;

use Doctrine\DBAL\Connection;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;
use SwagPaymentSezzle\SezzleBundle\PaymentType;
use SwagPaymentSezzle\SezzleBundle\Resources\TokenizeResource;
use SwagPaymentSezzle\SezzleBundle\Structs\Tokenize;

class UserDataService
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
                    ->set('ua.swag_sezzle_customer_uuid', ':customerUuid')
                    ->set('ua.swag_sezzle_customer_uuid_status', ':customerUuidStatus')
                    ->set('ua.swag_sezzle_customer_uuid_expiry', ':customerUuidExpiry')
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

    public function isCustomerUuidValid($userId)
    {
        $dateTimeNow = new \DateTime();


        $customerUuidExpiry = $this->getValueByKey($userId, 'customer_uuid_expiry');

        if (!$customerUuidExpiry) {
            return false;
        }

        $customerUuidExpiry = new \DateTime($customerUuidExpiry);

        if ($customerUuidExpiry->getTimestamp() < $dateTimeNow->getTimestamp()) {
            return false;
        }

        return true;

    }

    public function getValueByKey($userId, $key)
    {
        $attribute = sprintf("swag_sezzle_%s", $key);
        //return !empty($userData['additional']['user'][$attribute]) ? $userData['additional']['user'][$attribute] : null;


        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        return $this->dbalConnection->createQueryBuilder()
            ->select('ua.'.$attribute)
            ->from('s_user_attributes', 'ua')
            ->where('ua.userID = :userId')
            ->setParameters([
                ':userId' => $userId
            ])
            ->execute()->fetchColumn(0);
    }

}
