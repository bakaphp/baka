<?php

namespace Baka\Test\Support\Models;

use Baka\Database\Model;

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
