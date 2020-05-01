<?php

declare(strict_types=1);

namespace Baka\Auth\Contracts;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\ValidationData;

/**
 * Trait For JWT User Auth Token.
 *
 * @package Gewaer\Traits
 *
 * @property Users $user
 * @property Config $config
 * @property Request $request
 * @property Auth $auth
 * @property \Phalcon\Di $di
 *
 */
trait AuthTokenTrait
{
    /**
    * Returns the string token.
    *
    * @return string
    * @throws ModelException
    */
    public function getToken(): array
    {
        $random = new \Phalcon\Security\Random();
        $sessionId = $random->uuid();

        $signer = new Sha512();
        $builder = new Builder();
        $token = $builder
            ->setIssuer(getenv('TOKEN_AUDIENCE'))
            ->setAudience(getenv('TOKEN_AUDIENCE'))
            ->setId($sessionId, true)
            ->setIssuedAt(time())
            ->setNotBefore(time() + 500)
            ->setExpiration(time() + $this->di->getConfig()->jwt->payload->exp)
            ->set('sessionId', $sessionId)
            ->set('email', $this->getEmail())
            ->sign($signer, getenv('TOKEN_PASSWORD'))
            ->getToken();

        $refreshToken = $builder
            ->setIssuer(getenv('TOKEN_AUDIENCE'))
            ->setAudience(getenv('TOKEN_AUDIENCE'))
            ->setId($sessionId, true)
            ->setIssuedAt(time())
            ->setNotBefore(time() + 500)
            ->setExpiration(time() + $this->di->getConfig()->jwt->payload->refresh_exp)
            ->set('sessionId', $sessionId)
            ->set('email', $this->getEmail())
            ->sign($signer, getenv('TOKEN_PASSWORD'))
            ->getToken();

        return [
            'sessionId' => $sessionId,
            'token' => $token->__toString(),
            'refresh_token' => $refreshToken->__toString()
        ];
    }

    /**
     * Returns the ValidationData object for this record (JWT).
     *
     * @return ValidationData
     * @throws ModelException
     */
    public static function getValidationData(string $id): ValidationData
    {
        $validationData = new ValidationData();
        $validationData->setIssuer(getenv('TOKEN_AUDIENCE'));
        $validationData->setAudience(getenv('TOKEN_AUDIENCE'));
        $validationData->setId($id);
        $validationData->setCurrentTime(time() + 500);

        return $validationData;
    }
}
