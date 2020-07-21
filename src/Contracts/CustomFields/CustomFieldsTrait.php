<?php

namespace Baka\Contracts\CustomFields;

use Baka\Auth\UserProvider;
use Baka\Database\CustomFields\AppsCustomFields;
use Baka\Database\CustomFields\CustomFields;
use Baka\Database\CustomFields\CustomFieldsModules;
use Baka\Database\CustomFields\Modules;
use Baka\Database\Model;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Utils\Slug;

/**
 * Custom field class.
 */
trait CustomFieldsTrait
{
    public array $customFields = [];

    /**
     * Get the custom field primary key
     * for faster access via redis.
     *
     * @return string
     */
    public function getCustomFieldPrimaryKey() : string
    {
        return Slug::generate(get_class($this) . ' ' . $this->getId());
    }

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
     * @return  array
     */
    public function getAllCustomFields() : array
    {
        return $this->getAll();
    }

    /**
     * Get all the custom fields.
     *
     * @return array
     */
    public function getAll() : array
    {
        if (!empty($listOfCustomFields = $this->getAllFromRedis())) {
            return $listOfCustomFields;
        }

        $companyId = $this->companies_id ?? 0;

        $result = $this->getReadConnection()->prepare('
            SELECT name, value 
                FROM apps_custom_fields
                WHERE
                    companies_id = ?
                    AND model_name = ?
                    AND entity_id = ?
        ');

        $result->execute([
            $companyId,
            get_class($this),
            $this->getId()
        ]);

        $listOfCustomFields = [];

        while ($row = $result->fetch()) {
            $listOfCustomFields[$row['name']] = $row['value'];
        }

        return $listOfCustomFields;
    }

    /**
     * Get all the custom fields from redis.
     *
     * @return array
     */
    public function getAllFromRedis() : array
    {
        //use redis to speed things up
        if (Di::getDefault()->has('redis')) {
            $redis = Di::getDefault()->get('redis');

            return $redis->hGetAll(
                $this->getCustomFieldPrimaryKey(),
            );
        }

        return [];
    }

    /**
     * Get the Custom Field.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if ($value = $this->getFromRedis($name)) {
            return $value;
        }

        $field = $this->getCustomField($name);

        return $field ? $field->value : null;
    }

    /**
     * Delete key from custom Fields.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function del(string $name) : bool
    {
        if ($field = $this->getCustomField($name)) {
            $field->delete();

            if (Di::getDefault()->has('redis')) {
                $redis = Di::getDefault()->get('redis');

                $redis->hDel(
                    $this->getCustomFieldPrimaryKey(),
                    $name
                );
            }
        }

        return true;
    }

    /**
     * Get a Custom Field.
     *
     * @param string $name
     *
     * @return ModelInterface|null
     */
    public function getCustomField(string $name) : ?ModelInterface
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
     * Get custom field from redis.
     *
     * @param string $name
     *
     * @return void
     */
    protected function getFromRedis(string $name)
    {
        //use redis to speed things up
        if (Di::getDefault()->has('redis')) {
            $redis = Di::getDefault()->get('redis');

            return $redis->hGet(
                $this->getCustomFieldPrimaryKey(),
                $name
            );
        }

        return false;
    }

    /**
     * Set value.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return ModelInterface
     */
    public function set(string $name, $value)
    {
        $companyId = $this->companies_id ?? 0;

        $this->setInRedis($name, $value);

        $this->createCustomField($name);

        return AppsCustomFields::updateOrCreate([
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
            'label' => $name,
            'name' => $name,
            'value' => $value
        ]);
    }

    /**
     * Create a new Custom Fields.
     *
     * @param string $name
     *
     * @return CustomFields
     */
    public function createCustomField(string $name) : CustomFields
    {
        $di = Di::getDefault();
        $appsId = $di->has('app') ? $di->get('app')->getId() : 0;
        $companiesId =  $di->has('userData') ? UserProvider::get()->currentCompanyId() : 0;
        $textField = 1;
        $cacheKey = Slug::generate(get_class($this) . '-' . $appsId . '-' . $name);
        $lifetime = 604800;

        $customFieldModules = CustomFieldsModules::findFirstOrCreate([
            'conditions' => 'model_name = :model_name: AND apps_id = :apps_id:',
            'bind' => [
                'model_name' => get_class($this),
                'apps_id' => $appsId
            ],
            'cache' => [
                'lifetime' => $lifetime,
                'key' => $cacheKey
            ]], [
                'model_name' => get_class($this),
                'companies_id' => $companiesId,
                'name' => get_class($this),
                'apps_id' => $appsId
            ]);

        $customField = CustomFields::findFirstOrCreate([
            'conditions' => 'apps_id = :apps_id: AND name = :name: AND custom_fields_modules_id = :custom_fields_modules_id:',
            'bind' => [
                'apps_id' => $appsId,
                'name' => $name,
                'custom_fields_modules_id' => $customFieldModules->getId(),
            ],
            'cache' => [
                'lifetime' => $lifetime,
                'key' => $cacheKey . $customFieldModules->getId()
            ]], [
                'users_id' => $di->has('userData') ? UserProvider::get()->getId() : 0,
                'companies_id' => $companiesId,
                'apps_id' => $appsId,
                'name' => $name,
                'label' => $name,
                'custom_fields_modules_id' => $customFieldModules->getId(),
                'fields_type_id' => $textField,
            ]);

        return $customField;
    }

    /**
     * Set custom field in redis.
     *
     * @param string $name
     * @param [type] $value
     *
     * @return boolean
     */
    protected function setInRedis(string $name, $value) : bool
    {
        if (Di::getDefault()->has('redis')) {
            $redis = Di::getDefault()->get('redis');

            return $redis->hSet(
                $this->getCustomFieldPrimaryKey(),
                $name,
                $value
            );
        }

        return false;
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
        if ($this->hasCustomFields()) {
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
     * @return bool
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

    /**
     * Does this model have custom fields?
     *
     * @return bool
     */
    public function hasCustomFields() : bool
    {
        return !empty($this->customFields);
    }

    /**
     * Overwrite toArray , to add custom fields value.
     *
     * @param mixed $columns
     *
     * @return array
     */
    public function toArray($columns = null) : array
    {
        return array_merge(
            parent::toArray($columns),
            $this->getAll()
        );
    }

    /**
     * If something happened to redis
     * And we need to re insert all the custom fields
     * for this entity , we run this method.
     *
     * @return void
     */
    public function reCacheCustomFields() : void
    {
        foreach ($this->getAll() as $key => $value) {
            $this->setInRedis($key, $value);
        }
    }
}
