<?php

namespace Baka\Test\Support\Models;

use Baka\Contracts\CustomFields\CustomFieldsTrait;
use Baka\Database\Model;

class LeadsCustomFields extends Model
{
    use CustomFieldsTrait;

    public function initialize()
    {
        $this->setSource('leads_custom_fields');
    }
}
