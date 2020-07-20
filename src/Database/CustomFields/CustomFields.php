<?php

namespace Baka\Database\CustomFields;

use Baka\Contracts\CustomFields\CustomFieldsTrait;
use Baka\Database\Model;

class CustomFields extends Model
{
    use CustomFieldsTrait;

    public int $companies_id = 0;
    public int $users_id = 0;
    public int $apps_id = 0;
    public int $custom_fields_modules_id = 1;
    public ?string $name = null;
    public ?string $label = null;
    public int $fields_type_id = 1;
    public ?string $attributes = null;

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields');

        $this->belongsTo('fields_type_id', '\Baka\Database\CustomFields\FieldsType', 'id', ['alias' => 'type']);
        $this->belongsTo('custom_fields_modules_id', '\Baka\Database\CustomFields\Module', 'id', ['alias' => 'module']);
    }

    /**
     * Get the fields of this custom field module.
     *
     * @param string $module
     *
     * @return void
     */
    public static function getFields(string $module) : array
    {
        $fields = [];

        if ($modules = Modules::findFirstByName($module)) {
            $customFields = self::find([
                'custom_fields_modules_id = ?0',
                'bind' => [$modules->id],
            ]);

            foreach ($customFields as $field) {
                $fields[] = [
                    'label' => !empty($field->label) ? $field->label : $field->name,
                    'name' => $field->name,
                    'type' => $field->type->name,
                ];
            }
        }

        return $fields;
    }
}
