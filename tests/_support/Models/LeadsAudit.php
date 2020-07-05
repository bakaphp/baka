<?php

namespace Baka\Test\Support\Models;

use Baka\Blameable\Blameable;
use Baka\Blameable\BlameableTrait;
use Baka\Database\Model;

class LeadsAudit extends Model
{
    use BlameableTrait;

    public function initialize()
    {
        $this->setSource('leads');
        $this->keepSnapshots(true);
        $this->addBehavior(new Blameable());
    }
}
