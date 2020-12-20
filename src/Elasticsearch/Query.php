<?php
declare(strict_types=1);

namespace Baka\Elasticsearch;

use Baka\Contracts\Database\ModelInterface as BakaModelInterface;
use Baka\Elasticsearch\Query\FromClause;
use function Baka\envValue;
use Baka\Exception\Exception;
use GuzzleHttp\Client as GuzzleClient;
use Phalcon\Di;
use Phalcon\Mvc\Model\Query\Builder;
use SplFixedArray;

class Query
{
    public ?BakaModelInterface $model = null;
    protected string $sql;
    protected int $total = 0;

    /**
     * Constructor.
     *
     * @param string $sql
     * @param BakaModelInterface|null $model
     */
    public function __construct(string $sql, ?BakaModelInterface $model = null)
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
        $client = new GuzzleClient([
            'base_uri' => $this->getHost()
        ]);

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

        // Send request.
        $results = json_decode(curl_exec($ch), true);

        if (isset($results['error'])) {
            throw  Exception::create(
                str_replace('SQL', '', $results['error']['reason']) . ' : ' . $results['error']['details'],
                $results
            );
        }

        //set total
        $dataset = isset($results['datarows']) ? $results['datarows'] : $results['hits']['hits'];
        $this->total = count($dataset);

        if (!$this->total) {
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
     * @return array
     */
    private function getResultSet(array $elasticResults) : SplFixedArray
    {
        $results = new SplFixedArray($this->total);
        $i = 0;

        if (!empty($elasticResults)) {
            foreach ($elasticResults as $result) {
                $result = isset($result['_source']) ? $result['_source'] : $result;

                if ($this->model) {
                    $results[$i] = !$this->model->useDocument ? new $this->model($result) : (object) $result;
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
     * @param BakaModelInterface $model
     *
     * @return string
     */
    public static function convertPhlToSql(Builder $builder, BakaModelInterface $model) : string
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
