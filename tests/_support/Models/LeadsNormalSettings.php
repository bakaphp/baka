<?php

namespace Baka\Test\Support\Models;

use Baka\Database\Model;

class LeadsNormalSettings extends Model
{
    /**
     * Specify the table.
     *
     * @return void
     */
    public function getSource()
    {
        return 'leads_settings';
    }
}
