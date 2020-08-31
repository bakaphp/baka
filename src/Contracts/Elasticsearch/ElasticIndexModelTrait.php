<?php

declare(strict_types=1);

namespace Baka\Contracts\Elasticsearch;

use Baka\Database\Exception\ModelNotFoundException;
use Baka\Elasticsearch\Models\Documents;
use Baka\Elasticsearch\Query;
use function Baka\getShortClassName;
use Phalcon\Mvc\Model\Query\Builder;

trait ElasticIndexModelTrait
{
    protected int $elasticMaxDepth = 3;

    /**
     * Fields we want to have excluded from the audits.
     *
     * @var array
     */
    public array $auditExcludeFields = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * With this variable we tell elasticsearch to not analyze string fields in order to allow us
     * to perform wildcard matches.
     *
     * @var bool
     */
    public bool $elasticSearchNotAnalyzed = true;

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
            $maxDepth = $this->elasticMaxDepth;
        }

        //insert into elastic
        return Documents::add($this, $maxDepth);
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
