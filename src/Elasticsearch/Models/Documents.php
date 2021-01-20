<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Models;

use Baka\Contracts\CustomFields\CustomFieldsTrait;
use Baka\Contracts\Database\ElasticModelInterface;
use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\IndexBuilder;
use Baka\Elasticsearch\Query;
use Phalcon\Mvc\Model;
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
    public static function add(ElasticModelInterface $model, int $maxDepth = 3) : array
    {
        // Start the document we are going to insert by converting the object to an array.
        $document = $model->toArray();

        //merge custom fields
        if (in_array(CustomFieldsTrait::class, class_uses(get_class($model)))) {
            $document = array_merge($document, $model->getAll());
        }

        // Use reflection to extract necessary information from the object.
        $modelReflection = (new ReflectionClass($model));

        IndexBuilder::getRelatedData($document, $model, $modelReflection->name, 0, $maxDepth);

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
    public static function delete(ElasticModelInterface $model) : array
    {
        $params = [
            'index' => Indices::getName($model),
            'id' => $model->getId(),
        ];

        return Client::getInstance()->delete($params);
    }

    /**
     * Find by query in this document.
     *
     * @param string $sql
     *
     * @return array
     */
    public static function findBySql(string $sql, ElasticModelInterface $model) : array
    {
        $elasticQuery = new Query($sql, $model);

        return $elasticQuery->find();
    }

    /**
     * Find by query and get total results.
     *
     * @todo this is shitting we should implement resultset interface
     *
     * @param string $sql
     * @param ElasticModelInterface $model
     *
     * @return array
     */
    public static function findBySqlPaginated(string $sql, ElasticModelInterface $model) : array
    {
        $elasticQuery = new Query($sql, $model);

        return [
            'results' => $elasticQuery->find(),
            'total' => $elasticQuery->getTotal()
        ];
    }
}
