<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class FieldsTypeSettings extends Model
{
    public int $custom_fields_types_id;
    public string $name;
    public ?string $value = null;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_types_settings');
    }
}
