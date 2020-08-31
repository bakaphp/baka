<?php
declare(strict_types=1);

namespace Baka\Elasticsearch;

use Baka\Contracts\Database\ModelInterface as BakaModelInterface;
use Baka\Elasticsearch\Query\FromClause;
use function Baka\envValue;
use GuzzleHttp\Client as GuzzleClient;
use Iterator;
use Phalcon\Di;
use Phalcon\Mvc\Model\Query\Builder;

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
        return 'http://' . $config->elasticSearch['hosts'][0];
    }

    /**
     * Given a SQL search the elastic indices.
     *
     * @param string $sql
     *
     * @return Iterator
     */
    public function find() : array
    {
        $client = new GuzzleClient([
            'base_uri' => $this->getHost()
        ]);

        // since 6.x+ we need to use POST
        $response = $client->post($this->getDriverUrl(), [
            'body' => json_encode([
                $this->getPostKey() => trim($this->sql)
            ]),
            'headers' => [
                'content-type' => 'application/json',
                'Accept' => 'application/json'
            ],
        ]);

        //get the response in a array
        $results = json_decode(
            $response->getBody()->getContents(),
            true
        );

        //set total
        $this->total = isset($results['total']) ? $results['total'] : $results['hits']['total']['value'];

        if ((isset($results['total']) && $results['total'] == 0) ||
            (isset($results['hits']['total']) && $results['hits']['total']['value'] == 0)
            ) {
            return [];
        }

        return $this->getResultSet($results);
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
     * @param array $results
     *
     * @return array
     */
    private function getResultSet(array $elasticResults) : array
    {
        $elasticResults = isset($elasticResults['datarows']) ? $elasticResults['datarows'] : $elasticResults['hits']['hits'];
        $results = [];
        foreach ($elasticResults as $result) {
            $result = isset($result['_source']) ? $result['_source'] : $result;

            if ($this->model) {
                $results[] = new $this->model($result);
            } else {
                $results[] = $result;
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
