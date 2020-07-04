<?php

namespace Baka\Test\Support\Models;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Model;

class LeadsNormal extends Model
{
    use HashTableTrait;

     /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->setSource('leads');
    }
  
}
