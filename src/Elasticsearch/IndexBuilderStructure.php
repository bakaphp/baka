<?php

namespace Baka\Elasticsearch;

class IndexBuilderStructure extends IndexBuilder
{
    /**
     * Map the nested properties of a index by using recursive calls.
     *
     * @todo we are reusing this code on top so we must find a better way to handle it @kaioken
     *
     * @param array $params
     * @param string $column
     * @param array $columns
     *
     * @return void
     */
    public static function mapNestedProperties(array &$params, string $column, array $columns) : void
    {
        $params[$column] = ['type' => 'nested'];

        foreach ($columns as $innerColumn => $type) {
            // For now this is only being used for date/datetime fields
            if (is_array($type) && isset($type[0])) {
                $params[$column]['properties'][$innerColumn] = [
                    'type' => $type[0],
                    'format' => $type[1],
                ];
            } elseif (!is_array($type)) {
                $params[$column]['properties'][$innerColumn] = ['type' => $type];

                if ($type == 'string') {
                    $params[$column]['properties'][$innerColumn]['analyzer'] = 'lowercase';
                }
            } else {
                //fix issues when nested arrays  contains another array with no fields
                if (!array_key_exists('properties', $params[$column])) {
                    $params[$column]['properties'] = [];
                }
                self::mapNestedProperties($params[$column]['properties'], $innerColumn, $type);
            }
        }
    }
}
