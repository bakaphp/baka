<?php

namespace Test\Model;

use Baka\Database\Model;
use Baka\Database\Contracts\CustomFields\CustomFieldsTrait;
use Baka\Database\Contracts\HashTableTrait;

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
