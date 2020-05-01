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
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields_settings';
    }
}
