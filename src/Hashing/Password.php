<?php

declare(strict_types=1);

namespace Baka\Hashing;

use Baka\Contracts\Auth\UserInterface;

class Password
{
    /**
     * The default cost factor.
     *
     * @var int
     */
    protected static $rounds = 12;

    /**
     * Has for the user password.
     *
     * @param string $password
     *
     * @return string
     */
    public static function make(string $password) : string
    {
        $options = [
            //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM), // Never use a static salt or one that is not randomly generated.
            'cost' => self::$rounds, // the default cost is 10
        ];

        return  password_hash($password, PASSWORD_DEFAULT, $options);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     *
     * @return bool
     */
    public static function check($value, $hashedValue, array $options = []) : bool
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the user password needs to ve rehash.
     *
     * @param string $password
     *
     * @return boolean
     */
    public static function needsRehash(string $password) : bool
    {
        $options = [
            //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM), // Never use a static salt or one that is not randomly generated.
            'cost' => self::$rounds, // the default cost is 10
        ];

        return password_needs_rehash($password, PASSWORD_DEFAULT, $options);
    }

    /**
     * Given any entity with password , verify if the password need rehash and update it.
     *
     * @param string $password
     * @param object $entity
     *
     * @return boolean
     */
    public static function rehash(string $password, UserInterface $entity) : bool
    {
        if (self::needsRehash($entity->password)) {
            $entity->password = self::make($password);
            $entity->updateOrFail();

            return true;
        }

        return false;
    }
}
