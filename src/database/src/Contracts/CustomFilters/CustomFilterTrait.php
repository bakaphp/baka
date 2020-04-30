<?php

namespace Baka\Database\Contracts\CustomFilters;

use Baka\Database\CustomFilters\CustomFilters;
use Baka\Database\CustomFilters\Conditions;
use Baka\Database\Exception\CustomFilterException;
use RuntimeException;

/**
 * Custom field class.
 */
trait CustomFilterTrait
{
    /**
     * Given the POST Array create a filter.
     *
     * [
     *  'system_modules_id' = > 1,
     *  'apps_id' = > 1,
     *  ....
     *  'criterias' => [
     *    ]
     * ]
     *
     * @param array $params
     * @return void
     */
    public function processFilter(array $params): CustomFilters
    {
        //check sytem module?
        if (!array_key_exists('criterias', $params)) {
            throw new RuntimeException('Expected Criteria key on this array');
        }

        $customFilter = null;
        if (array_key_exists('id', $params)) {
            $customFilter = CustomFilters::findFirst([
                'conditions' => 'id = ?0 and apps_id = ?1 and companies_id = ?2 and system_modules_id = ?3',
                'bind' => [$params['id'], $params['apps_id'], $params['companies_id'], $params['system_modules_id']]
            ]);
        }

        //if we cant find it we create it
        if (!is_object($customFilter)) {
            $customFilter = new CustomFilters();
            $customFilter->system_modules_id = $params['system_modules_id'];
            $customFilter->apps_id = $params['apps_id'];
            $customFilter->companies_id = $params['companies_id'];
            $customFilter->companies_branch_id = $params['companies_branch_id'];
            $customFilter->users_id = $params['users_id'];
            $customFilter->fields_type_id = $params['fields_type_id'];
        }

        $customFilter->name = $params['name'];
        $customFilter->sequence_logic = $params['sequence_logic'];
        $customFilter->total_conditions = count($params['criterias']);
        $customFilter->description = $params['description'];
        $customFilter->saveOrFail();

        return $customFilter;
    }

    /**
     * Given a filter and it soptions save process the critiria.
     *
     * [
     *     [
     *          {
     *              "comparator": "equal",
     *              "value": "333",
     *              "field": "Annual Revenue"
     *           },
     *           "and",
     *           {
     *               "comparator": "equal",
     *               "value": "${NOTEMPTY}",
     *               "field": "Campaign Name"
     *           }
     *       ]
     *   ]
     *
     * @param CustomFilters $filter
     * @param array $criterias
     * @return void
     */
    public function processsCriterias(CustomFilters $filter, array $criterias) : bool
    {
        for ($i = 0 ; $i < count($criterias) ; $i++) {
            //not an array then you are the conditional between the 2 operators
            if (!is_array($criterias[$i])) {
                continue;
            }

            //the last element of a criteria doesnt have a conditonal
            $conditional = array_key_exists($i + 1, $criterias) ? $criterias[$i + 1] : ' ';

            $customFilterCondition = new Conditions();
            $customFilterCondition->custom_filter_id = $filter->getId();
            $customFilterCondition->position = $i > 0 ? $i : $i + 1;
            $customFilterCondition->value = $criterias[$i]['value'];
            $customFilterCondition->field = $criterias[$i]['field'];
            $customFilterCondition->comparator = $criterias[$i]['comparator'];
            $customFilterCondition->conditional = $conditional;
            $customFilterCondition->created_at = date('Y-m-d H:i:s');
            $customFilterCondition->saveOrFail();
        }

        return true;
    }

    /**
     * Given the criteria update the filter.
     *
     * @param CustomFilters $filter
     * @param array $criterias
     * @return boolean
     */
    public function updateCriterias(CustomFilters $filter, array $criterias): bool
    {
        //clean all the conditions
        $filter->conditions->delete();

        return $this->processsCriterias($filter, $criterias);
    }
}
