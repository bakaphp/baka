<?php

declare(strict_types=1);

namespace Baka\Contracts\Elasticsearch;

use Baka\Elasticsearch\IndexBuilder;
use Baka\Elasticsearch\Models\Documents;
use Baka\Elasticsearch\Models\Indices;

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
        Indices::create($model, $maxDepth, $nestedLimit);
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
    public function createDocumentsAction(string $model, int $maxDepth = 3) : void
    {
        // Get model's records
        $records = $model::find('is_deleted = 0');
        // Get elasticsearch class handler instance
        $elasticsearch = new IndexBuilder();

        foreach ($records as $record) {
            Documents::add($record, $maxDepth);
        }
    }
}
