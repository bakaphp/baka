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
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_types_settings');
    }
}
