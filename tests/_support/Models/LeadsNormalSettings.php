<?php

namespace Baka\Test\Support\Models;

use Baka\Database\Model;

class LeadsNormalSettings extends Model
{
    public function initialize()
    {
        $this->setSource('leads_settings');
    }
}
