<?php

namespace Baka\Contracts\Http\Api;

use Baka\Http\Converter\RequestUriToElasticSearch;
use Exception;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\ModelInterface;

trait CrudCustomFieldsBehaviorTrait
{
    use CrudBehaviorTrait {
        CrudBehaviorTrait::processCreate as processCreateParent;
        CrudBehaviorTrait::processEdit as processEditParent;
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

            $results = is_object($results) ? RequestUriToElasticSearch::parseRelationShips($relationships, $results) : $results;
        }

        return $results;
    }

    /**
     * Process output.
     *
     * @param mixed $results
     *
     * @return mixed
     */
    protected function processOutput($results)
    {
        return is_object($results) ? $results->toFullArray() : $results;
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
        //set the custom fields to create
        $this->model->setCustomFields($request->getPostData());

        $this->processCreateParent($request);

        return $this->model;
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
        //set the custom fields to update
        $record->setCustomFields($request->getPutData());

        $record = $this->processEditParent($request, $record);

        return $record;
    }
}
