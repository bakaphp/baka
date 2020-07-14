<?php
namespace Baka\Database;

class Apps extends Model
{
    public string $name;
    public ?string $description;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps');
    }
}
