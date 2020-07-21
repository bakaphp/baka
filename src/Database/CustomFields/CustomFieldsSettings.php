<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class CustomFieldsSettings extends Model
{
    public int $companies_id;
    public string $name;
    public ?string $value = null;

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
