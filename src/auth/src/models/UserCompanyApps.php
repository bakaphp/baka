<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class UserCompanyApps extends Model
{
    /**
     *
     * @var integer
     */
    public $company_id;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('apps_id', 'Baka\Auth\Models\Apps', 'id', ['alias' => 'app']);
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'Company']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'user_company_apps';
    }
}
