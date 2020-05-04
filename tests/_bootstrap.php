<?php
// This is global bootstrap for autoloading

/**
 * Setup autoloading.
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/PhalconUnitTestCase.php';

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__) . '/');
}

//load classes
$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    [
        'Baka' => ROOT_DIR . 'src/',
        'Baka\Test' => ROOT_DIR . 'tests/_support/',
    ]
);

$loader->register();

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();
