<?php

namespace Baka\Auth\Models;

use Baka\Database\Contracts\HashTableTrait;
use Baka\Database\Model;

class UserConfig extends Model
{
    use HashTableTrait;

    public int $users_id;
    public string $name;
    public string $value;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->setSource('user_config');
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
    }
}
