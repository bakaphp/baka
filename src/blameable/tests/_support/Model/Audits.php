<?php

namespace Test\Model;

use Baka\Database\Model;
use Baka\Blameable\Blameable;
use Baka\Blameable\BlameableTrait;

class Audits extends Model
{
    /**
     * Specify the table.
     *
     * @return void
     */
    public function getSource()
    {
        return 'audits';
    }
}
