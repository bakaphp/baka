<?php

namespace Baka\Elasticsearch;

use Baka\Contracts\CustomFields\CustomFieldModelInterface;
use Baka\Database\CustomFields\CustomFields;
use Baka\Elasticsearch\Model as ModelCustomFields;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Phalcon\Db\Column;
use Phalcon\Di;
use Phalcon\Mvc\Model;
use RuntimeException;

class IndexBuilder
{
    protected static ?Di $di = null;
    protected static ?Client $client = null;

    /**
     * Initialize some classes for internal use.
     *
     * @return void
     */
    protected static function initialize()
    {
        // Get the DI and set it to a property.
        self::$di = Di::getDefault();

        // Load the config through the DI.
        if (!self::$di->has('config')) {
            throw new RuntimeException('Please add your configuration as a service (`config`).');
        }

        // Load the config through the DI.
        if (!$config = self::$di->get('config')->get('elasticSearch')) {
            throw new RuntimeException('Please add the elasticSearch configuration.');
        }

        // Check that there is a hosts definition for Elasticsearch.
        if (!$config->has('hosts')) {
            throw new RuntimeException('Please add the hosts definition for elasticSearch.');
        }

        // Instance the Elasticsearch client.
        self::$client = ClientBuilder::create()
            ->setHosts($config->get('hosts')->toArray())
            ->build();
    }

    /**
     * Run checks to avoid unwanted errors.
     *
     * @param string $model
     *
     * @return string
     */
    protected static function checks(string $model) : string
    {
        // Call the initializer.
        self::initialize();

        return $model;
    }

    /**
     * Get the general settings for our predefine indices.
     *
     * @param int $nestedLimit
     *
     * @return array
     */
    protected static function getIndicesSettings(int $nestedLimit) : array
    {
        return [
            'index.mapping.nested_fields.limit' => $nestedLimit,
            'max_result_window' => 50000,
            'analysis' => [
                'analyzer' => [
                    'lowercase' => [
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => ['lowercase'],
                    ],
                ],
            ]
        ];
    }

    /**
     * Check if the index exist.
     *
     * @param string $model
     *
     * @return void
     */
    public static function existIndices(string $model) : bool
    {
        // Run checks to make sure everything is in order.
        $modelPath = self::checks($model);
        $model = strtolower(str_replace(['_', '-'], '', $model));

        return self::$client->indices()->exists(['index' => $model]);
    }

    /**
     * Create an index for a model.
     *
     * @param string $model
     * @param int $maxDepth
     *
     * @return array
     */
    public static function createIndices(string $modelClass, int $maxDepth = 3, int $nestedLimit = 75) : array
    {
        self::initialize();

        // We need to instance the model in order to access some of its properties.
        $modelInstance = new $modelClass();
        $model = $modelInstance->getSource();

        // Get the model's table structure.
        $columns = self::getFieldsTypes($model);
        // Set the model variable for use as a key.
        $model = strtolower(str_replace(['_', '-'], '', $model));

        // Define the initial parameters that will be sent to Elasticsearch.
        $params = [
            'index' => $model,
            'body' => [
                'settings' => self::getIndicesSettings($nestedLimit),
                'mappings' => [
                    'properties' => [],
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

                if ($type == 'string'
                    && property_exists($modelInstance, 'elasticSearchNotAnalyzed')
                    && $modelInstance->elasticSearchNotAnalyzed
                ) {
                    $params['body']['mappings']['properties'][$column]['analyzer'] = 'lowercase';
                }
            }
        }

        // Get custom fields... fields.
        //self::getCustomParams($params['body']['mappings']['properties'], $model);

        // Call to get the information from related models.
        self::getRelatedParams($params['body']['mappings']['properties'], $modelClass, $modelClass, 1, $maxDepth);

        /**
         * Delete the index before creating it again.
         *
         * @todo move this to its own function
         */
        if (self::$client->indices()->exists(['index' => $model])) {
            self::$client->indices()->delete(['index' => $model]);
        }

        return self::$client->indices()->create($params);
    }

    /**
     * Save the object to an elastic index.
     *
     * @param Model $object
     * @param int $maxDepth
     *
     * @return array
     */
    public static function indexDocument(CustomFieldModelInterface $object, int $maxDepth = 3) : array
    {
        // Call the initializer.
        self::initialize();

        // Start the document we are going to insert by converting the object to an array.
        $document = $object->getAll();

        // Use reflection to extract necessary information from the object.
        $modelReflection = (new \ReflectionClass($object));

        self::getRelatedData($document, $object, $modelReflection->name, 1, $maxDepth);

        $params = [
            'index' => strtolower($modelReflection->getShortName()),
            'type' => strtolower($modelReflection->getShortName()),
            'id' => $object->getId(),
            'body' => $document,
        ];

        return self::$client->index($params);
    }

    /**
     * Delete a document from Elastic.
     *
     * @param Model $object
     *
     * @return array
     */
    public static function deleteDocument(Model $object) : array
    {
        // Call the initializer.
        self::initialize();

        // Use reflection to extract necessary information from the object.
        $modelReflection = (new \ReflectionClass($object));

        $params = [
            'index' => strtolower($modelReflection->getShortName()),
            'type' => strtolower($modelReflection->getShortName()),
            'id' => $object->getId(),
        ];

        return self::$client->delete($params);
    }

    /**
     * Retrieve a model's table structure so that we can define the appropriate Elasticsearch data type.
     *
     * @param string $modelPath
     *
     * @return array
     */
    protected static function getFieldsTypes(string $modelPath) : array
    {
        // Get the columns description.
        $columns = self::$di->getDb()->describeColumns(strtolower($modelPath));
        // Define a fields array
        $fields = [];

        // Iterate the columns
        foreach ($columns as $column) {
            switch ($column->getType()) {
                case Column::TYPE_INTEGER:
                    $fields[$column->getName()] = 'integer';
                    break;
                case Column::TYPE_BIGINTEGER:
                    $fields[$column->getName()] = 'long';
                    break;
                case Column::TYPE_TEXT:
                case Column::TYPE_VARCHAR:
                case Column::TYPE_CHAR:
                    $fields[$column->getName()] = 'text';
                    break;
                case Column::TYPE_DATE:
                    // We define a format for date fields.
                    $fields[$column->getName()] = ['date', 'yyyy-MM-dd'];
                    break;
                case Column::TYPE_DATETIME:
                    // We define a format for datetime fields.
                    $fields[$column->getName()] = ['date', 'yyyy-MM-dd HH:mm:ss'];
                    break;
                case Column::TYPE_DECIMAL:
                    $fields[$column->getName()] = 'float';
                    break;
            }
        }

        return $fields;
    }

    /**
     * Get the related models structures and add them to the Elasticsearch definition.
     *
     * @param array $params
     * @param string $parentModel
     * @param string $model
     * @param int $depth
     * @param int $maxDepth
     *
     * @return void
     */
    protected static function getRelatedParams(array &$params, string $parentModel, string $model, int $depth, int $maxDepth) : void
    {
        $depth++;
        $relationsData = self::$di->getModelsManager()->getRelations($model);

        foreach ($relationsData as $relation) {
            $referencedModel = $relation->getReferencedModel();

            if ($referencedModel != $parentModel) {
                $referencedModel = new $referencedModel();

                //ignore properties we don't need right now
                if (array_key_exists('elasticSearch', $relation->getOptions())) {
                    if (!$relation->getOptions()['elasticSearch']) {
                        continue;
                    }
                }

                $alias = strtolower($relation->getOptions()['alias']);
                $params[$alias] = ['type' => 'nested'];

                $fieldsData = self::getFieldsTypes($referencedModel->getSource());
                foreach ($fieldsData as $column => $type) {
                    // For now this is only being used for date/datetime fields
                    if (is_array($type)) {
                        $params[$alias]['properties'][$column] = [
                            'type' => $type[0],
                            'format' => $type[1],
                        ];
                    } else {
                        $params[$alias]['properties'][$column] = ['type' => $type];

                        if ($type == 'string'
                            && property_exists($referencedModel, 'elasticSearchNotAnalyzed')
                            && $referencedModel->elasticSearchNotAnalyzed
                        ) {
                            $params[$alias]['properties'][$column]['analyzer'] = 'lowercase';
                        }
                    }
                }

                self::getCustomParams($params[$alias]['properties'], $relation->getReferencedModel());

                if ($depth < $maxDepth) {
                    self::getRelatedParams(
                        $params[$alias]['properties'],
                        $parentModel,
                        $relation->getReferencedModel(),
                        $depth,
                        $maxDepth
                    );
                }
            }
        }
    }

    /**
     * Get the models custom fields structures and add them to the Elasticsearch definition.
     *
     * @param array $params
     * @param string $modelPath
     *
     * @return void
     */
    protected static function getCustomParams(array &$params, string $modelPath) : void
    {
        $customFields = CustomFields::getFields($modelPath);

        if (!empty($customFields)) {
            $params['custom_fields'] = ['type' => 'nested'];

            foreach ($customFields as $field) {
                $type = [
                    'type' => 'text',
                    'analyzer' => 'lowercase',
                ];
                if ($field['type'] == 'date') {
                    $type = [
                        'type' => 'date',
                        'format' => 'yyyy-MM-dd',
                        'ignore_malformed' => true,
                    ];
                }

                $params['custom_fields']['properties'][$field['name']] = $type;
            }
        }
    }

    /**
     * Get the related models data and add them to the Elasticsearch index.
     *
     * @param array $document
     * @param Model $data
     * @param string $parentModel
     * @param int $depth
     * @param int $maxDepth
     *
     * @return void
     */
    protected static function getRelatedData(array &$document, Model $data, string $parentModel, int $depth, int $maxDepth) : void
    {
        $depth++;
        $modelPath = (new \ReflectionClass($data))->name;
        $model = new $modelPath;

        $hasOne = self::$di->getModelsManager()->getHasOne($model);
        $belongsTo = self::$di->getModelsManager()->getBelongsTo($model);
        $hasMany = self::$di->getModelsManager()->getHasMany($model);

        $hasAll = array_merge($hasOne, $belongsTo);

        foreach ($hasAll as $has) {
            $referencedModel = $has->getReferencedModel();

            if ($referencedModel != $parentModel) {
                $options = $has->getOptions();

                //ignore a relationship if we specify so
                if (array_key_exists('elasticSearch', $options)) {
                    if (!$options['elasticSearch']) {
                        continue;
                    }
                }

                $alias = $has->getOptions()['alias'];
                $aliasKey = strtolower($alias);

                if ($data->$alias) {
                    //if alias exist over write it and get the none deleted
                    $alias = 'get' . $has->getOptions()['alias'];
                    $aliasRecords = $data->$alias('is_deleted = 0');

                    if ($aliasRecords) {
                        $document[$aliasKey] = ModelCustomFields::getCustomFields($aliasRecords, true);

                        if ($depth < $maxDepth) {
                            self::getRelatedData($document[$aliasKey], $aliasRecords, $parentModel, $depth, $maxDepth);
                        }
                    }
                }
            }
        }

        foreach ($hasMany as $has) {
            $referencedModel = $has->getReferencedModel();

            if ($referencedModel != $parentModel) {
                $options = $has->getOptions();

                //ignore a relationship if we specify so
                if (array_key_exists('elasticSearch', $options)) {
                    if (!$options['elasticSearch']) {
                        continue;
                    }
                }

                $alias = $has->getOptions()['alias'];
                $aliasKey = strtolower($alias);

                if ($data->$alias->count()) {
                    //if alias exist over write it and get the none deleted
                    $alias = 'get' . $has->getOptions()['alias'];
                    $aliasRecords = $data->$alias('is_deleted = 0');

                    if (count($aliasRecords) > 0) {
                        foreach ($aliasRecords as $k => $relation) {
                            $document[$aliasKey][$k] = ModelCustomFields::getCustomFields($relation, true);

                            if ($depth < $maxDepth) {
                                self::getRelatedData($document[$aliasKey][$k], $relation, $parentModel, $depth, $maxDepth);
                            }
                        }
                    }
                }
            }
        }
    }
}
