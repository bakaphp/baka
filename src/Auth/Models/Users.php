<?php

namespace Baka\Auth\Models;

use Baka\Contracts\Auth\UserInterface;
use Baka\Database\Model;

class Users extends Model implements UserInterface
{
    /**
     * Is loggedIn.
     *
     * @return bool
     */
    public function isLoggedIn() : bool
    {
        return false;
    }

    /**
     * Is Anonymous?
     *
     * @return bool
     */
    public function isAnonymous() : bool
    {
        return false;
    }
}
