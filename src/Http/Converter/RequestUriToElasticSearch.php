<?php

namespace Baka\Http\Converter;

use Baka\Database\Model;
use Exception;

/**
 * Base QueryParser. Parse GET request for a API to a array Phalcon Model find and FindFirst can interpret.
 *
 * Supports queries with the following parameters:
 *   Searching:
 *     q=(searchField1:value1,searchField2:value2)
 *   Partial Responses:
 *     fields=(field1,field2,field3)
 *   Limits:
 *     limit=10
 *   Partials:
 *     offset=20
 */
class RequestUriToElasticSearch extends RequestUriToSql
{
    /**
     * @var array
     */
    protected $operators = [
        ':' => '=',
        '>' => '>=',
        '<' => '<=',
        '~' => '!=',
    ];

    /**
     * Pass the request.
     */
    public function __construct(array $request, Model $model)
    {
        $this->request = $request;
        $this->model = $model;
    }

    /**
     * Main method for parsing a query string.
     * Finds search parameters, partial response fields, limits, and offsets.
     * Sets Controller fields for these variables.
     *
     * @param  array $allowedFields Allowed fields array for search and partials
     *
     * @return bool              Always true if no exception is thrown
     */
    public function convert() : array
    {
        $params = [
            'subquery' => '',
        ];

        $hasSubquery = false;

        //if we find that we are using custom field this is a different beast so we have to send it
        //to another function to deal with this shit
        if (array_key_exists('cq', $this->request)) {
            $params['cparams'] = $this->request['cq'];
        }

        //verify the user is searching for something
        if (array_key_exists('q', $this->request)) {
            $params['params'] = $this->request['q'];
        }

        // Check to see if the user wants certain columns returned
        if (array_key_exists('columns', $this->request)) {
            $this->parseColumns($this->request['columns']);
        } else {
            $this->columns = '*';
        }

        // Check the limit the user is asking for.
        if (array_key_exists('limit', $this->request)) {
            $limit = (int) $this->request['limit'];
            // Prevent ridiculous limits. Nothing above 200 and nothing below 1.
            if ($limit >= 1 && $limit <= 200) {
                $this->limit = $limit;
            } elseif ($limit > 200) {
                $this->limit = 200;
            } elseif ($limit < 1) {
                $this->limit = 25;
            }
        }

        // Check the page the user is asking for.
        if (array_key_exists('page', $this->request)) {
            $page = (int) $this->request['page'];
            // Prevent ridiculous pagination requests
            if ($page >= 1) {
                $this->page = $page;
            }
        }

        // Sorting logic for related searches.
        if (array_key_exists('sort', $this->request)) {
            if (!empty($this->request['sort'])) {
                $this->setCustomSort(trim($this->request['sort']));
            }
        }

        // Prepare the search parameters.
        $this->prepareParams($params);

        // Append any additional user parameters
        $this->appendAdditionalParams();
        //base on th eesarch params get the raw query
        $rawSql = $this->prepareCustomSearch();

        if (!is_null($this->sort)) {
            $rawSql['sql'] .= $this->sort;
        }

        // Calculate the corresponding offset
        $this->offset = ($this->page - 1) * $this->limit;
        $rawSql['sql'] .= " LIMIT {$this->limit} OFFSET {$this->offset}";

        return $rawSql;
    }

    /**
     * gien the request array , get the custom query to find the results.
     *
     * @param  array  $params
     *
     * @return string
     */
    protected function prepareCustomSearch($hasSubquery = false) : array
    {
        $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
        $classReflection = (new \ReflectionClass($this->model));
        $classname = $this->model->getSource();

        $primaryKey = null;

        if ($primaryKey = $metaData->getPrimaryKeyAttributes($this->model)) {
            $primaryKey = $primaryKey[0];
        }

        $sql = '';

        $sql .= ' WHERE';

        // create normal sql search
        if (!empty($this->normalSearchFields)) {
            foreach ($this->normalSearchFields as $fKey => $searchFieldValues) {
                if (is_array(current($searchFieldValues))) {
                    foreach ($searchFieldValues as $csKey => $chainSearch) {
                        $sql .= !$csKey ? ' AND  (' : '';
                        $sql .= $this->prepareNormalSql($chainSearch, $classname, ($csKey ? 'OR' : ''), $fKey);
                        $sql .= ($csKey == count($searchFieldValues) - 1) ? ') ' : '';
                    }
                } else {
                    $sql .= $this->prepareNormalSql($searchFieldValues, $classname, 'AND', $fKey);
                }
            }
        }

        // create custom query sql
        if (!empty($this->customSearchFields)) {
            // print_r($this->customSearchFields);die();
            // We have to pre-process the fields in order to have them bundled together.
            $customSearchFields = [];

            foreach ($this->customSearchFields as $fKey => $searchFieldValues) {
                if (is_array(current($searchFieldValues))) {
                    foreach ($searchFieldValues as $csKey => $chainSearch) {
                        $searchTable = explode('.', $chainSearch[0])[0];
                        $customSearchFields[$fKey][$searchTable][] = $chainSearch;
                    }
                } else {
                    $searchTable = explode('.', $searchFieldValues[0])[0];
                    $customSearchFields[$searchTable][] = $searchFieldValues;
                }
            }

            // print_r($customSearchFields);die();

            $prepareNestedSql = function (array $searchCriteria, string $classname, string $andOr, string $fKey) : string {
                $sql = '';
                $textFields = $this->getTextFields($classname);
                list($searchField, $operator, $searchValues) = $searchCriteria;
                $operator = $this->operators[$operator];

                if (trim($searchValues) !== '') {
                    if ($searchValues == '%%') {
                        $sql .= ' ' . $andOr . ' (' . $searchField . ' IS NULL';
                        $sql .= ' OR ' . $searchField . ' = ""';

                        if ($this->model->$searchField === 0) {
                            $sql .= ' OR ' . $searchField . ' = 0';
                        }

                        $sql .= ')';
                    } elseif ($searchValues == '$$') {
                        $sql .= ' ' . $andOr . ' (' . $searchField . ' IS NOT NULL';
                        $sql .= ' OR ' . $searchField . ' != ""';

                        if ($this->model->$searchField === 0) {
                            $sql .= ' OR ' . $searchField . ' ) != 0';
                        }

                        $sql .= ')';
                    } else {
                        if (strpos($searchValues, '|')) {
                            $searchValues = explode('|', $searchValues);
                        } else {
                            $searchValues = [$searchValues];
                        }

                        $sqlArray = [];
                        foreach ($searchValues as $vKey => $value) {
                            if ((preg_match('#^%[^%]+%|%[^%]+|[^%]+%$#i', $value))
                                || $value == '%%'
                            ) {
                                $operator = 'LIKE';
                            }

                            if (!$vKey) {
                                $sql .= ' ' . $andOr . ' (' . $searchField . ' ' . $operator . ' :f' . $searchField . $fKey . $vKey;
                            } else {
                                $sql .= ' OR ' . $searchField . ' ' . $operator . ' :f' . $searchField . $fKey . $vKey;
                            }

                            $this->bindParamsKeys[] = ':f' . $searchField . $fKey . $vKey;
                            $this->bindParamsValues[] = "'{$value}'";
                        }

                        $sql .= ')';
                    }
                }

                return $sql;
            };

            // With the stuff processed we now proceed to assemble the query

            foreach ($customSearchFields as $fKey => $searchFieldValues) {
                // If the key is an integer, this means the fields have to be OR'd inside the nesting
                if (is_int($fKey)) {
                    $nestedSql = ' AND (';
                    $first = true;
                    foreach ($searchFieldValues as $csKey => $chainSearch) {
                        if (count($chainSearch) > 1) {
                            $nestedSql .= ' nested("' . $csKey . '",';
                            foreach ($chainSearch as $cKey => $chain) {
                                $nestedSql .= $prepareNestedSql($chain, $classname, ($cKey ? 'OR' : ''), $csKey . $cKey);
                            }
                            $nestedSql .= ') ';
                        } else {
                            $nestedSql .= !$first ? ' OR nested("' . $csKey . '",' : ' nested("' . $csKey . '",';
                            $nestedSql .= $prepareNestedSql($chainSearch[0], $classname, '', $csKey);
                            $nestedSql .= ') ';
                        }
                        $first = false;
                    }
                    $sql .= $nestedSql . ') ';
                } else {
                    $nestedSql = ' AND nested("' . $fKey . '",';
                    foreach ($searchFieldValues as $csKey => $chainSearch) {
                        $nestedSql .= $prepareNestedSql($chainSearch, $classname, ($csKey ? 'AND' : ''), $fKey);
                    }
                    $nestedSql .= ') ';
                    $sql .= $nestedSql;
                }
            }

            // ==================================================
            // ==================================================
            // ==================================================

            // foreach ($this->customSearchFields as $fKey => $searchFieldValues) {
            //     if (is_array(current($searchFieldValues))) {
            //         foreach ($searchFieldValues as $csKey => $chainSearch) {
            //             $sql .= !$csKey ? ' AND  (' : '';
            //             $sql .= $this->prepareNestedSql($chainSearch, $classname, ($csKey ? 'OR' : ''), $fKey);
            //             $sql .= ($csKey == count($searchFieldValues) - 1) ? ') ' : '';
            //         }
            //     } else {
            //         $sql .= $this->prepareNestedSql($searchFieldValues, $classname, 'AND', $fKey);
            //     }
            // }
        }

        // Replace initial `AND ` or `OR ` to avoid SQL errors.
        $sql = str_replace(
            ['WHERE AND', 'WHERE OR', 'WHERE ( OR'],
            ['WHERE', 'WHERE', 'WHERE ('],
            $sql
        );

        // Remove empty where from the end of the string.
        $sql = preg_replace('# WHERE$#', '', $sql);

        //sql string
        $countSql = 'SELECT COUNT(*) total FROM ' . $classname . $this->customTableJoins . $sql . $this->customConditions;
        $resultsSql = "SELECT {$this->columns} {$this->customColumns} FROM {$classname} {$this->customTableJoins} {$sql} {$this->customConditions}";
        //bind params
        $bindParams = array_combine($this->bindParamsKeys, $this->bindParamsValues);

        return [
            'sql' => strtr($resultsSql, $bindParams),
            'countSql' => strtr($countSql, $bindParams),
            'bind' => null,
        ];
    }

    /**
     * Prepare the SQL for a normal search.
     *
     * @param array $searchCriteria
     * @param string $classname
     * @param string $andOr
     * @param int $fKey
     *
     * @return string
     */
    protected function prepareNormalSql(array $searchCriteria, string $classname, string $andOr, int $fKey) : string
    {
        $sql = '';
        $textFields = $this->getTextFields($classname);
        list($searchField, $operator, $searchValues) = $searchCriteria;
        $operator = $this->operators[$operator];

        if (trim($searchValues) !== '') {
            if ($searchValues == '%%') {
                $sql .= ' ' . $andOr . ' (' . $searchField . ' IS NULL';
                $sql .= ' OR ' . $searchField . ' = ""';

                if ($this->model->$searchField === 0) {
                    $sql .= ' OR ' . $searchField . ' = 0';
                }

                $sql .= ')';
            } elseif ($searchValues == '$$') {
                $sql .= ' ' . $andOr . ' (' . $searchField . ' IS NOT NULL';
                $sql .= ' OR ' . $searchField . ' != ""';

                if ($this->model->$searchField === 0) {
                    $sql .= ' OR ' . $searchField . ' != 0';
                }

                $sql .= ')';
            } else {
                if (strpos($searchValues, '|')) {
                    $searchValues = explode('|', $searchValues);
                } else {
                    $searchValues = [$searchValues];
                }

                foreach ($searchValues as $vKey => $value) {
                    if (preg_match('#^%[^%]+%|%[^%]+|[^%]+%$#i', $value)
                        || $value == '%%'
                    ) {
                        $operator = 'LIKE';
                    }

                    if (!$vKey) {
                        $sql .= ' ' . $andOr . ' (' . $searchField . ' ' . $operator . ' :f' . $searchField . $fKey . $vKey;
                    } else {
                        $sql .= ' OR ' . $searchField . ' ' . $operator . ' :f' . $searchField . $fKey . $vKey;
                    }

                    $this->bindParamsKeys[] = ':f' . $searchField . $fKey . $vKey;
                    $this->bindParamsValues[] = "'{$value}'";
                }

                $sql .= ')';
            }
        }

        return $sql;
    }

    /**
     * Prepare the SQL for a related search.
     *
     * @param array $searchCriteria
     * @param string $classname
     * @param string $andOr
     * @param int $fKey
     *
     * @return string
     */
    protected function prepareNestedSql(array $searchCriteria, string $classname, string $andOr, string $fKey) : string
    {
        $sql = '';
        $textFields = $this->getTextFields($classname);
        $nested = ' nested(';
        list($searchField, $operator, $searchValues) = $searchCriteria;
        $operator = $this->operators[$operator];

        if (trim($searchValues) !== '') {
            if ($searchValues == '%%') {
                $sql .= ' ' . $andOr . ' (' . $nested . '' . $searchField . ' IS NULL';
                $sql .= ' OR ' . $nested . '' . $searchField . ' = ""';

                if ($this->model->$searchField === 0) {
                    $sql .= ' OR ' . $nested . '' . $searchField . ' = 0';
                }

                $sql .= ')';
            } elseif ($searchValues == '$$') {
                $sql .= ' ' . $andOr . ' (' . $nested . '' . $searchField . ' IS NOT NULL';
                $sql .= ' OR ' . $nested . '' . $searchField . ' != ""';

                if ($this->model->$searchField === 0) {
                    $sql .= ' OR ' . $nested . '' . $searchField . ' ) != 0';
                }

                $sql .= ')';
            } else {
                if (strpos($searchValues, '|')) {
                    $searchValues = explode('|', $searchValues);
                } else {
                    $searchValues = [$searchValues];
                }

                foreach ($searchValues as $vKey => $value) {
                    if (preg_match('#^%[^%]+%|%[^%]+|[^%]+%$#i', $value)
                        || $value == '%%'
                    ) {
                        $operator = 'LIKE';
                    }

                    if (!$vKey) {
                        $sql .= ' ' . $andOr . ' (' . $nested . '' . $searchField . ') ' . $operator . ' :f' . $searchField . $fKey . $vKey;
                    } else {
                        $sql .= ' OR ' . $nested . '' . $searchField . ' ' . $operator . ' :f' . $searchField . $fKey . $vKey;
                    }

                    $this->bindParamsKeys[] = ':f' . $searchField . $fKey . $vKey;
                    $this->bindParamsValues[] = $value;
                }

                $sql .= ')';
            }
        }

        return $sql;
    }

    /**
     * Preparse the parameters to be used in the search.
     *
     * @return void
     */
    protected function prepareParams(array $unparsed) : void
    {
        $this->customSearchFields = array_key_exists('cparams', $unparsed) ? $this->parseSearchParameters($unparsed['cparams'])['mapped'] : [];
        $this->normalSearchFields = array_key_exists('params', $unparsed) ? $this->parseSearchParameters($unparsed['params'])['mapped'] : [];
    }

    /**
     * Parse the requested columns to be returned.
     *
     * @param string $columns
     *
     * @return void
     */
    protected function parseColumns(string $columns) : void
    {
        // Split the columns string into individual columns
        $columns = explode(',', $columns);

        foreach ($columns as &$column) {
            $column = preg_replace('/[^a-zA-_Z]/', '', $column);
            if (strpos($column, '.') === false) {
                $column = "{$column}";
            } else {
                $as = str_replace('.', '_', $column);
                $column = "{$column} {$as}";
            }
        }

        $this->columns = implode(', ', $columns);
    }

    /**
     * Based on the given relaitonship , add the relation array to the Resultset.
     *
     * @param  string $relationships
     * @param  Model $results
     *
     * @return array
     */
    public static function parseRelationShips(string $relationships, &$results) : array
    {
        $relationships = explode(',', $relationships);
        $newResults = [];
        if (!($results instanceof Model)) {
            throw new Exception(_('Result needs to be a Baka Model'));
        }
        $newResults = $results->toFullArray();
        foreach ($relationships as $relationship) {
            if ($results->$relationship) {
                $callRelationship = 'get' . ucfirst($relationship);
                $newResults[$relationship] = $results->$callRelationship();
            }
        }
        unset($results);
        return $newResults;
    }
}
