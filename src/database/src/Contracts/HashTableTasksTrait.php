<?php

namespace Baka\Database\Contracts;

use Baka\Database\CustomFields\Modules;
use Baka\Database\Apps;

/**
 * Custom field class.
 */
trait HashTableTasksTrait
{
    /**
     * Create a new custom field Module to work with.
     *
     * @param array $params
     * @return void
     */
    public function createModuleAction(array $params)
    {
        if (count($params) != 1) {
            echo 'We are expecting name and the model name to create its settings Module registration' . PHP_EOL;
            return;
        }

        $model = $params[0];

        $model = new $model();

        $table = $model->getSource() . '_settings';

        $sql = '
                CREATE TABLE `' . $table . '` (
                    `' . $model->getSource() . '_id` int(10) UNSIGNED NOT NULL,
                    `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    `is_deleted` tinyint(1) DEFAULT 0,
                    PRIMARY KEY (`' . $model->getSource() . '_id`,`name`),
                    UNIQUE KEY `' . $table . '_settings_key` (`' . $model->getSource() . '_id`,`name`),
                    KEY `' . $table . '_name_key` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ';

        if ($this->getDI()->getDb()->query($sql)) {
            /**
             * @todo create module
             */
        }

        echo 'Hash table for Module Created ' . get_class($model);
        return 'Hash table for Module Created  ' . get_class($model);
    }
}
