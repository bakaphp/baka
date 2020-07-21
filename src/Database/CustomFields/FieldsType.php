<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class FieldsType extends Model
{
    public string $name;
    public ?string $description = null;
    public ?string $icon = null;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_types');
    }
}
