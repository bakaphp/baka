<?php

namespace Baka\Database;

class Apps extends Model
{
    public ?string $name = null;
    public ?string $description = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps');
    }
}
