<?php

use Baka\Database\Apps;
use Baka\TestCase\PhalconUnit;
use Elasticsearch\ClientBuilder;
use Phalcon\Di;

class PhalconUnitTestCase extends PhalconUnit
{
    protected function setConfiguration() : void
    {
        $this->config = new \Phalcon\Config([
            'database' => [
                'adapter' => 'Mysql',
                'host' => getenv('DATA_API_MYSQL_HOST'),
                'username' => getenv('DATA_API_MYSQL_USER'),
                'password' => getenv('DATA_API_MYSQL_PASS'),
                'dbname' => getenv('DATA_API_MYSQL_NAME'),
            ],
            'memcache' => [
                'host' => getenv('MEMCACHE_HOST'),
                'port' => getenv('MEMCACHE_PORT'),
            ],
            'elasticSearch' => [
                'hosts' => [getenv('ELASTIC_HOST')], //change to pass array
            ],
        ]);
    }

    /**
     * Setup phalconPHP DI to use for testing components.
     *
     * @return Phalcon\DI
     */
    protected function configureDI() : void
    {
        $config = $this->config;

        $this->di->set('config', function () use ($config) {
            return $config;
        }, true);

        /**
         * Everything needed initialize phalconphp db.
         */
        $this->di->set('modelsManager', function () {
            return new Phalcon\Mvc\Model\Manager();
        }, true);

        $this->di->set('modelsMetadata', function () {
            return new Phalcon\Mvc\Model\Metadata\Memory();
        }, true);

        $this->di->set('app', function () {
            return Apps::findFirst();
        }, true);

        $this->di->set('db', function () use ($config) {
            //db connection
            $connection = new Phalcon\Db\Adapter\Pdo\Mysql([
                'host' => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname' => $config->database->dbname,
                'charset' => 'utf8',
            ]);

            return $connection;
        });

        $this->di->set('elastic', function () use ($config) {
            $hosts = $config->elasticSearch->hosts->toArray();

            $client = ClientBuilder::create()
                                    ->setHosts($hosts)
                                    ->build();

            return $client;
        });
    }
}
