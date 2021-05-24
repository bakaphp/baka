<?php

declare(strict_types=1);

namespace Baka\Notifications;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Models\Users;
use Phalcon\Di;

class Notify
{
    /**
     * Send the notification to all the users.
     *
     * @param array | ResultsetInterface $users
     * @param NotificationInterface $notification
     *
     * @return void
     */
    public static function all($users, NotificationInterface $notification)
    {
        foreach ($users as $user) {
            self::one($user, $notification);
        }
    }

    /**
     * Process just one.
     *
     * @param UserInterface $user
     * @param NotificationInterface $notification
     *
     * @return bool
     */
    public static function one(UserInterface $user, NotificationInterface $notification) : bool
    {
        if (Di::getDefault()->has('userData')) {
            $from = Di::getDefault()->get('userData');
        } else {
            $from = $user;
        }

        $notification->setTo($user);
        $notification->setFrom($from);

        return $notification->process();
    }
}
