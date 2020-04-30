<?php
return new \Phalcon\Config(
    [
        /**
         * The name of the database, username,password for Baka
         */
        'database' => [
            'mysql' => [
                'host' => 'localhost',
                'username' => 'root',
                'password' => getenv('DATABASE_PASSWORD'),
                'dbname' => 'baka_auth',
                'charset' => 'utf8',
            ]
        ],
        'stripe' => [
            'model' => 'App\Models\Users',
            'secretKey' => getenv('STRIPE_SECRET'),
            'publishKey' => getenv('STRIPE_PUBLIC'),
        ],
        /**
         * Application settings
         */
        'application' => [
            /**
             * The site name, you should change it to your name website
             */
            'name' => 'Baka',
            /**
             * In a few words, explain what this site is about.
             */
            'tagline' => 'A Q&A, Discussion PHP platform',
            'publicUrl' => 'http://Baka.com',
            /**
             * Change URL cdn if you want it
             */
            'development' => [
                'staticBaseUri' => '/',
            ],
            'production' => [
                'staticBaseUri' => '/',
            ],
            /**
             * For developers: Baka debugging mode.
             *
             * Change this to true to enable the display of notices during development.
             * It is strongly recommended that plugin and theme developers use
             * in their development environments.
             */
            'debug' => true
        ],
    ]
);
