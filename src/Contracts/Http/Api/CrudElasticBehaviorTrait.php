<?php
declare(strict_types=1);

namespace Baka\Contracts\Http\Api;

use Baka\Database\Exception\ModelNotFoundException;
use Baka\Elasticsearch\Models\Documents;
use function Baka\getShortClassName;
use Baka\Http\QueryParser\QueryParser;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;

trait CrudElasticBehaviorTrait
{
    use CrudCustomFieldsBehaviorTrait;

    /**
     * We dont need you in elastic.
     *
     * @param RequestInterface $request
     * @param array|object $results
     *
     * @return array
     */
    protected function appendRelationshipsToResult(RequestInterface $request, $results)
    {
        return $results;
    }

    /**
     * Given a request it will give you the SQL to process.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function processRequest(RequestInterface $request) : array
    {
        //parse the request
        $parse = new QueryParser($this->model, $request->getQuery());
        $parse->setAdditionalQueryFields($this->additionalSearchFields);

        //convert to SQL
        return [
            'sql' => $parse
        ];
    }

    /**
     * Given a process request return the records.
     *
     * @return void
     */
    protected function getRecords(array $processedRequest) : array
    {
        $results = Documents::findBySqlPaginated($processedRequest['sql']->getParsedQuery(), $this->model);

        return [
            'results' => $results['results'],
            'total' => $results['total']
        ];
    }

    /**
     * body of the index function to simply extending methods.
     *
     * @return void
     */
    protected function processIndex()
    {
        //convert the request to sql
        $processedRequest = $this->processRequest($this->request);
        $results = $this->getRecords($processedRequest);

        //this means the want the response in a vuejs format
        if ($this->request->hasQuery('format')) {
            $limit = (int) $this->request->getQuery('limit', 'int', 25);

            $results = [
                'data' => $results,
                'limit' => $limit,
                'page' => $this->request->getQuery('page', 'int', 1),
                'total_pages' => ceil($results['total'] / $limit),
            ];
        }

        return $this->processOutput($results);
    }

    /**
     * Get the element by Id
     * with the current search params user specified in the constructed.
     *
     * @param mixed $id
     *
     * @return ModelInterface|array $results
     */
    protected function getRecordById($id)
    {
        $this->additionalSearchFields[] = [
            $this->model->getPrimaryKey(), ':', $id
        ];

        $processedRequest = $this->processRequest($this->request);
        $records = $this->getRecords($processedRequest);

        //get the results and append its relationships
        $results = $records['results'];

        if (empty($results) || !isset($results[0])) {
            throw new ModelNotFoundException(
                getShortClassName($this->model) . ' Record not found'
            );
        }

        return $results[0];
    }
}
