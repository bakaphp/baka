<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class Apps extends Model
{
    public string $name;
    public string $description;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps');
    }
}
