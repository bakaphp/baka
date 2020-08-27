<?php

namespace Baka\Http\QueryParser;

use Baka\Elasticsearch\Query\FromClause;
use Baka\Support\Str;
use Phalcon\Mvc\ModelInterface;

/**
 * QueryParser translates a complex syntax query provided via an url in an string format to a SQL alike syntax.
 */
class QueryParser
{
    /**
     *  The external operators that will be used in different contexts.
     */
    const OPERATORS = [
        ':' => '=',
        '>' => '>',
        '<' => '<',
        '!' => '!',
    ];

    /**
     * Operators to be used with equalities.
     */
    const EQUALS = [
        '=' => '=',
        '>' => '>=',
        '<' => '<=',
        '!' => '<>',
    ];

    /**
     * Operators to be used with partial comparisons.
     */
    const LIKE = [
        '=' => 'LIKE',
        '!' => 'NOT LIKE',
    ];

    /**
     * Operators to be used with nullable comparisons.
     */
    const NULL = [
        '=' => 'IS NULL',
        '!' => 'IS NOT NULL',
    ];

    /**
     * Joiners to be used with comparisons.
     */
    const JOINERS = [
        '' => 'AND',
        ',' => 'AND',
        ';' => 'OR',
    ];

    const DEFAULT_JOINER = 'AND';

    const WILDCARDS = ['*', '%'];

    const MAX_LIMIT = 3000;

    const VALUES_DELIMITER = '|';

    const QUOTE_CHAR = '"';

    protected string $source;
    protected string $sourceAlias;

    protected ModelInterface $model;

    protected string $fields = '*';

    protected int $limit = 25;

    protected bool $withLimit = false;

    protected bool $overWriteLimit = false;

    protected int $page = 1;

    protected string $sort = 'id DESC';

    protected string $filters = '';

    protected array $notQueryableFields = [];

    protected ?string $queryFields = null;

    protected array $additionalQueryFields = [];

    /**
     * Constructor.
     *
     * @param string $source
     * @param array $params
     */
    public function __construct(ModelInterface $model, array $params)
    {
        $this->model = $model;
        $this->setSource($model->getSource());
        $this->setSort($params['sort'] ?? $this->sort);
        $this->setLimit($params['limit'] ?? $this->limit);
        $this->setPage($params['page'] ?? $this->page);
        $this->setFields($params['fields'] ?? $this->fields);

        //if empty default search 1 = 1
        $this->setQuery($params['q'] ?? '(1:1)');
    }

    /**
     * Set Additional query fields.
     *
     * @param array $additionalQueryFields
     *
     * @return void
     */
    public function setAdditionalQueryFields(array $additionalQueryFields)
    {
        $this->additionalQueryFields = $additionalQueryFields;
    }

    /**
     * Set main table.
     *
     * @param string $source
     *
     * @return void
     */
    public function setSource(string $source) : void
    {
        $this->sourceAlias = $source[0];
        $this->source = $source . ' as ' . $this->sourceAlias;
    }

    /**
     * Allow user to overwrite the limit settings.
     *
     * @return void
     */
    public function overWriteLimit() : void
    {
        $this->overWriteLimit = true;
    }

    /**
     * Set fields to be retrieved by this query.
     *
     * @param string $fields
     */
    public function setFields(string $fields) : void
    {
        $this->fields = $fields;
    }

    /**
     * Set sort for this query.
     *
     * @param string $sort
     */
    public function setSort(string $sort) : void
    {
        if ($sort) {
            $this->sort = str_replace('|', ' ', $sort);
        }
    }

    /**
     * Set page.
     *
     * @param int $page
     *
     * @return voie
     */
    public function setPage(int $page) : void
    {
        $this->page = $page;
    }

    /**
     * Set limit for this query.
     *
     * @param int $limit
     */
    public function setLimit(int $limit) : void
    {
        $this->withLimit = true;

        if (!$this->overWriteLimit && $limit > 200) {
            $this->limit = 200;
        } else {
            $this->limit = $limit;
        }
    }

    /**
     * Set the query which will be parsed.
     *
     * @param string $query
     */
    public function setQuery(string $query) : void
    {
        if (!$query) {
            return;
        }

        $this->queryFields = $query;
    }

    /**
     * Returns limit.
     *
     * @return int
     */
    public function getLimit() : int
    {
        return  $this->limit;
    }

    /**
     * Return the filters to be applied.
     *
     * @return string
     */
    public function getFilters() : string
    {
        return  $this->filters;
    }

    /**
     * Returns wether the is a valid expected operator or not.
     *
     * @param string $operator The operator to validate
     *
     * @return bool
     */
    public static function isAValidJoiner(string $joiner) : bool
    {
        return in_array($joiner, array_keys(self::JOINERS));
    }

    /**
     * Parse the query to an complete valid SQL statement.
     *
     * @return string
     */
    public function getParsedQuery() : string
    {
        if ($this->queryFields) {
            $this->appendFilters($this->parseQuery($this->queryFields));
        }

        $limit = $this->withLimit ? " LIMIT {$this->getOffset()},  {$this->getLimit()}" : '';

        $fromClause = new FromClause($this->model);
        $fromClauseParsed = $fromClause->get();

        $this->filters = str_replace($fromClauseParsed['searchNodes'], $fromClauseParsed['replaceNodes'], $this->filters);
        $this->source .= implode(', ', $fromClauseParsed['nodes']);

        $sql = "SELECT {$this->fields} FROM {$this->source} {$this->filters} ORDER BY {$this->sort} {$limit}";

        $this->filters = ''; //clean up the filter
        return $sql;
    }

    /**
     * Returns the page of the current query.
     *
     * @return int
     */
    protected function getPage() : int
    {
        return $this->page;
    }

    /**
     * Get offset.
     */
    protected function getOffset() : int
    {
        return round(($this->page - 1) * $this->limit);
    }

    /**
     * Parse the query to an valid SQL filtering statement.
     *
     * @param string $query
     *
     * @return string
     */
    protected function parseQuery(string $query) : string
    {
        $parser = new NestedParenthesesParser();
        $parser->setAdditionalQueryFields($this->additionalQueryFields);
        $comparisons = $parser->parse($query);

        if (!$comparisons) {
            return '';
        }

        return  $this->transformNestedComparisons($comparisons);
    }

    /**
     * Convert nested property into sql comparisons.
     */
    protected function transformNestedComparisons(array &$comparisons) : string
    {
        $operatorsPattern = '#(' . implode('|', array_keys(self::OPERATORS)) . ')#';
        $sql = '';
        $joiner = '';
        foreach ($comparisons as $index => $comparison) {
            if (count($comparison) != count($comparison, COUNT_RECURSIVE)) {
                $sqlComparison = "{$this->transformNestedComparisons($comparison)}";
            } else {
                $joiner = isset($comparisons[(int) $index - 1]) ? self::JOINERS[$comparisons[(int) $index - 1]['joiner']] : self::DEFAULT_JOINER;

                list(
                    $parsedComparison['field'],
                    $parsedComparison['operator'],
                    $parsedComparison['values']
                    ) = preg_split($operatorsPattern, $comparison['comparison'], 2, PREG_SPLIT_DELIM_CAPTURE);

                $sqlComparison = $this->parseComparison($parsedComparison);
            }

            $sql = $sql ? "{$sql} {$joiner} ({$sqlComparison})" : "({$sqlComparison})";
        }

        return $sql;
    }

    /**
     * Parse an array of comparison elements to string.
     *
     * @param array $comparison The array of comparison elements
     *
     * @return string
     */
    protected function parseComparison(array $comparison) : string
    {
        $field = $this->parseComparisonField($comparison['field']);

        $values = self::parseComparisonValues($comparison['values']);

        $operator = self::parseComparisonOperator($comparison['operator']);

        $comparison = self::buildComparison($field, $operator, array_shift($values));

        foreach ($values as $value) {
            $comparison .= ' OR ' . self::buildComparison($field, $operator, $value);
        }

        return $comparison;
    }

    /**
     * Creates a valid comparison from the provided field, operator and value.
     *
     * @param string $field The field to compare
     * @param string $operator The operator that will define the type of comparison
     * @param string $value The value with which the field will be compared
     *
     * @return string
     */
    protected static function buildComparison(string $field, string $operator, string $value) : string
    {
        if ('~' == $value) {
            $operator = self::getOperatorFromScope($operator, self::NULL);

            return "{$field} {$operator}";
        }

        $operator = self::getOperatorFromScope($operator, self::containsWildcards($value) ? self::LIKE : self::EQUALS);

        $value = mb_strtolower($value);
        $value = ctype_digit($value) ? $value : "'{$value}'";

        return "{$field} {$operator} {$value}";
    }

    /**
     * Parse a field to be used in a comparison.
     *
     * @param string $field The field that will be parsed
     *
     * @return string A valid format of how the provided field can be used in the comparison
     */
    protected function parseComparisonField(string $field) : string
    {
        if (!$this->isAQueryableField($field)) {
            throw new NotQueryableFieldException($field);
        }

        //return mb_strpos($field, '.') ? "{$field}" : $field;
        return $field;
    }

    /**
     * Parse the provided operator to one that can be used internally.
     *
     * @param string $operator
     *
     * @return string
     */
    protected static function parseComparisonOperator(string $operator) : string
    {
        $operator = self::OPERATORS[$operator];

        if ($operator) {
            return $operator;
        }

        throw new UnknownOperatorException($operator);
    }

    /**
     * Parse the values string to an exploded array of values.
     *
     * @param string $values Values string joined by a delimiter
     *
     * @return array The array of exploded values
     */
    protected static function parseComparisonValues(string $values) : array
    {
        $values = explode(self::VALUES_DELIMITER, str_replace(self::QUOTE_CHAR, '', $values));
        if ($values) {
            return  $values;
        }

        throw new NotValuesProvidedException();
    }

    /**
     * Append filters to the existent ones.
     *
     * @param string $filters
     */
    protected function appendFilters(string $filters) : void
    {
        $this->filters .= $this->filters ? ' ' . self::DEFAULT_JOINER . " ({$filters})" : " WHERE {$filters}";
    }

    /**
     * Returns wether this query  has filters or not.
     *
     * @return bool
     */
    protected function hasFilters() : bool
    {
        return (bool) $this->filters;
    }

    /**
     * Returns wether the provided field can be queried or not.
     *
     * @param string $field The field to validate
     *
     * @return bool
     */
    protected function isAQueryableField(string $field) : bool
    {
        return !in_array($field, $this->notQueryableFields);
    }

    /**
     * Returns wether the provided string has a wildcard or not.
     *
     * @param string $string The string to evaluate
     *
     * @return bool
     */
    protected static function containsWildcards(string $string) : bool
    {
        foreach (self::WILDCARDS as $wildcard) {
            if (Str::contains($string, $wildcard)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find an operator on the provided scope.
     *
     * Throws an OutOfScopeOperatorException if not found
     *
     * @param string $operator
     * @param array $scope
     *
     * @return string
     */
    protected static function getOperatorFromScope(string $operator, array $scope) : string
    {
        $validOperator = $scope[$operator];

        if ($validOperator) {
            return $validOperator;
        }

        throw new OutOfScopeOperatorException($operator);
    }
}
