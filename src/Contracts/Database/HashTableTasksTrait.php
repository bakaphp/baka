<?php

declare(strict_types=1);

namespace Baka\Contracts\Database;

use RuntimeException;

/**
 * Custom field class.
 */
trait HashTableTasksTrait
{
    /**
     * Create a new custom field Module to work with.
     *
     * @param array $params
     *
     * @return void
     */
    public function createModuleAction(string $model)
    {
        if (!class_exists($model)) {
            throw new RuntimeException('No Model Class Name Found ' . $model);
        }

        $model = new $model();

        $table = $model->getSource() . '_settings';

        $sql = '
                CREATE TABLE IF NOT EXISTS  `' . $table . '` (
                    `' . $model->getSource() . '_id` int(10) UNSIGNED NOT NULL,
                    `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    `is_deleted` tinyint(1) DEFAULT 0,
                    PRIMARY KEY (`' . $model->getSource() . '_id`,`name`),
                    UNIQUE KEY `' . $table . '_settings_key` (`' . $model->getSource() . '_id`,`name`),
                    KEY `' . $table . '_name_key` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ';

        echo 'Hash table for Module Created ' . get_class($model);
        return 'Hash table for Module Created  ' . get_class($model);
    }
}
