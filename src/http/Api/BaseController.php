<?php

namespace Baka\Http\Api;

use Baka\Http\Contracts\Api\ResponseTrait;
use Phalcon\Mvc\Controller;

/**
 * Default REST API Base Controller.
 */
class BaseController extends Controller
{
    use ResponseTrait;

    /**
     * Soft delete option, default 1.
     *
     * @var int
     */
    public $softDelete = 0;

    /**
     * fields we accept to create.
     *
     * @var array
     */
    protected $createFields = [];

    /**
     * fields we accept to update.
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * PhalconPHP Model.
     *
     * @var object
     */
    public $model;

    /**
     * @param array $normalSearchFields
     */
    protected $additionalSearchFields = [];

    /**
     * @param array $customSearchFields
     */
    protected $additionalCustomSearchFields = [];

    /**
     * @param array $relationSearchFields
     */
    protected $additionalRelationSearchFields = [];

    /**
     * Specify any customf columns.
     *
     * @var string
     */
    protected $customColumns = null;

    /**
     * Specify any custom join tables.
     *
     * @var string
     */
    protected $customTableJoins = null;

    /**
     * Specify any custom conditionals we need.
     *
     * @var string
     */
    protected $customConditions = null;

    /**
     * Specify the custom limit.
     *
     * @var int
     */
    protected $customLimit = null;

    /**
     * Specify the sort limit.
     *
     * @var strong
     */
    protected $customSort = null;
}
