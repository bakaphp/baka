<?php

namespace Baka\Test\Support\Models;

use Baka\Cashier\Billable;

class Users extends \Baka\Database\Model
{
    use Billable;

    public $stripe_id;

    public function initialize()
    {
        $this->hasMany('id', 'Baka\Test\Support\Models\Subscriptions', 'user_id', ['alias' => 'subscriptions']);
    }

    
    /**
     * Get all of the subscriptions for the user.
     */
    public function subscriptions()
    {
        $this->hasMany(
            'id',
            subscriptions::class,
            'user_id',
            [
                'alias' => 'subscriptions',
                'params' => ['order' => 'id DESC']
            ]
        );
        return $this->getRelated('subscriptions');
    }
}
