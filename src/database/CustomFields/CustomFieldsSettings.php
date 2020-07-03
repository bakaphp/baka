<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class CustomFieldsSettings extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var int
     */
    public $companies_id;

    /**
     * @var int
     */
    public $name;

    /**
     * @var int
     */
    public $value;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_settings');
    }
}
