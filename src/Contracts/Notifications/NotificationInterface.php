<?php
declare(strict_types=1);

namespace Baka\Contracts\Notifications;

use Baka\Contracts\Auth\UserInterface;

interface NotificationInterface
{
    /**
     * Undocumented function.
     *
     * @return void
     */
    public function message() : string;

    public function process() : bool;

    public function trigger() : bool;

    public function sendToQueue() : bool;

    public function setTo(UserInterface $user) : void;

    public function setFrom(UserInterface $user) : void;
}
