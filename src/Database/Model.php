<?php

namespace Baka\Database;

use Baka\Contracts\Database\ModelInterface;
use Baka\Database\Exception\ModelNotFoundException;
use Baka\Database\Exception\ModelNotProcessedException;
use function Baka\getShortClassName;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface as PhalconModelInterface;
use RuntimeException;

class Model extends PhalconModel implements ModelInterface, PhalconModelInterface
{
    /**
     * Define a model alias to throw exception msg to the end user.
     *
     * @var ?string
     */
    protected static ?string $modelNameAlias = null;

    /**
     * @var mixed
     */
    public $id;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?int $is_deleted = 0;

    /**
     * Get the primary id of this model.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * before validate create.
     *
     * @return void
     */
    public function beforeValidationOnCreate()
    {
        $this->beforeCreate();
    }

    /**
     * before validate update.
     *
     * @return void
     */
    public function beforeValidationOnUpdate()
    {
        $this->beforeUpdate();
    }

    /**
     * Before create.
     *
     * @return void
     */
    public function beforeCreate()
    {
        $this->updated_at = $this->created_at = date('Y-m-d H:i:s');
        $this->is_deleted = 0;
    }

    /**
     * Before update.
     *
     * @return void
     */
    public function beforeUpdate()
    {
        //if we are trying to overwrite a existing record
        //sometimes we need to fill up this rows manually
        if (empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        if (empty($this->is_deleted)) {
            $this->is_deleted = 0;
        }

        $this->updated_at = date('Y-m-d H:i:s');
    }

    /**
     * Soft Delete.
     *
     * @return void
     */
    public function softDelete()
    {
        $this->is_deleted = 1;

        return $this->save();
    }

    /**
     * Get by Id or thrown an exception.
     *
     * @param mixed $id
     *
     * @return self
     */
    public static function getByIdOrFail($id) : ModelInterface
    {
        if (property_exists(new static, 'is_deleted')) {
            if ($record = static::findFirst([
                'conditions' => 'id = :id:  and is_deleted = :is_deleted:',
                'bind' => [
                    'id' => $id,
                    'is_deleted' => 0
                ]
            ])) {
                return $record;
            }
        } else {
            if ($record = static::findFirst($id)) {
                return $record;
            }
        }

        throw new ModelNotFoundException(
            getShortClassName(new static) . ' Record not found'
        );
    }

    /**
     * Query the first record that matches the specified conditions.
     *
     * @param array $parameters
     *
     * @return self
     */
    public static function findFirstOrFail($parameters = null) : ModelInterface
    {
        $result = static::findFirst($parameters);
        if (!$result) {
            throw new ModelNotFoundException(
                getShortClassName(new static) . ' Record not found'
            );
        }

        return $result;
    }

    /**
     * Query the first record that matches the specified conditions.
     *
     * @param array $parameters
     *
     * @return self
     */
    public static function findOrFail($parameters = null) : ResultsetInterface
    {
        $results = static::find($parameters);
        if (!$results) {
            throw new ModelNotFoundException(
                getShortClassName(new static) . ' Record not found'
            );
        }

        return $results;
    }

    /**
     * save model or throw an exception.
     *
     * @param null|mixed $data
     * @param null|mixed $whiteList
     */
    public function saveOrFail($data = null, $whiteList = null) : bool
    {
        if (is_array($data)) {
            $this->assign($data, $whiteList);
        }

        if ($savedModel = $this->save()) {
            return $savedModel;
        }

        $this->throwErrorMessages();
    }

    /**
     * update model or throw an exception.
     *
     * @param null|mixed $data
     * @param null|mixed $whiteList
     */
    public function updateOrFail($data = null, $whiteList = null) : bool
    {
        if (is_array($data)) {
            $this->assign($data, $whiteList);
        }

        if ($updatedModel = $this->update()) {
            return $updatedModel;
        }

        $this->throwErrorMessages();
    }

    /**
     * Find or create a new object.
     *
     * @param $parameters
     *
     * @return Model
     */
    public static function findFirstOrCreate($parameters = null, array $fields = []) : ModelInterface
    {
        $model = static::findFirst($parameters);

        if (!$model) {
            $model = new static;
            $model->assign($fields);
            $model->saveOrFail();
        }
        return $model;
    }

    /**
     * Update or create a new object.
     *
     * @param $parameters
     *
     * @return Model
     */
    public static function updateOrCreate($parameters = null, array $fields = []) : ModelInterface
    {
        $model = static::findFirst($parameters);

        if (!$model) {
            $model = new static;
        }

        $model->assign($fields);
        $model->saveOrFail();

        return $model;
    }

    /**
     * Delete the model or throw an exception.
     */
    public function deleteOrFail() : bool
    {
        if (!parent::delete()) {
            $this->throwErrorMessages();
        }

        return true;
    }

    /**
     * Since Phalcon 3, they pass model objet through the toArray function when we call json_encode, that can fuck u up, if you modify the obj
     * so we need a way to convert it to array without loosing all the extra info we add.
     *
     * @return array
     */
    public function toFullArray() : array
    {
        //convert the obj to array in order to convert to json
        $result = get_object_vars($this);

        $modelPhalconProperties = [
            'container',
            'dirtyState',
            'dirtyRelated',
            'errorMessages',
            'modelsManager',
            'modelsMetaData',
            'related',
            'oldSnapshot',
            'skipped',
            'snapshot',
            'transaction',
            'uniqueKey',
            'uniqueParams',
            'uniqueTypes',
            'auditExcludeFields',
            'eventsManager',
            'settingsModel',
            'operationMade'
        ];
        foreach ($result as $key => $value) {
            if (preg_match('#^_#', $key) === 1 || in_array($key, $modelPhalconProperties)) {
                unset($result[$key]);
            }

            if ($value === '0000-00-00 00:00:00') {
                $result[$key] = null;
            }
        }

        //remove properties we add
        unset($result['customFields'], $result['uploadedFiles']);

        return $result;
    }

    /**
     * Get the list of primary keys from the current model.
     *
     * @return array
     */
    public function getPrimaryKeys() : array
    {
        $metaData = $this->di->get('modelsMetadata');
        return $metaData->getPrimaryKeyAttributes($this);
    }

    /**
     * Get get the primary key, if we have more than 1 , use keys.
     *
     * @return array
     */
    public function getPrimaryKey() : string
    {
        $primaryKeys = $this->getPrimaryKeys();

        if (empty($primaryKeys)) {
            throw new RuntimeException('No primary key defined in this Model ' . getShortClassName($this));
        }

        return $primaryKeys[0];
    }

    /**
     * Throws an exception with including all validation messages that were retrieved.
     *
     * @throws ModelNotProcessedException
     */
    protected function throwErrorMessages() : void
    {
        throw new ModelNotProcessedException(
            getShortClassName(new static) . ' ' . current($this->getMessages())->getMessage()
        );
    }

    /**
     * hasProperty.
     *
     * @param  string $property
     *
     * @return bool
     */
    public function hasProperty(string $property) : bool
    {
        $metadata = $this->getModelsMetaData();
        $attributes = $metadata->getAttributes($this);
        return key_exists($property, $attributes);
    }
}
