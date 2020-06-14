<?php

namespace Baka\Contracts\CustomFields;

use Baka\Database\Apps;
use Baka\Database\CustomFields\CustomFields;
use Baka\Database\CustomFields\FieldsType;
use Baka\Database\CustomFields\FieldsValues;
use Baka\Database\CustomFields\Modules;
use Phalcon\Utils\Slug;
use ReflectionClass;

/**
 * Custom field class.
 */
trait CustomFieldsTasksTrait
{
    /**
     * This will list you all the custom field the current app is handling.
     *
     * @return void
     */
    public function mainAction()
    {
        $modules = Modules::find();

        echo 'This App has the current list of custom field module ' . PHP_EOL;
        foreach ($modules as $module) {
            echo $module->model_name . ' - ' . $module->name . PHP_EOL;
        }
    }

    /**
     * Create a new custom field Module to work with.
     *
     * @param array $params
     *
     * @return void
     */
    public function createModuleAction(array $params)
    {
        if (count($params) != 3) {
            echo 'We are expecting name and the model name to create its custom field Module registration' . PHP_EOL;
            return;
        }

        $name = $params[0];
        $model = $params[1];
        $apps = $params[2];

        if (!$apps = Apps::findFirstByName($apps)) {
            echo 'No app found with that name' . $apps . PHP_EOL;
            return;
        }

        $model = new $model();

        $customFieldModel = new Modules();
        $customFieldModel->apps_id = $apps->getId();
        $customFieldModel->companies_id = 1;
        $customFieldModel->name = $name;
        $customFieldModel->model_name = get_class($model);
        $customFieldModel->saveOrFail();

        $table = $model->getSource() . '_custom_fields';

        $sql = '
                CREATE TABLE IF NOT EXISTS `' . $table . '` (
                    `id` bigint(11) UNSIGNED NOT NULL,
                    `' . $model->getSource() . '_id` int(10) UNSIGNED NOT NULL,
                    `custom_fields_id` int(10) UNSIGNED NOT NULL,
                    `value` text DEFAULT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                ALTER TABLE `' . $table . '`
                    ADD PRIMARY KEY (`id`),
                    ADD KEY `' . $model->getSource() . '_id` (`' . $model->getSource() . '_id`),
                    ADD KEY `created_at` (`created_at`),
                    ADD KEY `updated_at` (`updated_at`),
                    ADD KEY `custom_fields_id` (`custom_fields_id`),
                    ADD KEY `' . $model->getSource() . '_id_2` (`' . $model->getSource() . '_id`,`custom_fields_id`);
                ALTER TABLE `' . $table . '`
                    MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;
            ';

        if ($this->getDI()->getDb()->query($sql)) {
            $namespace = str_replace('\\', '\\\\', $this->getDI()->getConfig()->namespace->models);
            $extends = explode('\\', (new ReflectionClass($model))->getParentClass()->getName());
            $extends = end($extends);

            system('cd ' . getenv('BASE_DIR') . '; /usr/bin/php ' . getenv('BASE_DIR') . '/vendor/phalcon/devtools/phalcon.php model ' . $table . ' --namespace=' . $namespace . ' --extends=' . $extends . ' --mapcolumn --excludefields=created_at,updated_at > /dev/null');
        }

        echo 'Custom Field Module Created ' . get_class($model);
        return 'Custom Field Module Created ' . get_class($model);
    }

    /**
     * Given the params create the fields for that specific module.
     *
     * @param array $params
     *
     * @return void
     */
    public function createFieldsAction(array $params)
    {
        if (count($params) != 5) {
            echo 'We are expecting name and the model name to create its custom field Module registration' . PHP_EOL;
            return;
        }

        $name = Slug::generate($params[0], '_');
        $label = $params[0];
        $model = $params[1];
        $apps = $params[2];
        $type = $params[3] ?? 1;
        $customFieldDefaultValues = $params[4] ?? null;

        if (!$apps = Apps::findFirstByName($apps)) {
            echo 'No app found with that name' . $apps . PHP_EOL;
            return;
        }

        $model = new $model();

        $modules = Modules::getByCustomFieldModuleByModuleAndApp(get_class($model), $apps);
        $type = FieldsType::findFirstByName($type);
        $customFields = null;

        $customFields = CustomFields::findFirst([
            'conditions' => 'name = ?0 AND apps_id = ?1 and custom_fields_modules_id = ?2',
            'bind' => [$name, $apps->getId(), $modules->getId()]
        ]);

        if (!is_object($customFields)) {
            $customFields = new CustomFields();
        }

        $customFields->users_id = 0; //always 1
        $customFields->companies_id = 0; //always 1
        $customFields->apps_id = $apps->getId(); //always 1
        $customFields->custom_fields_modules_id = $modules->getId();
        $customFields->fields_type_id = $type->getId(); //right now just text
        $customFields->label = $label;
        $customFields->name = $name;

        if (!$customFields->saveOrFail()) {
            echo implode("\n", $customFields->getMessages());
            return;
        }

        //campo|none:nonenone;campos|none:none:none;
        if ($customFieldDefaultValues) {
            $values = $customFieldDefaultValues;
            $values = preg_split('/\n|,|\|/', $values);

            foreach ($values as $value) {
                $value = explode(':', $value);

                $customFieldsValues = new FieldsValues();
                $customFieldsValues->custom_fields_id = $customFields->id;
                $customFieldsValues->is_default = 0;

                if (count($value) > 1) {
                    $customFieldsValues->label = trim($value[0]);
                    $customFieldsValues->value = trim($value[1]);
                } else {
                    $customFieldsValues->label = trim($value[0]);
                    $customFieldsValues->value = Slug::generate($value[0], '_');
                }

                $customFieldsValues->saveOrFail();
            }
        }

        echo 'Custom field created for ' . get_class($model) . ' On the App' . $apps->name . PHP_EOL;
        return  'Custom field created for ' . get_class($model) . ' On the App' . $apps->name . PHP_EOL;
    }
}
