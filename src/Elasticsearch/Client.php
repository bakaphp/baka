<?php

namespace Baka\Elasticsearch;

use function Baka\envValue;
use Elasticsearch\Client as ElasticClient;
use Elasticsearch\ClientBuilder;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Iterator;
use Phalcon\Di;

class Client
{
    private string $host;
    private static ?ElasticClient $instance = null;

    /**
     * Set the host.
     *
     * @param string $host
     *
     * @return void
     */
    public function __construct(string $host)
    {
        $this->host = $host;
    }

    /**
     * Get elastic Cluster Client.
     *
     * @return ElasticClient
     */
    public static function getInstance() : ElasticClient
    {
        if (self::$instance === null) {
            // Get the DI and set it to a property.
            $di = Di::getDefault();

            // Load the config through the DI.
            if (!$di->has('config')) {
                throw new Exception('Please add your configuration as a service (`config`).');
            }

            // Load the config through the DI.
            if (!$config = $di->get('config')->get('elasticSearch')) {
                throw new Exception('Please add the elasticSearch configuration.');
            }

            // Instance the Elasticsearch client.
            self::$instance = ClientBuilder::create()->setHosts($config['hosts']->toArray())->build();
        }

        return self::$instance;
    }

    /**
     * Given a SQL search the elastic indices.
     *
     * @param string $sql
     *
     * @return Iterator
     */
    public function findBySql(string $sql) : Iterator
    {
        $client = new GuzzleClient([
            'base_uri' => $this->host,
        ]);

        // since 6.x+ we need to use POST
        $response = $client->post($this->getDriverUrl(), [
            $this->getPostKey() => trim($sql),
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

        if ($results['hits']['total'] == 0) {
            yield [];
        }

        return $this->getResults($results);
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
                $url = '/_opendistro/_sql';
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
        switch (getenv('ELASTIC_DRIVE')) {
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
    private function getResults(array $results) : Iterator
    {
        foreach ($results['hits']['hits'] as $result) {
            yield $result['_source'];
        }
    }
}
