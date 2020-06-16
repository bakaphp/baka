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
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_values');
    }
}
