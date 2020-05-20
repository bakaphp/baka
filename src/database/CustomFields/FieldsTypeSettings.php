<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class FieldsTypeSettings extends Model
{
    /**
     * @var int
     */
    public $custom_fields_types_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $value;

    /**
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields_types_settings';
    }
}
