<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class CompanySettings extends Model
{
    public int $company_id;
    public string $name;
    public string $value;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('company_settings');
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'company']);
    }
}
