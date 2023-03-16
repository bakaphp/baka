<?php

declare(strict_types=1);

namespace Baka\Contracts\Elasticsearch;

use Baka\Database\Exception\ModelNotFoundException;
use Baka\Elasticsearch\Models\Documents;
use Baka\Elasticsearch\Query;
use Phalcon\Mvc\Model\Query\Builder;
use function Baka\getShortClassName;

trait ElasticIndexModelTrait
{
    /**
     * With this variable we tell elasticsearch to not analyze string fields in order to allow us
     * to perform wildcard matches.
     *
     * @var bool
     */
    public bool $elasticSearchNotAnalyzed = true;

    /**
     * Allow use to overwrite the behavior for a specific field.
     * in order use wildcard search.
     */
    public array $elasticSearchAnalyzedFields = [];

    /**
     * With this variable we tell elasticsearch to enable sorting text fields.
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/fielddata.html.
     *
     * @var bool
     */
    public bool $elasticSearchTextFieldData = true;

    /**
     * Specify to the elastic result to use stdClass object instead of the class itself.
     */
    public bool $useRawElastic = false;

    /**
     * Current object save to elastic.
     *
     * @param int $maxDepth
     *
     * @return array
     */
    public function saveToElastic(int $maxDepth = 0) : array
    {
        if ($maxDepth === 0) {
            $maxDepth = isset($this->elasticMaxDepth) ? $this->elasticMaxDepth : 3;
        }

        //insert into elastic
        return Documents::add($this, $maxDepth);
    }

    /**
     * Set the use of elastic raw data.
     *
     * @return void
     */
    public function setElasticRawData() : void
    {
        $this->useRawElastic = true;
    }

    /**
     * Set the use of phalcon model in elastic.
     *
     * @return void
     */
    public function setElasticPhalconData() : void
    {
        $this->useRawElastic = false;
    }

    /**
     * Determine if we are using elastic raw data.
     *
     * @return bool
     */
    public function useRawElasticRawData() : bool
    {
        return $this->useRawElastic;
    }

    /**
     * Delete from elastic.
     *
     * @return array
     */
    public function deleteFromElastic() : array
    {
        //insert into elastic
        return Documents::delete($this);
    }

    /**
     * Save to elastic.
     *
     * @return void
     */
    public function afterSave()
    {
        $this->saveToElastic();
    }

    /**
     * Remove from elastic.
     *
     * @return void
     */
    public function afterDelete()
    {
        $this->deleteFromElastic();
    }

    /**
     * Find the first element in elastic indice.
     *
     * @param array $params
     *
     * @throws Exception
     *
     * @return self
     */
    public static function findFirstInElastic(array $params = []) : self
    {
        $params['limit'] = 1;

        $resultSet = self::findInElastic($params);

        return $resultSet[0];
    }

    /**
     * Find in elastic sql.
     *
     * @param array $params
     *
     * @throws Exception
     *
     * @return array
     */
    public static function findInElastic(array $params = []) : array
    {
        $params['models'] = self::class;
        if (!isset($params['columns'])) {
            $params['columns'] = ['*'];
        }

        $model = new self();
        $builder = new Builder($params);
        $sql = Query::convertPhlToSql($builder, $model);

        $resultSets = (new Query($sql, $model))->find();

        if (empty($resultSets)) {
            throw new ModelNotFoundException(
                getShortClassName(new static) . ' Record not found'
            );
        }

        return $resultSets;
    }
}
