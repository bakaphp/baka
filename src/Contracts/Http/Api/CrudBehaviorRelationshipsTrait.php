<?php
declare(strict_types=1);

namespace Baka\Contracts\Http\Api;

use Phalcon\Http\Response;

trait CrudBehaviorRelationshipsTrait
{
    use CrudBehaviorTrait;

    /**
     * Parent Primary Key Data.
     *
     * @var int|string
     */
    protected $parentId;

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
        $id = $this->router->getParams()['id'];
        return parent::getById($id);
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
        $id = $this->router->getParams()['id'];
        return parent::edit($id);
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
        $id = $this->router->getParams()['id'];
        return parent::delete($id);
    }
}
