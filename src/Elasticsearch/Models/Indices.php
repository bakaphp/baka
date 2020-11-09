<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Models;

use Baka\Contracts\Database\ModelInterface;
use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\IndexBuilder;
use Phalcon\Mvc\Model;
use RuntimeException;

class Indices
{
    /**
     * Given a model name class name get it index name.
     *
     * @param string $model
     *
     * @return string
     */
    public static function getName(ModelInterface $model) : string
    {
        return strtolower($model->getSource());
    }

    /**
     * Check if the index exist.
     *
     * @param string $model
     *
     * @return void
     */
    public static function exist(string $model) : bool
    {
        if (!class_exists($model)) {
            throw new RuntimeException($model . ' Model doesn\'t exist ');
        }

        $model = new $model();

        return Client::getInstance()->indices()->exists([
            'index' => self::getName($model)
        ]);
    }

    /**
     * Delete index.
     *
     * @param ModelInterface $model
     *
     * @return bool
     */
    public static function delete(ModelInterface $model) : array
    {
        return Client::getInstance()->indices()->delete([
            'index' => self::getName($model)
        ]);
    }

    /**
     * Create an index for a model.
     *
     * @param string $model
     * @param int $maxDepth
     *
     * @return array
     */
    public static function create(string $modelClassName, int $maxDepth = 3, int $nestedLimit = 75) : array
    {
        $model = new $modelClassName();

        // Get the model's table structure.
        $columns = IndexBuilder::getFieldsTypes($model);

        // Set the model variable for use as a key.
        $index = self::getName($model);
        $modelClass = get_class($model);

        // Define the initial parameters that will be sent to Elasticsearch.
        $params = [
            'index' => $index,
            'body' => [
                'settings' => IndexBuilder::getIndicesSettings($nestedLimit),
                'mappings' => [
                ],
            ],
        ];

        // Iterate each column to set it in the index definition.
        foreach ($columns as $column => $type) {
            if (is_array($type)) {
                // Remember we used an array to define the types for dates. This is the only case for now.
                $params['body']['mappings']['properties'][$column] = [
                    'type' => $type[0],
                    'format' => $type[1],
                ];
            } else {
                $params['body']['mappings']['properties'][$column] = ['type' => $type];

                if (IndexBuilder::useFieldSearchNotAnalyzed($type, $model)) {
                    $params['body']['mappings']['properties'][$column]['analyzer'] = 'lowercase';
                }

                if (IndexBuilder::useFieldSearchTextFieldData($type, $model)) {
                    $params['body']['mappings']['properties'][$column]['fielddata'] = true;
                }
            }
        }

        // Get custom fields... fields.
        IndexBuilder::getCustomParams($params['body']['mappings']['properties'], $modelClass);

        // Call to get the information from related models.
        IndexBuilder::getRelatedParams($params['body']['mappings']['properties'], $modelClass, $modelClass, 0, $maxDepth);

        return  Client::getInstance()->indices()->create($params);
    }

    /**
     * Create if it doesn't exist.
     *
     * @param string $model
     *
     * @return bool|array
     */
    public static function createIfNotExist(string $modelClassName, int $maxDepth = 3, int $nestedLimit = 75)
    {
        if (!self::exist($modelClassName)) {
            return self::create($modelClassName, $maxDepth, $nestedLimit = 75);
        }

        return false;
    }
}
