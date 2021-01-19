<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Objects;

use Baka\Contracts\Database\ElasticModelInterface;
use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\Query;
use function Baka\getShortClassName;

abstract class Documents implements ElasticModelInterface
{
    public int $id;
    public array $data;
    public ?string $indices = null;

    protected string $text = 'text';
    protected string $integer = 'integer';
    protected string $bigInt = 'long';
    protected array $dateNormal = ['date', 'yyyy-MM-dd'];
    protected array $dateTime = ['date', 'yyyy-MM-dd HH:mm:ss'];
    protected string $decimal = 'float';
    protected array $relations = [];

    /**
     * __construct.
     *
     * @param  array $argv
     *
     * @return void
     */
    public function __construct(array $argv = [])
    {
        foreach ($argv as $key => $value) {
            $this->{$key} = $value;
        }

        $this->initialize();
    }

    public function initialize() : void
    {
    }

    /**
     * addRelation.
     *
     * @param  string $index
     * @param  array $options
     *
     * @return void
     */
    protected function addRelation(string $index, array $options)
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
     * @param  int $id
     * @param  array $data
     *
     * @return void
     */
    public function setData(int $id, array $data) : self
    {
        $this->id = $id;
        $this->data = $data;
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
            'body' => $this->data,
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
     * @param int $id
     *
     * @return self
     */
    public static function getById(int $id) : self
    {
        $params = [
            'index' => (new static())->setData($id, [])->getIndices(),
            'id' => $id
        ];

        $response = Client::getInstance()->get($params);

        return (new static())->setData($id, $response['_source']);
    }

    /**
     * Find by query in this document.
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
