<?php

use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
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
     * Setup phalconPHP DI to use for testing components.
     *
     * @return Phalcon\DI
     */
    protected function _getDI()
    {
        Phalcon\DI::reset();

        $di = new Phalcon\DI();

        /**
         * DB Config.
         * @var array
         */
        $this->_config = new \Phalcon\Config([
            'application' => [ //@todo migration to app
                'production' => getenv('PRODUCTION'),
                'development' => getenv('DEVELOPMENT'),
                'jwtSecurity' => getenv('JWT_SECURITY'),
                'debug' => [
                    'profile' => getenv('DEBUG_PROFILE'),
                    'logQueries' => getenv('DEBUG_QUERY'),
                    'logRequest' => getenv('DEBUG_REQUEST')
                ],
            ],
            'memcache' => [
                'host' => getenv('DATA_API_MEMCACHED_HOST'),
                'port' => getenv('DATA_API_MEMCACHED_PORT'),
            ],
            'email' => [
                'driver' => 'smtp',
                'host' => getenv('DATA_API_EMAIL_HOST'),
                'port' => getenv('DATA_API_EMAIL_PORT'),
                'username' => getenv('DATA_API_EMAIL_USER'),
                'password' => getenv('DATA_API_EMAIL_PASS'),
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
            'beanstalk' => [
                'host' => getenv('DATA_API_BEANSTALK_HOST'),
                'port' => getenv('DATA_API_BEANSTALK_PORT'),
                'prefix' => getenv('DATA_API_BEANSTALK_PREFIX'),
            ]
        ]);

        $config = $this->_config;

        $di->set('config', function () use ($config) {
            //setup
            return $config;
        });

        /**
         * Everything needed initialize phalconphp db.
         */

        $di->set('mail', function () use ($config, $di) {
            //setup
            $mailer = new \Baka\Mail\Manager($config->email->toArray());

            return $mailer->createMessage();
        });

        /**
         * config queue by default Beanstalkd.
         */
        $di->set('queue', function () use ($config) {
            //Connect to the queue
            $queue = new \Phalcon\Queue\Beanstalk\Extended([
                'host' => $config->beanstalk->host,
                'prefix' => $config->beanstalk->prefix,
            ]);

            return $queue;
        });

        $di->set('view', function () use ($config) {
            $view = new \Phalcon\Mvc\View\Simple();
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

        return $di;
    }

    /**
    * this runs before everyone.
    */
    protected function setUp()
    {
        $this->_getDI();
    }

    protected function tearDown()
    {
    }
}
