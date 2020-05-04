<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class Banlist extends Model
{
    public $id;

    /**
     * @var integer
     */
    public $users_id;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $email;

    /**
     * Initialize
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
    }
}
