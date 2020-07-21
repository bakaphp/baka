<?php

namespace Helper;

use Baka\Auth\Models\Users;
use Baka\TestCase\Phinx;
use Codeception\Module;
use Codeception\TestInterface;
use Phalcon\Config as PhConfig;
use Phalcon\Di\FactoryDefault;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Integration extends Module
{
    /**
     * @var null|PhDI
     */
    protected ?FactoryDefault $diContainer = null;
    protected $savedModels = [];
    protected $savedRecords = [];
    protected $config = ['rollback' => false];

    /**
     * Test initializer.
     */
    public function _before(TestInterface $test)
    {
        FactoryDefault::reset();
        $this->setDi();

        $this->grabDi()->setShared('userProvider', new Users());
    }

    public function _after(TestInterface $test)
    {
    }

    /**
     * Run migration.
     *
     * @param array $settings
     *
     * @return void
     */
    public function _beforeSuite($settings = [])
    {
        Phinx::migrate();
        Phinx::seed();
    }

    /**
     * After all is done.
     *
     * @return void
     */
    public function _afterSuite()
    {
        //Phinx::dropTables();
    }

    /**
     * Set DI.
     *
     * @return void
     */
    public function setDi()
    {
        $this->diContainer = new FactoryDefault();
    }

    /**
     * @return mixed
     */
    public function grabDi()
    {
        return $this->diContainer;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function grabFromDi(string $name)
    {
        return $this->diContainer->get($name);
    }

    /**
     * Returns the relationships that a model has.
     *
     * @param string $class
     *
     * @return array
     */
    public function getModelRelationships(string $class) : array
    {
        /** @var AbstractModel $class */
        $model = new $class();
        $manager = $model->getModelsManager();
        $relationships = $manager->getRelations($class);
        $data = [];
        foreach ($relationships as $relationship) {
            $data[] = [
                $relationship->getType(),
                $relationship->getFields(),
                $relationship->getReferencedModel(),
                $relationship->getReferencedFields(),
                $relationship->getOptions(),
            ];
        }
        return $data;
    }

    /**
     * @param array $configData
     */
    public function haveConfig(array $configData)
    {
        $config = new PhConfig($configData);
        $this->diContainer->set('config', $config);
    }

    /**
     * Create a record for $modelName with fields provided.
     *
     * @param string $modelName
     * @param array  $fields
     *
     * @return mixed
     */
    public function haveRecordWithFields(string $modelName, array $fields = [])
    {
        $record = new $modelName;
        foreach ($fields as $key => $val) {
            $record->set($key, $val);
        }
        $this->savedModels[$modelName] = $fields;
        $result = $record->save();
        $this->assertNotSame(false, $result);
        $this->savedRecords[] = $record;
        return $record;
    }

    /**
     * @param string $name
     * @param mixed  $service
     */
    public function haveService(string $name, $service)
    {
        $this->diContainer->set($name, $service);
    }

    /**
     * @param string $name
     */
    public function removeService(string $name)
    {
        if ($this->diContainer->has($name)) {
            $this->diContainer->remove($name);
        }
    }

    /**
     * Checks that record exists and has provided fields.
     *
     * @param $model
     * @param $by
     * @param $fields
     */
    public function seeRecordSaved($model, $by, $fields)
    {
        $this->savedModels[$model] = array_merge($by, $fields);
        $record = $this->seeRecordFieldsValid(
            $model,
            array_keys($by),
            array_keys($by)
        );
        $this->savedRecords[] = $record;
    }
}
