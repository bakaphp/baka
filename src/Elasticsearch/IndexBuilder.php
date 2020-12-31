<?php
declare(strict_types=1);

namespace Baka\Elasticsearch;

use Baka\Contracts\Database\ModelInterface;
use Baka\Database\CustomFields\CustomFields;
use Baka\Elasticsearch\Model as ModelCustomFields;
use Exception;
use Phalcon\Db\Column;
use Phalcon\Di;
use Phalcon\Mvc\Model;
use ReflectionClass;

class IndexBuilder
{
    protected static ?Di $di = null;

    /**
     * Get the general settings for our predefine indices.
     *
     * @param int $nestedLimit
     *
     * @return array
     */
    public static function getIndicesSettings(int $nestedLimit) : array
    {
        return [
            'index.mapping.nested_fields.limit' => $nestedLimit,
            'index.mapping.total_fields.limit' => $nestedLimit,
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
     * Retrieve a model's table structure so that we can define the appropriate Elasticsearch data type.
     *
     * @param string $modelPath
     *
     * @return array
     */
    public static function getFieldsTypes(ModelInterface $model) : array
    {
        // Get the columns description.
        $columns = $model->getReadConnection()->describeColumns($model->getSource());
        // Define a fields array
        $fields = [];

        // Iterate the columns
        foreach ($columns as $column) {
            switch ($column->getType()) {
                case Column::TYPE_MEDIUMINTEGER:
                case Column::TYPE_BOOLEAN:
                case Column::TYPE_SMALLINTEGER:
                case Column::TYPE_TINYINTEGER:
                case Column::TYPE_INTEGER:
                    $fields[$column->getName()] = 'integer';
                    break;
                case Column::TYPE_BIGINTEGER:
                    $fields[$column->getName()] = 'long';
                    break;
                case Column::TYPE_TEXT:
                case Column::TYPE_MEDIUMTEXT:
                case Column::TYPE_MEDIUMBLOB:
                case Column::TYPE_LONGTEXT:
                case Column::TYPE_LONGBLOB:
                case Column::TYPE_TINYTEXT:
                case Column::TYPE_VARCHAR:
                case Column::TYPE_CHAR:
                    $fields[$column->getName()] = 'text';
                    // We define a format for datetime fields.
                    $fields[$column->getName()] = ['date', 'yyyy-MM-dd HH:mm:ss'];
                    break;
                case Column::TYPE_FLOAT:
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
    public static function getRelatedParams(array &$params, string $parentModel, string $model, int $depth, int $maxDepth) : void
    {
        self::$di = Di::getDefault();

        $depth++;
        $relationsData = self::$di->getModelsManager()->getRelations($model);

        foreach ($relationsData as $relation) {
            $referencedModel = $relation->getReferencedModel();

            if ($referencedModel != $parentModel) {
                $referencedModel = new $referencedModel();

                if (!is_array($relation->getOptions()) || !array_key_exists('alias', $relation->getOptions())) {
                    throw new Exception('Model Relationship ' . get_class($referencedModel) . ' need alias defined');
                };

                //ignore properties we don't need right now
                if (array_key_exists('elasticIndex', $relation->getOptions())) {
                    if (!$relation->getOptions()['elasticIndex']) {
                        continue;
                    }
                }

                $alias = strtolower($relation->getOptions()['alias']);
                $params[$alias] = ['type' => 'nested'];

                $fieldsData = self::getFieldsTypes($referencedModel);
                foreach ($fieldsData as $column => $type) {
                    // For now this is only being used for date/datetime fields
                    if (is_array($type)) {
                        $params[$alias]['properties'][$column] = [
                            'type' => $type[0],
                            'format' => $type[1],
                        ];
                    } else {
                        $params[$alias]['properties'][$column] = ['type' => $type];

                        if (self::useFieldSearchNotAnalyzed($type, $referencedModel)) {
                            $params[$alias]['properties'][$column]['analyzer'] = 'lowercase';
                        }

                        if (self::useFieldSearchTextFieldData($type, $referencedModel)) {
                            $params[$alias]['properties'][$column]['fielddata'] = true;
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
    public static function getCustomParams(array &$params, string $modelPath) : void
    {
        $customFields = CustomFields::getFields($modelPath);

        if (!empty($customFields)) {
            //$params['custom_fields'] = ['type' => 'nested'];

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

                //$params['custom_fields']['properties'][$field['name']] = $type;
                $params[$field['name']] = $type;
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
    public static function getRelatedData(array &$document, ModelInterface $data, string $parentModel, int $depth, int $maxDepth) : void
    {
        self::$di = Di::getDefault();
        $depth++;
        $modelPath = (new ReflectionClass($data))->name;
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
                if (array_key_exists('elasticIndex', $options)) {
                    if (!$options['elasticIndex']) {
                        continue;
                    }
                }

                $alias = $has->getOptions()['alias'];
                $aliasKey = strtolower($alias);

                if ($data->$alias) {
                    //if alias exist over write it and get the none deleted
                    $alias = 'get' . $has->getOptions()['alias'];
                    $aliasRecords = $data->$alias();
                    if (is_object($aliasRecords) && $aliasRecords->hasProperty('is_deleted')) {
                        $aliasRecords = $data->$alias('is_deleted = 0');
                    }
                    if ($aliasRecords && is_object($aliasRecords)) {
                        $document[$aliasKey] = $aliasRecords->toFullArray();
                        //$document[$aliasKey] = ModelCustomFields::getCustomFields($aliasRecords, true);

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
                if (array_key_exists('elasticIndex', $options)) {
                    if (!$options['elasticIndex']) {
                        continue;
                    }
                }

                $alias = $has->getOptions()['alias'];
                $aliasKey = strtolower($alias);

                if ($data->$alias->count()) {
                    //if alias exist over write it and get the none deleted
                    $alias = 'get' . $has->getOptions()['alias'];
                    $metadata = $data->$alias()[0];
                    $aliasIsDeleted = null;
                    if ($metadata->hasProperty('is_deleted')) {
                        $aliasIsDeleted = 'is_deleted = 0';
                    }
                    $aliasRecords = $data->$alias($aliasIsDeleted);
                    if (count($aliasRecords) > 0) {
                        foreach ($aliasRecords as $k => $relation) {
                            $document[$aliasKey][$k] = $relation->toFullArray();
                            //$document[$aliasKey][$k] = $relation::getCustomFields($relation, true);

                            if ($depth < $maxDepth) {
                                self::getRelatedData($document[$aliasKey][$k], $relation, $parentModel, $depth, $maxDepth);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Validate if the model uses search not analyzed fields
     * so we can create the indice with
     *     $params['body']['mappings']['properties'][$column]['analyzer'] = 'lowercase';.
     *
     * @param string $type
     * @param ModelInterface $model
     *
     * @return bool
     */
    public static function useFieldSearchNotAnalyzed(string $type, ModelInterface $model) : bool
    {
        return $type == 'text'
                && property_exists($model, 'elasticSearchNotAnalyzed')
                && $model->elasticSearchNotAnalyzed;
    }

    /**
     * Validate if the model uses search fielddata for the text fields
     * allowing us to sort this type of fields in elastic.
     *
     * @param string $type
     * @param ModelInterface $model
     *
     * @return bool
     */
    public static function useFieldSearchTextFieldData(string $type, ModelInterface $model) : bool
    {
        return $type == 'text'
                && (
                    (property_exists($model, 'elasticSearchTextFieldData') && $model->elasticSearchTextFieldData)
                    || !isset($model->elasticSearchTextFieldData)
                );
    }
}
