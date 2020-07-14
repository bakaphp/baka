<?php

namespace Baka\Database\CustomFields;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Model;

class CustomFields extends Model
{
    use HashTableTrait;

    public int $companies_id;
    public int $user_id;
    public int $apps_id;
    public int $custom_fields_modules_id;
    public string $name;
    public ?string $label = null;
    public int $fields_type_id;
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
