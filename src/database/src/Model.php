<?php

namespace Baka\Database;

use Baka\Database\Exception\ModelNotFoundException;
use Baka\Database\Exception\ModelNotProcessedException;
use Phalcon\Mvc\Model\MetaData\Memory as MetaDataMemory;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model as PhalconModel;
use RuntimeException;
use ReflectionClass;

class Model extends PhalconModel
{
    /**
     * Define a model alias to throw exception msg to the end user.
     *
     * @var ?string
     */
    protected static $modelNameAlias = null;

    /**
     * @return int
     */
    public $id;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var string
     */
    public $updated_at;

    /**
     * @var int
     */
    public $is_deleted = 0;

    /**
     * Get the primary id of this model.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = null;
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
     * Get by Id or thrown an exceptoin.
     *
     * @param mixed $id
     * @return self
     */
    public static function getByIdOrFail($id): self
    {
        if ($record = static::findFirst($id)) {
            return $record;
        }

        throw new ModelNotFoundException((new ReflectionClass(new static))->getShortName() . ' Record not found');
    }

    /**
     * Query the first record that matches the specified conditions.
     *
     * @param array $parameters
     * @return self
     */
    public static function findFirstOrFail($parameters = null): self
    {
        $result = static::findFirst($parameters);
        if (!$result) {
            throw new ModelNotFoundException((new ReflectionClass(new static))->getShortName() . ' Record not found');
        }

        return $result;
    }

    /**
     * Query the first record that matches the specified conditions.
     *
     * @param array $parameters
     * @return self
     */
    public static function findOrFail($parameters = null): ResultsetInterface
    {
        $results = static::find($parameters);
        if (!$results) {
            throw new ModelNotFoundException((new ReflectionClass(new static))->getShortName() . ' Record not found');
        }

        return $results;
    }

    /**
    * save model or throw an exception.
    *
    * @param null|mixed $data
    * @param null|mixed $whiteList
    */
    public function saveOrFail($data = null, $whiteList = null): bool
    {
        if ($savedModel = static::save($data, $whiteList)) {
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
    public function updateOrFail($data = null, $whiteList = null): bool
    {
        if ($updatedModel = static::update($data, $whiteList)) {
            return $updatedModel;
        }

        $this->throwErrorMessages();
    }

    /**
    * Delete the model or throw an exception.
    */
    public function deleteOrFail(): bool
    {
        if (!parent::delete()) {
            $this->throwErrorMessages();
        }

        return true;
    }

    /**
     * Since Phalcon 3, they pass model objet throught the toArray function when we call json_encode, that can fuck u up, if you modify the obj
     * so we need a way to convert it to array without loosing all the extra info we add.
     *
     * @return array
     */
    public function toFullArray(): array
    {
        //convert the obj to array in order to conver to json
        $result = get_object_vars($this);

        foreach ($result as $key => $value) {
            if (preg_match('#^_#', $key) === 1) {
                unset($result[$key]);
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
    public function getPrimaryKeys(): array
    {
        $metaData = new MetaDataMemory();
        return $metaData->getPrimaryKeyAttributes($this);
    }

    /**
     * Get get the primarey key, if we have more than 1 , use keys.
     *
     * @return array
     */
    public function getPrimaryKey(): string
    {
        $primaryKeys = $this->getPrimaryKeys();

        if (empty($primaryKeys)) {
            throw new RuntimeException('No primary key defined in this Model ' . self::getModelNameAlias());
        }

        return $primaryKeys[0];
    }

    /**
    * Throws an exception with including all validation messages that were retrieved.
    * @throws ModelNotProcessedException
    */
    protected function throwErrorMessages(): void
    {
        throw new ModelNotProcessedException(
            (new ReflectionClass(new static))->getShortName() . ' ' . current($this->getMessages())->getMessage()
        );
    }

    /**
     * Does this model have custom fields?
     *
     * @return boolean
     */
    public function hasCustomFields(): bool
    {
        return isset($this->customFields);
    }
}
