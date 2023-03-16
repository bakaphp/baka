<?php

/**
 * Setup autoloading.
 */

use Dotenv\Dotenv;
use Phalcon\Loader;
use function Baka\appPath;

require __DIR__.'/../src/functions.php';
require __DIR__ . '/PhalconUnitTestCase.php';

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__) . '/');
}

//load classes
$loader = new Loader();
$loader->registerNamespaces([
    'Baka' => appPath('src/'),
    'Baka\Test' => appPath('tests/'),
    'Baka\Test\Support' => appPath('tests/_support'),
    'Phalcon\Cashier' => appPath('src/Cashier'),
]);

$loader->register();

require appPath('vendor/autoload.php');

$dotenv = Dotenv::createImmutable(appPath());
$dotenv->load();
