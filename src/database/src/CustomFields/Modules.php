<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;
use Baka\Database\Exception\Exception;
use Baka\Database\Apps;

class Modules extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $apps_id;

    /**
     * @var string
     */
    public $model_name;

    /**
     * @var string
     */
    public $name;

    /**
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields_modules';
    }

    /**
     * Given the custom field table get its module
     *
     * @param string $customFieldClassName
     * @param Apps $app
     * @throws Exception
     * @return Modules
     */
    public static function getByCustomeFieldModuleByModuleAndApp(string $customFieldClassName, Apps $app): Modules
    {
        $model = self::findFirst([
            'conditions' => 'model_name = ?0 and apps_id = ?1',
            'bind' => [$customFieldClassName, $app->getId()]
        ]);
        
        if (!is_object($model)) {
            throw new Exception('No Custom Field define for this class ' . $customFieldClassName);
        }

        return $model;
    }
}
