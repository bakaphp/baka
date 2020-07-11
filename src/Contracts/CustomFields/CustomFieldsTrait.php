<?php

namespace Baka\Contracts\CustomFields;

use Baka\Auth\UserProvider;
use Baka\Database\CustomFields\AppsCustomFields;
use Baka\Database\CustomFields\CustomFields;
use Baka\Database\CustomFields\Modules;
use Baka\Database\Model;
use Phalcon\Mvc\Model\ResultsetInterface;

/**
 * Custom field class.
 */
trait CustomFieldsTrait
{
    public $customFields = [];

    /**
     * Get the custom fields of the current object.
     *
     * @return array
     *
     */
    public function getCustomFields() : array
    {
        if (!$module = Modules::findFirstByModelName(get_class($this))) {
            return [];
        }

        $customFields = CustomFields::findByCustomFieldsModulesId($module->getId());

        foreach ($customFields as $customField) {
            $result[$customField->label ?? $customField->name] = [
                'type' => $customField->type->name,
                'label' => $customField->name,
                'attributes' => $customField->attributes ? json_decode($customField->attributes) : null
            ];
        }

        return $result;
    }

    /**
     * Get all custom fields of the given object.
     *
     * @param  array  $fields
     *
     * @return Phalcon\Mvc\Model
     */
    public function getAllCustomFields()
    {
        return $this->getAll();
    }

    /**
     * Get all the custom fields.
     *
     * @return void
     */
    public function getAll() : ResultsetInterface
    {
        return AppsCustomFields::find([
            'conditions' => 'companies_id = :companies_id:  AND model_name = :model_name: AND entity_id = :entity_id:',
            'bind' => [
                'companies_id' => $this->companies_id,
                'model_name' => get_class($this),
                'entity_id' => $this->getId()
            ]
        ]);
    }

    /**
     * Get the Custom Field.
     *
     * @param string $name
     *
     * @return void
     */
    public function get(string $name)
    {
        return AppsCustomFields::findFirst([
            'conditions' => 'companies_id = :companies_id:  AND model_name = :model_name: AND entity_id = :entity_id: AND name = :name:',
            'bind' => [
                'companies_id' => $this->companies_id,
                'model_name' => get_class($this),
                'entity_id' => $this->getId(),
                'name' => $name,
            ]
        ]);
    }

    /**
     * Set value.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $name, $value)
    {
        $companyId = $this->companies_id ?? 0;

        AppsCustomFields::updateOrCreate([
            'conditions' => 'companies_id = :companies_id:  AND model_name = :model_name: AND entity_id = :entity_id: AND name = :name:',
            'bind' => [
                'companies_id' => $companyId,
                'model_name' => get_class($this),
                'entity_id' => $this->getId(),
                'name' => $name,
            ]
        ], [
            'companies_id' => $companyId,
            'users_id' => UserProvider::get()->getId(),
            'model_name' => get_class($this),
            'entity_id' => $this->getId(),
            'name' => $name,
            'value' => $value
        ]);
    }

    /**
     * Create new custom fields.
     *
     * We never update any custom fields, we delete them and create them again, thats why we call deleteAllCustomFields before updates
     *
     * @return void
     */
    protected function saveCustomFields() : bool
    {
        if (isset($this->customFields) && !empty($this->customFields)) {
            foreach ($this->customFields as $key => $value) {
                if (!property_exists($this, $key)) {
                    $this->set($key, $value);
                }
            }
        }

        unset($this->customFields);
        return true;
    }

    /**
     * Remove all the custom fields from the entity.
     *
     * @param  int $id
     *
     * @return \Phalcon\MVC\Models
     */
    public function deleteAllCustomFields() : bool
    {
        $companyId = $this->companies_id ?? 0;

        $result = $this->getReadConnection()->prepare('DELETE FROM apps_custom_fields WHERE companies_id = ? AND model_name = ? and entity_id = ?');
        return $result->execute([
            $companyId,
            get_class($this),
            $this->getId(),
        ]);
    }

    /**
     * Set the custom field to update a custom field module.
     *
     * @param array $fields
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
            $this->deleteAllCustomFields();
            $this->saveCustomFields();
        }
    }

    /**
     * After delete remove the custom fields.
     *
     * @return void
     */
    public function afterDelete()
    {
        $this->deleteAllCustomFields();
    }
}
