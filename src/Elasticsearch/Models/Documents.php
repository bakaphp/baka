<?php

namespace Baka\Elasticsearch\Models;

use Baka\Contracts\CustomFields\CustomFieldsTrait;
use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\IndexBuilder;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use ReflectionClass;

class Documents
{
    /**
     * Save the object to an elastic index.
     *
     * @param Model $object
     * @param int $maxDepth
     *
     * @return array
     */
    public static function add(ModelInterface $model, int $maxDepth = 3) : array
    {
        // Start the document we are going to insert by converting the object to an array.
        $document = $model->toArray();

        //merge custom fields
        if (in_array(CustomFieldsTrait::class, class_uses(get_class($model)))) {
            $document = array_merge($document, $model->getAll());
        }

        // Use reflection to extract necessary information from the object.
        $modelReflection = (new ReflectionClass($model));

        IndexBuilder::getRelatedData($document, $model, $modelReflection->name, 1, $maxDepth);
        $params = [
            'index' => Indices::getName($model),
            'id' => $model->getId(),
            'body' => $document,
        ];

        return Client::getInstance()->index($params);
    }

    /**
     * Delete a document from Elastic.
     *
     * @param Model $object
     *
     * @return array
     */
    public static function delete(ModelInterface $model) : array
    {
        $params = [
            'index' => Indices::getName($model),
            'id' => $model->getId(),
        ];

        return Client::getInstance()->delete($params);
    }
}
