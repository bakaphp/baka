<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class SessionKeys extends Model
{
    /**
     * @var string
     */
    public $sessions_id;

    /**
     * @var int
     */
    public $users_id;

    /**
     * @var string
     */
    public $last_ip;

    /**
     * @var string
     */
    public $last_login;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->belongsTo('sessions_id', 'Baka\Auth\Models\Sesssions', 'id', ['alias' => 'session']);
    }
}
