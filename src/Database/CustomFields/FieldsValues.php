<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class FieldsValues extends Model
{
    public int $custom_fields_id;
    public string $value;
    public int $is_default;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_values');
    }
}
