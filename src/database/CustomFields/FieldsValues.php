<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class FieldsValues extends Model
{
    /**
     * @var int
     */
    public $custom_fields_id;

    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $is_default;

    /**
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields_values';
    }
}
