<?php

namespace Baka\Test\Support\Models;

use Baka\Database\Model;

class Subscriptions extends Model
{
    public function initialize()
    {
        $this->setSource('subscriptions');
        $this->belongsTo('user_id', 'Baka\Test\Support\Models\Users', 'id', ['alias' => 'user']);
    }
}
