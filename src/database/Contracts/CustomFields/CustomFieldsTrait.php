<?php

namespace Baka\Database\Contracts\CustomFields;

use Baka\Database\CustomFields\Modules;
use Baka\Database\CustomFields\CustomFields;
use Baka\Database\Model as BakaModel;
use Exception;
use ReflectionClass;
use PDO;
use Baka\Database\Model;

/**
 * Custom field class.
 */
trait CustomFieldsTrait
{
    public $customFields = [];

    /**
     * Get the custom fields of the current object.
     *
     * @return Array
     *
     */
    public function getCustomFields(): array
    {
        $classReflection = new ReflectionClass($this);
        $className = $classReflection->getShortName();
        $classNamespace = $classReflection->getNamespaceName();

        if (!$module = Modules::findFirstByModelName(get_class($this))) {
            throw new Exception('Trying to get custom fields of a Model ' . $className . ' that doesnt use it');
        }

        $model = $classNamespace . '\\' . ucfirst($module->name) . 'CustomFields';
        $modelId = strtolower($module->name) . '_id';

        $customFields = CustomFields::findByCustomFieldsModulesId($module->getId());

        foreach ($customFields as $customField) {
            $result[$customField->label ?? $customField->name] = [
                'type' => $customField->type->name,
                'label' => $customField->name,
                'attributse' => $customField->attributes ? json_decode($customField->attributes) : null
            ];
        }

        return $result;
    }

    /**
     * Get all custom fields of the given object.
     *
     * @param  array  $fields
     * @return Phalcon\Mvc\Model
     */
    public function getAllCustomFields(array $fields = [])
    {
        try {
            $module = Modules::getByCustomeFieldModuleByModuleAndApp(get_class($this), $this->di->getApp());
        } catch (Exception $e) {
            return [];
        }

        $conditions = [];
        $fieldsIn = null;

        if (!empty($fields)) {
            $fieldsIn = " and name in ('" . implode("','", $fields) . ')';
        }

        //$conditions = 'modules_id = ? ' . $fieldsIn;

        $bind = [$this->getId(), $module->getId()];

        $customFieldsValueTable = $this->getSource() . '_custom_fields';

        $result = $this->getReadConnection()->prepare("SELECT l.{$this->getSource()}_id,
                                                        c.id as field_id,
                                                        c.name,
                                                        l.value ,
                                                        c.users_id,
                                                        l.created_at,
                                                        l.updated_at
                                                        FROM {$customFieldsValueTable} l,
                                                        custom_fields c
                                                        WHERE c.id = l.custom_fields_id
                                                            AND l.{$this->getSource()}_id = ?
                                                            AND c.custom_fields_modules_id = ? ");

        $result->execute($bind);

        // $listOfCustomFields = $result->fetchAll();
        $listOfCustomFields = [];

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $listOfCustomFields[$row->name] = $row->value;
        }

        return $listOfCustomFields;
    }

    /**
     * Allows to query a set of records that match the specified conditions.
     *
     * @param mixed $parameters
     * @return Content[]
     */
    public static function find($parameters = null)
    {
        $results = parent::find($parameters);
        $newResult = [];

        if ($results) {
            foreach ($results as $result) {
                $customFields = $result->getAllCustomFields([]);
                if (is_array($customFields)) {
                    //field the object
                    foreach ($customFields as $key => $value) {
                        $result->{$key} = $value;
                    }

                    $newResult[] = $result;
                }
            }

            unset($results);
        }
        return $newResult;
    }

    /**
     * Allows to query the first record that match the specified conditions.
     *
     * @param mixed $parameters
     * @return Content
     */
    public static function findFirst($parameters = null)
    {
        $result = parent::findFirst($parameters);

        if ($result) {
            $customFields = $result->getAllCustomFields([]);

            if (is_array($customFields)) {
                //field the object
                foreach ($customFields as $key => $value) {
                    $result->{$key} = $value;
                }
            }
        }
        
        return $result;
    }

    /**
     * Create new custom fields.
     *
     * We never update any custom fields, we delete them and create them again, thats why we call cleanCustomFields before updates
     *
     * @return void
     */
    protected function saveCustomFields(): bool
    {
        //find the custom field module
        try {
            $module = Modules::getByCustomeFieldModuleByModuleAndApp(get_class($this), $this->di->getApp());
        } catch (Exception $e) {
            return false;
        }

        //if all is good now lets get the custom fields and save them
        foreach ($this->customFields as $key => $value) {
            //create a new obj per itration to se can save new info
            $customModel = $this->getCustomFieldModel();
            //validate the custome field by it model
            if ($customField = CustomFields::findFirst(['conditions' => 'name = ?0 and custom_fields_modules_id = ?1', 'bind' => [$key, $module->getId()]])) {
                //throw new Exception("this custom field doesnt exist");

                $customModel->setCustomId($this->getId());
                $customModel->custom_fields_id = $customField->getId();
                $customModel->value = $value;
                $customModel->created_at = date('Y-m-d H:i:s');

                if (!$customModel->save()) {
                    throw new Exception('Custome ' . $key . ' - ' . $this->customModel->getMessages()[0]);
                }

                //overwrite the values to return the object
                $this->{$key} = $value;
            }
        }

        return true;
    }

    /**
     * Remove all the custom fields from the entity.
     *
     * @param  int $id
     * @return \Phalcon\MVC\Models
     */
    public function cleanCustomFields(int $id): bool
    {
        $customModel = $this->getCustomFieldModel();

        //return $customModel->find(['conditions' => $this->getSource() . '_id = ?0', 'bind' => [$id]])->delete();
        //we need to run the query since we dont have primary key
        $result = $this->getReadConnection()->prepare("DELETE FROM {$customModel->getSource()} WHERE " . $this->getSource() . '_id = ?');
        return $result->execute([$id]);
    }

    /**
     * Given this object get its custome field table.
     *
     * @return string
     */
    public function getModelCustomFieldClassName() : string
    {
        $reflector = new ReflectionClass($this);
        $classNameWithNameSpace = $reflector->getNamespaceName() . '\\' . $reflector->getShortName() . 'CustomFields';

        return $classNameWithNameSpace;
    }

    /**
     * Given this object get its custome field Module.
     *
     * @return BakaModel
     */
    public function getCustomFieldModel() : BakaModel
    {
        $customFieldModuleName = $this->getModelCustomFieldClassName();

        return new $customFieldModuleName();
    }

    /**
     * Before create.
     *
     * @return void
     */
    public function beforeCreate()
    {
        if (empty($this->customFields)) {
            throw new Exception('This is a custom field module, which means it needs its custom field values in order to work, please call setCustomFields');
        }

        parent::beforeCreate();
    }

    /**
     * Before update.
     *
     * @return void
     */
    public function beforeUpdate()
    {
        parent::beforeUpdate();
    }

    /**
     * Set the custom field to update a custom field module.
     *
     * @param array $fields [description]
     */
    public function setCustomFields(array $fields)
    {
        $this->customFields = $fields;
    }

    /**
     * After the module was created we need to add it custom fields.
     *
     * @return  void
     */
    public function afterCreate()
    {
        $this->saveCustomFields();
        unset($this->customFields);
    }

    /**
     * After the model was update we need to update its custom fields.
     *
     * @return void
     */
    public function afterUpdate()
    {
        //only clean and change custom fields if they have been set
        if (!empty($this->customFields)) {
            //replace old custom with new
            $allCustomFields = $this->getAllCustomFields([]);
            if (is_array($allCustomFields)) {
                foreach ($this->customFields as $key => $value) {
                    $allCustomFields[$key] = $value;
                }
            }

            if (!empty($allCustomFields)) {
                //set
                $this->setCustomFields($allCustomFields);
                //clean old
                $this->cleanCustomFields($this->getId());
                //save new
                $this->saveCustomFields();
                //unset($this->customFields);
            }
        }
    }

    /**
     * After delete remove the custom fields.
     *
     * @return void
     */
    public function afterDelete()
    {
        $this->cleanCustomFields($this->getId());
    }
}
