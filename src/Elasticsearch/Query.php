<?php
declare(strict_types=1);

namespace Baka\Elasticsearch;

use Baka\Contracts\Database\ElasticModelInterface;
use Baka\Elasticsearch\Query\FromClause;
use function Baka\envValue;
use Baka\Exception\Exception;
use Baka\Exception\HttpException;
use Phalcon\Di;
use Phalcon\Mvc\Model\Query\Builder;
use SplFixedArray;

class Query
{
    public ?ElasticModelInterface $model = null;
    protected string $sql;
    protected int $totalResultSet = 0;
    protected int $total = 0;

    /**
     * Constructor.
     *
     * @param string $sql
     * @param ElasticModelInterface|null $model
     */
    public function __construct(string $sql, ?ElasticModelInterface $model = null)
    {
        $this->sql = $sql;
        $this->model = $model;
    }

    /**
     * Get elastic host.
     *
     * @return string
     */
    public function getHost() : string
    {
        $config = Di::getDefault()->get('config');
        $host = 'http://' . $config->elasticSearch['hosts'][0];
        return filter_var($config->elasticSearch['hosts'][0], FILTER_VALIDATE_URL) ? $config->elasticSearch['hosts'][0] : $host;
    }

    /**
     * Given a SQL search the elastic indices.
     *
     * @param string $sql
     *
     * @return array
     */
    public function find() : array
    {
        if (envValue('ELASTIC_SEARCH_QUERY_DEBUG', false)) {
            Di::getDefault()->get('log')->info('ELASTICSQL', [$this->sql]);
        }

        $ch = curl_init();
        $payload = [
            $this->getPostKey() => trim($this->sql)
        ];

        curl_setopt($ch, CURLOPT_URL, $this->getHost() . $this->getDriverUrl());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $results = curl_exec($ch);
        if (!$results) {
            $message = 'Elastic error: ' . curl_error($ch);
            throw new HttpException($message, 500);
        }

        // Send request.
        $results = json_decode($results, true);

        if (isset($results['error'])) {
            throw  Exception::create(
                str_replace('SQL', '', $results['error']['reason']) . ' : ' . $results['error']['details'],
                $results
            );
        }

        //set total
        $dataset = isset($results['datarows']) ? $results['datarows'] : $results['hits']['hits'];
        $this->totalResultSet = count($dataset);
        $this->total = $results['hits']['total']['value'];

        if (!$this->totalResultSet) {
            return [];
        }

        return $this->getResultSet($dataset)->toArray();
    }

    /**
     * Reading the env variables determine
     * the POST host URl.
     *
     * @return string
     */
    protected function getDriverUrl() : string
    {
        switch (envValue('ELASTIC_DRIVE', 'opendistro')) {
            case 'opendistro':
                $url = '/_opendistro/_sql?format=json';
                break;
            default:
                $url = '/_nlpcn/sql';
                break;
        }

        return $url;
    }

    /**
     * Given the driver config , determine the post Key.
     *
     * @return string
     */
    protected function getPostKey() : string
    {
        switch (envValue('ELASTIC_DRIVE', 'opendistro')) {
            case 'opendistro':
                $key = 'query';
                break;
            default:
                $key = 'sql';
                break;
        }

        return $key;
    }

    /**
     * Given the elastic results, return only the data.
     *
     * @param array $elasticResults
     *
     * @return SplFixedArray
     */
    private function getResultSet(array $elasticResults) : SplFixedArray
    {
        $results = new SplFixedArray($this->totalResultSet);
        $i = 0;

        if (!empty($elasticResults)) {
            foreach ($elasticResults as $result) {
                $result = isset($result['_source']) ? $result['_source'] : $result;

                if ($this->model) {
                    $results[$i] = !$this->model->useRawElasticRawData() ? new $this->model($result) : (object) $result;
                } else {
                    $results[$i] = $result;
                }

                $i++;
            }
        }
        return $results;
    }

    /**
     * From the current result set get the total count.
     *
     * @return int
     */
    public function getTotal() : int
    {
        return $this->total;
    }

    /**
     * Convert Phalcon SQL To Elastic SQL.
     *
     * @param Builder $builder
     * @param ElasticModelInterface $model
     *
     * @return string
     */
    public static function convertPhlToSql(Builder $builder, ElasticModelInterface $model) : string
    {
        $fromClause = new FromClause($model, $builder->getPhql());
        $fromClauseParser = $fromClause->get();

        $sql = $builder->getPhql();
        $from = $fromClause->getFromString();

        if (!empty($fromClauseParser)) {
            $from .= implode(' , ', $fromClauseParser['nodes']);
            $sql = str_replace($fromClauseParser['searchNodes'], $fromClauseParser['replaceNodes'], $sql);
        }

        $sql = str_replace('[' . get_class($model) . ']', $from, $sql);

        if (!empty($builder->getBindParams())) {
            foreach ($builder->getBindParams() as $key => $value) {
                $sql = str_replace(":{$key}:", $value, $sql);
            }
        }

        return $sql;
    }
}
