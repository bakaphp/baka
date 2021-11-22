<?php

namespace Baka\Database\CustomFilters;

use Baka\Database\Exception\Exception;
use Baka\Database\Model;
use Baka\Database\SystemModules;

class CustomFilters extends Model
{
    public int $system_modules_id;
    public int $user_id;
    public int $apps_id;
    public int $companies_id;
    public int $companies_branch_id;
    public string $name;
    public string $sequence_logic;
    public string $description;
    public int $total_conditions = 0;
    public int $fields_type_id;

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize() : void
    {
        $this->setSource('custom_filters');
        $this->hasMany(
            'id',
            Conditions::class,
            'custom_filter_id',
            [
                'alias' => 'conditions'
            ]
        );
        $this->belongsTo(
            'system_modules_id',
            SystemModules::class,
            'id',
            [
                'alias' => 'systemModule'
            ]
        );
    }

    /**
     * Get the query for this filter.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSql() : string
    {
        $conditions = $this->conditions;

        if (empty($conditions)) {
            throw new Exception('No conditions found on this filter to generate a query');
        }

        $model = new $this->systemModule->model_name();

        $sql = 'SELECT * FROM ' . $model->getSource() . ' WHERE ' . $this->sequence_logic;

        $replace = [];
        foreach ($conditions as $condition) {
            $condition->value = !is_numeric($condition->value) ? "'{$condition->value}'" : $condition->value;
            $replace[$condition->position] = $condition->field . ' ' . $condition->comparator . ' ' . $condition->value;
        }

        //replace the # for their array position
        return strtr($sql, $replace);
    }
}
