<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class UsersAssociatedApps extends Model
{
    public int $users_id;
    public int $apps_id;
    public int $company_id;
    public string $identify_id;
    public int $user_active;
    public string $user_role;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_associated_apps');
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'company']);
    }
}
