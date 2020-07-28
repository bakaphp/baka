<?php

namespace Baka\Contracts\Elasticsearch;

use Baka\Elasticsearch\Models\Documents;

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
     * @var boolean
     */
    public bool $elasticSearchNotAnalyzed = true;

    /**
     * Current object save to elastic.
     *
     * @param int $maxDepth
     *
     * @return array
     */
    public function saveToElastic(int $maxDepth = 1) : array
    {
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
}
