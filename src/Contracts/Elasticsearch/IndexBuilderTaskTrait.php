<?php

declare(strict_types=1);

namespace Baka\Contracts\Elasticsearch;

use Baka\Elasticsearch\IndexBuilder;
use Baka\Elasticsearch\Models\Documents;
use Baka\Elasticsearch\Models\Indices;
use Baka\Elasticsearch\Objects\Documents as ModelDocuments;
use Baka\Elasticsearch\Objects\Indices as ObjectIndices;

trait IndexBuilderTaskTrait
{
    /**
     * Action Descriptor.
     *
     * Command: indices
     * Description: Create the elasticsearch index for a model.
     *
     * php cli/app.php elasticsearch createIndex index_name 4 (model relationship length)
     *
     * @param string $model
     * @param int $maxDepth
     * @param int $nestedLimit
     *
     * @return void
     */
    public function createIndexAction(string $model, int $maxDepth = 3, int $nestedLimit = 75) : void
    {
        if (new $model instanceof ModelDocuments) {
            $indices = ObjectIndices::create(new $model(), $maxDepth, $nestedLimit);
        } else {
            $indices = Indices::create($model, $maxDepth, $nestedLimit);
        }
        echo "Indices {$model} created " . json_encode($indices);
    }

    /**
     * Delete a indice base on its model namespace name.
     *
     * @param string $model
     *
     * @return void
     */
    public function deleteIndexAction(string $model) : void
    {
        $modelName = new $model();
        $indices = Indices::delete($modelName);

        echo "Indices {$model} deleted " . json_encode($indices);
    }

    /**
     * Action Descriptor.
     *
     * Command: index
     * Description: Create the elasticsearch index and insert all the model data
     *
     * php cli/app.php elasticsearch index modelName 0 1
     *
     * @param string $model
     * @param int $maxDepth
     *
     * @return void
     */
    public function createDocumentsAction(string $model, int $maxDepth = 3, int $limit = 0) : void
    {
        // Get model's records
        $limitSql = $limit ? ' LIMIT ' . $limit : null;
        $records = $model::find('is_deleted = 0' . $limitSql);
        $totalRecords = $records->count();
        // Get elasticsearch class handler instance
        $elasticsearch = new IndexBuilder();

        foreach ($records as $record) {
            Documents::add($record, $maxDepth);
        }

        echo "Total records inserted for {$model} " . $totalRecords;
    }

    /**
     * createDocumentsElasticAction.
     *
     * @param  string $model
     * @param  string $modelDocument
     * @param  int $maxDepth
     * @param  int $limit
     *
     * @return void
     */
    public function createDocumentsElasticAction(string $model, string $modelDocument, int $maxDepth = 3, int $limit = 0) : void
    {
        // Get model's records
        $limitSql = $limit ? ' LIMIT ' . $limit : null;
        $records = $model::find('is_deleted = 0' . $limitSql);
        $totalRecords = $records->count();
        // Get elasticsearch class handler instance
        $elasticsearch = new IndexBuilder();

        foreach ($records as $record) {
            $row = new $modelDocument();
            $row->setData((int)$record->id, $record->toArray());
            $row->add();
            echo "Add record {$row->id} \n";
        }

        echo "Total records inserted for {$model} " . $totalRecords;
    }
}
