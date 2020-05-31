<?php

declare(strict_types=1);

namespace Baka\Auth;

use Baka\Contracts\Auth\UserInterface;

class UserProvider
{
    protected static $userProvider;

    /**
     * Set provider.
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public static function set(UserInterface $user) : void
    {
        self::$userProvider = $user;
    }

    /**
     * Get the User Model by a provider.
     *
     * @return UserInterface
     */
    protected static function get() : UserInterface
    {
        return Di::getDefault()->has('userProvider') ? Di::getDefault()->get('userProvider') : self::$userProvider;
    }
}
