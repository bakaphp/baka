<?php

namespace Baka\Auth\Models;

class SessionKeys extends \Phalcon\Mvc\Model
{
    /**
     * @var string
     */
    public $sessions_id;

    /**
     * @var integer
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
     * Initialize
     */
    public function initialize()
    {
        $this->belongsTo('sessions_id', 'Baka\Auth\Models\Sesssions', 'id', ['alias' => 'session']);
    }
}
