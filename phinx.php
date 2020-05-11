<?php

(new Dotenv\Dotenv(__DIR__))->load();

return [
    'paths' => [
        'migrations' => [
            'storage/db/migrations',
        ],
        'seeds' => [
            'storage/db/seeds',
        ]
    ],
    'environments' => [
        'default_migration_table' => 'ut_migrations',
        'default_database' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => getenv('DATA_API_MYSQL_HOST'),
            'name' => getenv('DATA_API_MYSQL_NAME'),
            'user' => getenv('DATA_API_MYSQL_USER'),
            'pass' => getenv('DATA_API_MYSQL_PASS'),
            'port' => 3306,
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV['DATA_API_MYSQL_HOST'],
            'name' => $_ENV['DATA_API_MYSQL_NAME'],
            'user' => $_ENV['DATA_API_MYSQL_USER'],
            'pass' => $_ENV['DATA_API_MYSQL_PASS'],
            'port' => 3306,
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
