<?php

declare(strict_types=1);

namespace Baka\Contracts\Notifications;

use Baka\Notifications\Notify;

trait NotifiableTrait
{
    /**
     * Notify a given User entity.
     *
     * @param NotificationInterface $notification
     *
     * @return bool
     */
    public function notify(NotificationInterface $notification) : bool
    {
        return Notify::one($this, $notification);
    }
}
