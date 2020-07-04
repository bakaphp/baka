<?php

namespace Baka\Test\Support\Models;

use Baka\Database\Model;

class Audits extends Model
{
    public function initialize()
    {
        $this->setSource('audits');
    }
}
