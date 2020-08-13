<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Objects;

use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\IndexBuilderStructure;

class Indices
{
    /**
     * Check if the index exist.
     *
     * @param string $model
     *
     * @return void
     */
    public static function exist(string $name) : bool
    {
        return Client::getInstance()->indices()->exists(['index' => strtolower($name)]);
    }

    /**
     * Delete a indices.
     *
     * @param string $name
     *
     * @return array
     */
    public static function delete(string $name) : array
    {
        return Client::getInstance()->indices()->delete(['index' => strtolower($name)]);
    }

    /**
     * Create an index for a model.
     *
     * @param string $model
     * @param int $maxDepth
     *
     * @return array
     */
    public static function create(Documents $document, int $maxDepth = 3, int $nestedLimit = 75) : array
    {
        // Get the model's table structure.
        $columns = $document->structure();

        // Set the model variable for use as a key.
        $index = $document->getIndices();

        // Define the initial parameters that will be sent to Elasticsearch.
        $params = [
            'index' => $index,
            'body' => [
                'settings' => IndexBuilderStructure::getIndicesSettings($nestedLimit),
                'mappings' => [
                ],
            ],
        ];

        // Iterate each column to set it in the index definition.
        foreach ($columns as $column => $type) {
            if (is_array($type) && isset($type[0])) {
                // Remember we used an array to define the types for dates. This is the only case for now.
                $params['body']['mappings']['properties'][$column] = [
                    'type' => $type[0],
                    'format' => $type[1],
                ];
            } elseif (!is_array($type)) {
                $params['body']['mappings']['properties'][$column] = ['type' => $type];

                if ($type == 'string') {
                    $params['body']['mappings']['properties'][$column]['analyzer'] = 'lowercase';
                }
            } else {
                //nested
                IndexBuilderStructure::mapNestedProperties($params['body']['mappings']['properties'], $column, $type);
            }
        }

        if (self::exist($index)) {
            self::delete($index);
        }

        return Client::getInstance()->indices()->create($params);
    }
}
