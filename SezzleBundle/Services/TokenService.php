<?php

namespace SwagPaymentSezzle\SezzleBundle\Services;

use Shopware\Components\CacheManager;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentSezzle\SezzleBundle\Resources\TokenResource;
use SwagPaymentSezzle\SezzleBundle\Structs\AuthCredentials;
use SwagPaymentSezzle\SezzleBundle\Structs\Token;

class TokenService
{
    const CACHE_ID = 'sezzle_auth_';

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param ClientService $client
     * @param AuthCredentials $credentials
     * @param int $shopId
     *
     * @return Token
     * @throws RequestException
     */
    public function getToken(ClientService $client, AuthCredentials $credentials, $shopId)
    {
        $token = $this->getTokenFromCache($shopId);
        if ($token === false || !$this->isTokenValid($token)) {
            $tokenResource = new TokenResource($client);

            $token = Token::fromArray($tokenResource->get($credentials));
            $this->setToken($token, $shopId);
        }

        return $token;
    }

    /**
     * @param int $shopId
     *
     * @return Token|false
     */
    private function getTokenFromCache($shopId)
    {
        return unserialize($this->cacheManager->getCoreCache()->load(self::CACHE_ID . $shopId));
    }

    /**
     * @param int $shopId
     */
    private function setToken(Token $token, $shopId)
    {
        $this->cacheManager->getCoreCache()->save(serialize($token), self::CACHE_ID . $shopId);
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function isTokenValid(Token $token)
    {
        $dateTimeNow = new \DateTime();
        $dateTimeExpire = new \DateTime(substr($token->getExpirationDate(), 0, 19));
        $dateTimeExpire = $dateTimeExpire->format('Y-m-d H:i:s');
        //Decrease expire date by one hour just to make sure, we don't run into an unauthorized exception.
        //$dateTimeExpire = $dateTimeExpire->sub(new \DateInterval('PT1H'));

        if ($dateTimeExpire < $dateTimeNow) {
            return false;
        }

        return true;
    }
}
