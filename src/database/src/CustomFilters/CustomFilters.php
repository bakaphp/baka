<?php

namespace Baka\Database\CustomFilters;

use Baka\Database\Model;
use Baka\Database\Contracts\HashTableTrait;
use Baka\Database\Exception\Exception;

class CustomFilters extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var int
     */
    public $system_modules_id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var int
     */
    public $apps_id;

    /**
     * @var int
     */
    public $companies_id;

    /**
     * @var int
     */
    public $companies_branch_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $sequence_logic;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $total_conditions;

    /**
     * @var int
     */
    public $fields_type_id;

    /**
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_filters';
    }

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->hasMany('id', '\Baka\Database\CustomFilters\Conditions', 'custom_filter_id', ['alias' => 'conditions']);
        $this->belongsTo('system_modules_id', '\Baka\Database\SystemModules', 'id', ['alias' => 'systemModule']);
    }

    /**
     * Get the query for this filter.
     *
     * @return string
     * @throws Exception
     */
    public function getQuery() : string
    {
        $conditions = $this->conditions;

        if (empty($conditions)) {
            throw new Exception('No conditions found on this filter to generate a query');
        }

        $module = new $this->systemModule->model_name;

        $sql = 'SELECT * FROM ' . $module->getSource() . ' WHERE ' . $this->sequence_logic;

        $replace = [];
        foreach ($conditions as $condition) {
            $condition->value = !is_numeric($condition->value) ? "'{$condition->value}'" : $condition->value;
            $replace[$condition->position] = $condition->field . ' ' . $condition->comparator . ' ' . $condition->value;
        }

        //replace the # for their array position
        return strtr($sql, $replace);
    }
}
