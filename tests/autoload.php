<?php
// This is global bootstrap for autoloading

/**
 * Setup autoloading.
 */

use function Baka\appPath;
use Dotenv\Dotenv;
use Phalcon\Loader;

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