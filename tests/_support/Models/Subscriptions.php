<?php

namespace Baka\Test\Support\Models;

use Baka\Cashier\Subscription;

class Subscriptions extends Subscription
{
    public function initialize()
    {
        $this->setSource('subscriptions');
        $this->belongsTo('user_id', 'Baka\Test\Support\Models\Users', 'id', ['alias' => 'user']);
    }
}
