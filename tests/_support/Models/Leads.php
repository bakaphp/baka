<?php

namespace Baka\Test\Support\Models;

use Baka\Contracts\CustomFields\CustomFieldsTrait;
use Baka\Database\Model;

class Leads extends Model
{
    use CustomFieldsTrait;
    public bool $elasticSearchTextFieldData = true;

    public function initialize()
    {
        $this->setSource('leads');
        $this->belongsTo('users_id', 'Baka\Test\Support\Models\Users', 'id', ['alias' => 'user', 'elasticAlias' => 'user']);
    }
}
