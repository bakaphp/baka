<?php
declare(strict_types=1);

namespace Baka\Contracts\Http\Api;

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
