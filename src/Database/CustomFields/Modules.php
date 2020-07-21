<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Apps;
use Baka\Database\Exception\Exception;
use Baka\Database\Model;

class Modules extends Model
{
    public int $apps_id;
    public string $model_name;
    public string $name;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_modules');
    }

    /**
     * Given the custom field table get its module.
     *
     * @param string $customFieldClassName
     * @param Apps $app
     *
     * @throws Exception
     *
     * @return Modules
     */
    public static function getByCustomFieldModuleByModuleAndApp(string $customFieldClassName, Apps $app) : Modules
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
