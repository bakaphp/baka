<?php

namespace Baka\Test\Support\Models;

use Baka\Cashier\Billable;

class Users extends \Baka\Database\Model
{
    use Billable;

    public $stripe_id;

    public function initialize()
    {
        $this->hasMany('id', 'Baka\Cashier\Subscription', 'user_id', ['alias' => 'user']);
    }
}
