<?php

declare(strict_types=1);

namespace Baka\Contracts\Auth;

interface UserInterface
{
    public function getId();

    public function isLoggedIn() : bool;

    public function isAnonymous() : bool;
}
