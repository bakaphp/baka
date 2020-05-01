<?php

use \Phalcon\Di;
use \Phalcon\Test\UnitTestCase as PhalconTestCase;

abstract class PhalconUnitTestCase extends PhalconTestCase
{
    /**
     * @var \Voice\Cache
     */
    protected $_cache;

    /**
     * @var \Phalcon\Config
     */
    protected $_config;

    /**
     * @var bool
     */
    private $_loaded = false;

    /**
     * Setup phalconPHP DI to use for testing components
     *
     * @return Phalcon\DI
     */
    protected function _getDI()
    {
        Phalcon\DI::reset();

        $di = new Phalcon\DI();

        /**
         * DB Config
         * @var array
         */
        $this->_config = new \Phalcon\Config([
            'database' => [
                'adapter' => 'Mysql',
                'host' => getenv('DATABASE_HOST'),
                'username' => getenv('DATABASE_USER'),
                'password' => getenv('DATABASE_PASS'),
                'dbname' => getenv('DATABASE_NAME'),
            ],
            'memcache' => [
                'host' => getenv('MEMCACHE_HOST'),
                'port' => getenv('MEMCACHE_PORT'),
            ],
        ]);

        $config = $this->_config;

        /**
         * Everything needed initialize phalconphp db
         */
        $di->set('modelsManager', function () {
            return new Phalcon\Mvc\Model\Manager();
        }, true);

        $di->set('modelsMetadata', function () {
            return new Phalcon\Mvc\Model\Metadata\Memory();
        }, true);

        $di->set('db', function () use ($config, $di) {

            //db connection
            $connection = new Phalcon\Db\Adapter\Pdo\Mysql(array(
                'host' => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname' => $config->database->dbname,
                'charset' => 'utf8',
            ));

            return $connection;
        });

        /**
         * Start the session the first time some component request the session service
         */
        $di->set('session', function () use ($config) {
            $memcache = new \Phalcon\Session\Adapter\Memcache(array(
                'host' => $config->memcache->host, // mandatory
                'post' => $config->memcache->port, // optional (standard: 11211)
                'lifetime' => 8600, // optional (standard: 8600)
                'prefix' => 'naruhodo', // optional (standard: [empty_string]), means memcache key is my-app_31231jkfsdfdsfds3
                'persistent' => false, // optional (standard: false)
            ));

            //only start the session if its not already started
            if (!isset($_SESSION)) {
                $memcache->start();
            }

            return $memcache;

        });

        return $di;
    }
}
