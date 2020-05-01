<?php
// This is global bootstrap for autoloading

/**
 * Setup autoloading.
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/unit/PhalconUnitTestCase.php';

if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__) . '/tests/unit/view');
}

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();
