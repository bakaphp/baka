<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class AppsCustomFields extends Model
{
    public int $companies_id = 0;
    public int $users_id = 0;
    public string $model_name;
    public int $entity_id;
    public string $name;
    public string $label;
    public ?string $value = null;

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('apps_custom_fields');
    }
}
