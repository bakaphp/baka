<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;
use Baka\Database\Contracts\HashTableTrait;

class CustomFields extends Model
{
    use HashTableTrait;

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
    public $user_id;

    /**
     * @var int
     */
    public $apps_id;

    /**
     * @var int
     */
    public $custom_fields_modules_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var int
     */
    public $fields_type_id;

    /**
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields';
    }

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize()
    {
        $this->belongsTo('fields_type_id', '\Baka\Database\CustomFields\FieldsType', 'id', ['alias' => 'type']);
        $this->belongsTo('custom_fields_modules_id', '\Baka\Database\CustomFields\Module', 'id', ['alias' => 'module']);
    }

    /**
     * Get the felds of this custom field module.
     *
     * @param string $module
     * @return void
     */
    public static function getFields(string $module): array
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
