<?php
declare(strict_types=1);

namespace Baka\Contracts\Http\Api;

use ArgumentCountError;
use Baka\Elasticsearch\Client;
use Baka\Http\Converter\RequestUriToElasticSearch;
use Baka\Http\QueryParser\QueryParser;
use Phalcon\Http\RequestInterface;

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
        return $parse;
    }

    /**
     * Given a process request return the records.
     *
     * @return void
     */
    protected function getRecords(array $processedRequest) : array
    {
        $required = ['sql', 'countSql', 'bind'];

        if (count(array_intersect_key(array_flip($required), $processedRequest)) != count($required)) {
            throw new ArgumentCountError('Not a processed request missing any of the following params : SQL, CountSQL, Bind');
        }

        $client = new Client('http://' . current($this->config->elasticSearch['hosts']));
        $results = $client->findBySql($processedRequest['sql']);

        return [
            'results' => $results,
            'total' => 0 //@todo fix this
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
        $records = $this->getRecords($processedRequest);

        //get the results and append its relationships
        $results = $this->appendRelationshipsToResult($this->request, $records['results']);

        //this means the want the response in a vuejs format
        if ($this->request->hasQuery('format')) {
            $limit = (int) $this->request->getQuery('limit', 'int', 25);

            $results = [
                'data' => $results,
                'limit' => $limit,
                'page' => $this->request->getQuery('page', 'int', 1),
                'total_pages' => ceil($records['total'] / $limit),
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
