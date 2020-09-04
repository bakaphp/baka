<?php

namespace Baka\Auth\Models;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Model;

class UserConfig extends Model
{
    use HashTableTrait;

    public ?int $users_id = null;
    public ?string $name = null;
    public ?string $value = null;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->setSource('user_config');
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
    }
}
