<?php

declare(strict_types=1);

namespace Baka\Contracts\Auth;

use Baka\Http\Exception\InternalServerErrorException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\ValidationData;
use Phalcon\Di;
use Phalcon\Security\Random;

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
     *
     * @throws ModelException
     */
    public function getToken() : array
    {
        $random = new Random();
        $sessionId = $random->uuid();

        $token = self::createJwtToken($sessionId, $this->getEmail(), $this->timezone);
        $refreshToken = self::createJwtToken($sessionId, $this->getEmail(), $this->timezone);

        return [
            'sessionId' => $sessionId,
            'token' => $token['token'],
            'refresh_token' => $refreshToken['token']
        ];
    }

    /**
     * Returns the ValidationData object for this record (JWT).
     *
     * @return ValidationData
     *
     * @throws ModelException
     */
    public static function getValidationData(string $id) : ValidationData
    {
        $validationData = new ValidationData();
        $validationData->setIssuer(getenv('TOKEN_AUDIENCE'));
        $validationData->setAudience(getenv('TOKEN_AUDIENCE'));
        $validationData->setId($id);
        $validationData->setCurrentTime(time() + 500);

        return $validationData;
    }

    /**
     * Create a new session based off the refresh token session id.
     *
     * @param string $sessionId
     * @param string $email
     *
     * @return array
     */
    public static function createJwtToken(string $sessionId, string $email, ?string $timezone = null) : array
    {
        $signer = new Sha512();
        $builder = new Builder();
        $timezone = $timezone ?? date_default_timezone_get();
        if (!date_default_timezone_set($timezone)) {
            throw new InternalServerErrorException('Timezone is invalid');
        }
        $token = $builder
            ->setIssuer(getenv('TOKEN_AUDIENCE'))
            ->setAudience(getenv('TOKEN_AUDIENCE'))
            ->setId($sessionId, true)
            ->setIssuedAt(time())
            ->setNotBefore(time() + 500)
            ->setExpiration(time() + Di::getDefault()->get('config')->jwt->payload->exp ?? 604800)
            ->set('sessionId', $sessionId)
            ->set('email', $email)
            ->sign($signer, getenv('TOKEN_PASSWORD'))
            ->getToken();

        return [
            'sessionId' => $sessionId,
            'token' => $token->__toString()
        ];
    }
}
