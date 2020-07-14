<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class UserCompanyApps extends Model
{
    public int $company_id;
    public int $apps_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('user_company_apps');
        $this->belongsTo('apps_id', 'Baka\Auth\Models\Apps', 'id', ['alias' => 'app']);
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'Company']);
    }
}
