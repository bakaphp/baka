<?php

declare(strict_types=1);

namespace Baka\Auth;

use Baka\Contracts\Auth\UserInterface;
use Phalcon\Di;
use Baka\Auth\Models\Users;

class UserProvider
{
    protected static ?UserInterface $userProvider = null;

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
    public static function get() : UserInterface
    {
        self::$userProvider = self::$userProvider ?? new Users();
        return Di::getDefault()->has('userProvider') ? Di::getDefault()->get('userProvider') : self::$userProvider;
    }
}
