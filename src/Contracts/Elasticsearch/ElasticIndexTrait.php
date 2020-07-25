<?php

namespace Baka\Contracts\Elasticsearch;

use stdClass;

trait ElasticIndexTrait
{
    protected string $text = 'text';
    protected string $integer = 'integer';
    protected string $bigInt = 'long';
    protected array $dateNormal = ['date', 'yyyy-MM-dd'];
    protected array $dateTime = ['date', 'yyyy-MM-dd HH:mm:ss'];
    protected string $decimal = 'float';

    /**
     * Set the Id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    /**
     * Define de structure for this index in elastic search.
     *
     * @return array
     */
    abstract public function structure() : array;

    /**
     * Set the data of the current index.
     *
     * @return stdClass
     */
    abstract public function data() : stdClass;

    /**
     * Given the object of the class we return a array document.
     *
     * @return array
     */
    public function document() : array
    {
        return (array) $this->data();
    }
}
