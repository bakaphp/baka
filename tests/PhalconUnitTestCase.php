<?php

use Baka\Contracts\Elasticsearch\IndexBuilderTaskTrait;
use Baka\Database\Apps;
use function Baka\envValue;
use Baka\Test\Support\Models\Leads;
use Baka\TestCase\PhalconUnit;
use Elasticsearch\ClientBuilder;
use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Di;
use Phalcon\Http\Response;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\View\Simple;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class PhalconUnitTestCase extends PhalconUnit
{
    use IndexBuilderTaskTrait;

    /**
     * Set configuration.
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
            'namespace' => [
                'models' => 'Baka\Test\Support\Models',
                'elasticIndex' => 'Baka\Test\Support\ElasticModel\Indices',
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
            'stripe' => [
                'secretKey' => getenv('STRIPE_SECRET'),
                'secret' => getenv('STRIPE_SECRET'),
                'public' => getenv('STRIPE_PUBLIC'),
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
     * Setup phalconPHP DI.
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

        $this->di->setShared('response', function () {
            return new Response();
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

        $this->di->setShared('modelsCache', function () {
            // Cache data for one day (default setting)
            $serializerFactory = new SerializerFactory();
            $adapterFactory = new AdapterFactory($serializerFactory);

            $options = [
                'defaultSerializer' => 'php',
                'lifetime' => 7200
            ];

            $adapter = $adapterFactory->newInstance('memory', $options);

            return new Cache($adapter);
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

        /**
         * Start the session the first time some component request the session service.
         */
        $this->di->setShared('session', function () use ($config) {
            $session = new Redis(
                [
                    'uniqueId' => uniqid(),
                    'host' => envValue('REDIS_HOST', '127.0.0.1'),
                    'port' => (int) envValue('REDIS_PORT', 6379),
                    'persistent' => false,
                    'lifetime' => 3600,
                    'prefix' => 'session',
                ]
            );

            $session->start();

            return $session;
        });

        $this->di->setShared('redis', function () {
            $redis = new \Redis();
            $redis->connect(envValue('REDIS_HOST', 'redis'));
            $serializeEngine = !extension_loaded('igbinary') ? \Redis::SERIALIZER_PHP : \Redis::SERIALIZER_IGBINARY;
            $redis->setOption(\Redis::OPT_SERIALIZER, $serializeEngine);
            return $redis;
        });
    }

    /**
     * this runs before everyone.
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->createIndexAction(Leads::class, 2);

        $this->createDocumentsAction(Leads::class, 2);
    }
}
