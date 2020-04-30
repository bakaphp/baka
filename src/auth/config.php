<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => getenv('DATABASE_HOST'),
        'username' => getenv('DATABASE_USER'),
        'password' => getenv('DATABASE_PASS'),
        'dbname' => getenv('DATABASE_NAME'),
    ],
]);
