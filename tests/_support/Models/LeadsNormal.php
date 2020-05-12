<?php

namespace Baka\Test\Support\Models;

use Baka\Database\Contracts\HashTableTrait;
use Baka\Database\Model;

class LeadsNormal extends Model
{
    use HashTableTrait;

    /**
     * Specify the table.
     *
     * @return void
     */
    public function getSource()
    {
        return 'leads';
    }
}
