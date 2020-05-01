<?php

namespace Test\Model;

use Baka\Database\Model;
use Baka\Blameable\Blameable;
use Baka\Blameable\BlameableTrait;

class Leads extends Model
{
    use BlameableTrait;
    
    public function initialize()
    {
        $this->keepSnapshots(true);
        $this->addBehavior(new Blameable());
    }

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
