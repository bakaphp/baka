<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class AppsRoles extends Model
{
    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var string
     */
    public $roles_name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('apps_id', 'Baka\Auth\Models\Apps', 'id', ['alias' => 'app']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'apps_roles';
    }
}
