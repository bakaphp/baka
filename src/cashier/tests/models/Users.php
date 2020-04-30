<?php

namespace App\Models;

use Phalcon\Cashier\Billable;

class Users extends \Baka\Database\Model
{
    use Billable;

    public function initialize()
    {
        $this->hasMany('id', '\Phalcon\Cashier\Subscription', 'user_id', ['alias' => 'user']);
    }
}
