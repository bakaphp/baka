<?php
declare(strict_types=1);

namespace Baka\Contracts\Http\Api;

use ArgumentCountError;
use Baka\Contracts\Database\ModelInterface;
use Baka\Database\Exception\ModelNotFoundException;
use function Baka\getShortClassName;
use Baka\Http\Converter\RequestUriToSql;
use Baka\Http\Exception\InternalServerErrorException;
use Exception;
use PDO;
use PDOException;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleRecords;

trait CrudBehaviorTrait
{
    /**
     * We need to find the response if you plan to use this trait.
     *
     * @param mixed $content
     * @param int $statusCode
     * @param string $statusMessage
     *
     * @return Response
     */
    abstract protected function response($content, int $statusCode = 200, string $statusMessage = 'OK') : Response;

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
        $parse = new RequestUriToSql($request->getQuery(), $this->model);
        $parse->setCustomColumns($this->customColumns);
        $parse->setCustomTableJoins($this->customTableJoins);
        $parse->setCustomConditions($this->customConditions);
        $parse->setCustomLimit($this->customLimit);
        $parse->setCustomSort($this->customSort);
        $parse->appendParams($this->additionalSearchFields);
        $parse->appendCustomParams($this->additionalCustomSearchFields);
        $parse->appendRelationParams($this->additionalRelationSearchFields);

        //convert to SQL
        return $parse->convert();
    }

    /**
     * Given the results we append the relationships.
     *
     * @param RequestInterface $request
     * @param array|object $results
     *
     * @return array
     */
    protected function appendRelationshipsToResult(RequestInterface $request, $results)
    {
        // Relationships, but we have to change it to sparo full implementation
        if ($request->hasQuery('relationships')) {
            $relationships = $request->getQuery('relationships', 'string');

            $results = RequestUriToSql::parseRelationShips($relationships, $results);
        }

        return $results;
    }

    /**
     * Given the results we will process the output
     * we will check if a DTO transformer exist and if so we will send it over to change it.
     *
     * @param object|array $results
     *
     * @return void
     */
    protected function processOutput($results)
    {
        return $results;
    }

    /**
     * Given a array request from a method DTO transformed to whats is needed to
     * process it.
     *
     * @param array $request
     *
     * @return array
     */
    protected function processInput(array $request) : array
    {
        return $request;
    }

    // TODO: Move it to its own class.

    /**
     * Given a process request return the records.
     *
     * @return void
     */
    protected function getRecords(array $processedRequest) : array
    {
        // TODO: Create a const with these values
        $required = ['sql', 'countSql', 'bind'];

        if ($diff = array_diff($required, array_keys($processedRequest))) {
            throw new ArgumentCountError(
                sprintf(
                    'Request no processed. Missing following params : %s.',
                    implode(', ', $diff)
                )
            );
        }

        try {
            $results = new SimpleRecords(
                null,
                $this->model,
                $this->model->getReadConnection()->query($processedRequest['sql'], $processedRequest['bind'])
            );
            //$results->setHydrateMode(\Phalcon\Mvc\Model\Resultset::HYDRATE_ARRAYS);

            $count = $this->model->getReadConnection()->query(
                $processedRequest['countSql'],
                $processedRequest['bind']
            )->fetch(PDO::FETCH_OBJ)->total;
        } catch (PDOException $e) {
            throw InternalServerErrorException::create(
                $e->getMessage(),
                !$this->config->app->production ? $processedRequest : null
            );
        }
        return [
            'results' => $results,
            'total' => $count
        ];
    }

    /**
     * Given the model list the records based on the  filter.
     *
     * @return Response
     */
    public function index() : Response
    {
        $results = $this->processIndex();

        //return the response + transform it if needed
        return $this->response($results);
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

        //return the kanvas pagination format
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

    /**
     * Get the record by its primary key.
     *
     * @param mixed $id
     *
     * @throws Exception
     *
     * @return Response
     */
    public function getById($id) : Response
    {
        //find the info
        $record = $this->getRecordById($id);

        //get the results and append its relationships
        $result = $this->appendRelationshipsToResult($this->request, $record);

        return $this->response($this->processOutput($result));
    }

    /**
     * Create new record.
     *
     * @return Response
     */
    public function create() : Response
    {
        //process the input
        $result = $this->processCreate($this->request);

        return $this->response($this->processOutput($result));
    }

    /**
     * Process the create request and records the object.
     *
     * @return ModelInterface
     *
     * @throws Exception
     */
    protected function processCreate(RequestInterface $request) : ModelInterface
    {
        //process the input
        $request = $this->processInput($request->getPostData());

        $this->model->saveOrFail($request, $this->createFields);

        return $this->model;
    }

    /**
     * Update a record.
     *
     * @param mixed $id
     *
     * @return Response
     */
    public function edit($id) : Response
    {
        $record = $this->getRecordById($id);

        //process the input
        $result = $this->processEdit($this->request, $record);

        return $this->response($this->processOutput($result));
    }

    /**
     * Process the update request and return the object.
     *
     * @param RequestInterface $request
     * @param ModelInterface $record
     *
     * @throws Exception
     *
     * @return ModelInterface
     */
    protected function processEdit(RequestInterface $request, ModelInterface $record) : ModelInterface
    {
        //process the input
        $request = $this->processInput($request->getPutData());

        $record->updateOrFail($request, $this->updateFields);

        return $record;
    }

    /**
     * Delete a Record.
     *
     * @throws Exception
     *
     * @return Response
     */
    public function delete($id) : Response
    {
        $record = $this->getRecordById($id);

        if ($this->softDelete == 1) {
            $record->softDelete();
        } else {
            $record->delete();
        }

        return $this->response(['Delete Successfully']);
    }
}
