<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Objects;

use Baka\Contracts\Database\ElasticModelInterface;
use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\Query;
use function Baka\getShortClassName;

/**
 * @psalm-consistent-constructor
 */
abstract class Documents implements ElasticModelInterface
{
    public $id;
    public array $data = [];
    public array $dataIndex = [];
    public ?string $indices = null;

    protected string $text = 'text';
    protected string $keyword = 'keyword';
    protected string $integer = 'integer';
    protected string $bigInt = 'long';
    protected string $boolean = 'boolean';
    protected string $object = 'object';
    protected array $dateNormal = ['date', 'yyyy-MM-dd'];
    protected array $dateTime = ['date', 'yyyy-MM-dd HH:mm:ss'];
    protected string $decimal = 'float';
    protected array $relations = [];

    /**
     * __construct.
     *
     * @param $argv
     *
     * @return void
     */
    public function __construct($argv = null)
    {
        if (is_array($argv)) {
            $this->assign($argv);
        }
        $this->initialize();
    }

    /**
     * initialize.
     *
     * @return void
     */
    public function initialize() : void
    {
    }

    /**
     * assign.
     *
     * @param  array $data
     *
     * @return self
     */
    public function assign(array $data) : self
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
            $this->dataIndex[] = $key;
        }
        return $this;
    }

    /**
     * Assign document properties to data when
     * - User assign the document data to each properties
     * - User assign the value via de construct.
     *
     * @return void
     */
    public function assignFromProperties() : void
    {
        if (empty($this->data) && !empty($this->dataIndex)) {
            foreach ($this->dataIndex as $index) {
                $this->data[$index] = $this->{$index};
            }
        } elseif (empty($this->data) && empty($this->dataIndex)) {
            $objectProperties = get_object_vars($this);

            $elasticDocumentProperties = [
                'data',
                'dataIndex',
                'indices',
                'text',
                'keyword',
                'integer',
                'bigInt',
                'dateNormal',
                'dateTime',
                'decimal',
                'relations',
            ];
            foreach ($objectProperties as $key => $value) {
                if (preg_match('#^_#', $key) === 1 || in_array($key, $elasticDocumentProperties) || empty($value)) {
                    unset($objectProperties[$key]);
                }
            }

            $this->data = $objectProperties;
        }
    }

    /**
     * addRelation.
     *
     * @param  string $index
     * @param  array $options
     *
     * @return void
     */
    protected function addRelation(string $index, array $options) : void
    {
        $this->relations[] = new Relation($index, $options);
    }

    /**
     * useRawElasticRawData.
     *
     * @return bool
     */
    public function useRawElasticRawData() : bool
    {
        return false;
    }

    /**
     * getSource.
     *
     * @return string
     */
    public function getSource() : string
    {
        return $this->getIndices();
    }

    /**
     * getRelations.
     *
     * @return array
     */
    public function getRelations() : array
    {
        return $this->relations;
    }

    /**
     * setData.
     *
     * @param $id
     * @param  array $data
     *
     * @return self
     */
    public function setData($id, array $data) : self
    {
        $this->id = $id;
        $this->data = $data;

        return $this;
    }

    /**
     * set data based on model.
     *
     * @param ElasticModelInterface $model
     *
     * @return self
     */
    public function setDataModel(ElasticModelInterface $model) : self
    {
        $this->id = $model->getId();

        return $this;
    }

    /**
     * Set indices.
     *
     * @param string $indices
     *
     * @return void
     */
    public function setIndices(string $indices) : void
    {
        $this->indices = strtolower($indices);
    }

    /**
     * Get Document Indices.
     *
     * @return string
     */
    public function getIndices() : string
    {
        return $this->indices ?? strtolower(getShortClassName($this));
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData() : array
    {
        $this->assignFromProperties();
        return $this->data;
    }

    /**
     * Define de structure for this index in elastic search.
     *
     * @return array
     */
    abstract public function structure() : array;

    /**
     * Add data to the index.
     *
     * @return array
     */
    public function add() : array
    {
        $params = [
            'index' => $this->getIndices(),
            'id' => $this->id,
            'body' => $this->getData(),
        ];

        return Client::getInstance()->index($params);
    }

    /**
     * Update a document.
     *
     * @return array
     */
    public function update() : array
    {
        return $this->add();
    }

    /**
     * Delete document.
     *
     * @return array
     */
    public function delete() : array
    {
        $params = [
            'index' => $this->getIndices(),
            'id' => $this->id,
        ];

        return Client::getInstance()->delete($params);
    }

    /**
     * Get a document by Id.
     *
     * @param mixed $id
     *
     * @return self
     */
    public static function getById($id) : self
    {
        $selfClass = new static();

        $params = [
            'index' => $selfClass->getIndices(),
            'id' => $id
        ];

        $response = Client::getInstance()->get($params);

        return $selfClass->setData($id, $response['_source']);
    }

    /**
     * Find by query in this document.
     *
     * @todo add findFirst and Find() like phalcon
     *
     * @param string $sql
     *
     * @return array
     */
    public static function findBySql(string $sql) : array
    {
        $elasticQuery = new Query($sql);

        return $elasticQuery->find();
    }
}
