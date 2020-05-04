<?php

namespace Baka\Test\Model;

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
