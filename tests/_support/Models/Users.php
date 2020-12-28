<?php

namespace Baka\Test\Support\Models;

class Users extends \Baka\Database\Model
{
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
            Subscriptions::class,
            'user_id',
            [
                'alias' => 'subscriptions',
                'params' => ['order' => 'id DESC']
            ]
        );
        return $this->getRelated('subscriptions');
    }
}
