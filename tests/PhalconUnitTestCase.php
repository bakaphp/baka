<?php

use Baka\Database\Apps;
use Baka\TestCase\PhalconUnit;
use Elasticsearch\ClientBuilder;
use Phalcon\Di;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Phalcon\Mvc\View\Simple;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

use function Baka\envValue;

class PhalconUnitTestCase extends PhalconUnit
{
    /**
     * Set configuration
     *
     * @return void
     */
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
            'application' => [ //@todo migration to app
                'production' => getenv('PRODUCTION'),
                'debug' => [
                    'profile' => getenv('DEBUG_PROFILE'),
                    'logQueries' => getenv('DEBUG_QUERY'),
                    'logRequest' => getenv('DEBUG_REQUEST')
                ],
            ],
            'email' => [
                'driver' => 'smtp',
                'host' => getenv('EMAIL_HOST'),
                'port' => getenv('EMAIL_PORT'),
                'username' => getenv('EMAIL_USER'),
                'password' => getenv('EMAIL_PASS'),
                'from' => [
                    'email' => 'email@bakaphp.com',
                    'name' => 'YOUR FROM NAME',
                ],
                'debug' => [
                    'from' => [
                        'email' => 'debug@bakaphp.com',
                        'name' => 'YOUR FROM NAME',
                    ],
                ],
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
     * Setup phalconPHP DI
     *
     * @return void
     */
    protected function configureDI() : void
    {
        $config = $this->config;

        $this->di->setShared('config', function () use ($config) {
            return $config;
        });

        $this->di->setShared('mail', function () use ($config) {
            //setup
            $mailer = new \Baka\Mail\Manager($config->email->toArray());

            return $mailer->createMessage();
        });

        $this->di->setShared(
            'queue',
            function () {
                //Connect to the queue
                $queue = new AMQPStreamConnection(
                    envValue('RABBITMQ_HOST', 'localhost'),
                    envValue('RABBITMQ_PORT', 5672),
                    envValue('RABBITMQ_DEFAULT_USER', 'guest'),
                    envValue('RABBITMQ_DEFAULT_PASS', 'guest'),
                    envValue('RABBITMQ_DEFAULT_VHOST', '/')
                );

                return $queue;
            }
        );

        /**
         * Everything needed initialize phalconphp db.
         */
        $this->di->setShared('modelsManager', function () {
            return new Phalcon\Mvc\Model\Manager();
        });

        $this->di->setShared('modelsMetadata', function () {
            return new Phalcon\Mvc\Model\Metadata\Memory();
        });

        $this->di->setShared('app', function () {
            return Apps::findFirst();
        });

        $this->di->setShared('db', function () use ($config) {
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

        $this->di->setShared('elastic', function () use ($config) {
            $hosts = $config->elasticSearch->hosts->toArray();

            $client = ClientBuilder::create()
                                    ->setHosts($hosts)
                                    ->build();

            return $client;
        });

        $this->di->setShared('view', function () use ($config) {
            $view = new Simple();
            $view->setViewsDir(realpath(dirname(__FILE__)) . '/view/');

            $view->registerEngines([
                '.volt' => function ($view, $di) use ($config) {
                    $volt = new VoltEngine($view, $di);

                    $volt->setOptions([
                        'compiledPath' => realpath(dirname(__FILE__)) . '/view/cache/',
                        'compiledSeparator' => '_',
                        //since production is true or false, and we inverse the value to be false in production true in debug
                        'compileAlways' => true,
                    ]);

                    return $volt;
                },
                '.php' => function ($view, $di) {
                    return new \Phalcon\Mvc\View\Engine\Php($view, $di);
                },
            ]);

            return $view;
        });
    }
}
