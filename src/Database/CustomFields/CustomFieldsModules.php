<?php
declare(strict_types=1);

namespace Baka\Database\CustomFields;

use Baka\Database\Model;

class CustomFieldsModules extends Model
{
    public ?int $apps_id = 0;
    public ?string $name = null;
    public ?string $model_name = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('custom_fields_modules');
    }
}
