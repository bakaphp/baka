<?php
declare(strict_types=1);

namespace Baka\Elasticsearch;

use Elasticsearch\Client as ElasticClient;
use Elasticsearch\ClientBuilder;
use Exception;
use Phalcon\Di;

class Client
{
    private static ?ElasticClient $instance = null;

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

            if (empty(current($config['hosts']->toArray()))) {
                return ClientBuilder::create()->setElasticCloudId($config['cloudId'])->setApiKey($config['cloudApiKeyId'],$config['cloudApiKey'])->build();
            }

            return ClientBuilder::create()->setHosts($config['hosts']->toArray())->build();
        }

        return self::$instance;
    }
}
