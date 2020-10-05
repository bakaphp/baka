<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Query;

use Baka\Contracts\Database\ModelInterface;
use Baka\Support\Str;

class FromClause
{
    protected ModelInterface $model;
    protected string $source;
    protected ?string $whereClause;
    protected array $whereClauseCleanup = ['AND', 'OR', 'WHERE'];

    /**
     * Constructor.
     *
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model, ?string $whereClause = null)
    {
        $this->model = $model;
        $this->source = $this->model->getSource();
        $this->whereClause = $whereClause ?? '';
    }

    /**
     * Get the from with alias.
     *
     * leads -> leas as l
     * we need this for the use of elasticSQL AWS
     *
     * @return void
     */
    public function getFromString() : string
    {
        return  $this->source . ' as ' . $this->getFromAlias();
    }

    /**
     * Get the from alias initials.
     *
     * Example:
     * leads -> l
     *
     * @return string
     */
    public function getFromAlias() : string
    {
        return $this->source[0];
    }

    /**
     * Get the relationship join nodes.
     *
     * @return void
     */
    public function get() : array
    {
        $class = get_class($this->model);

        $relationShips = $this->model->getModelsManager()->getRelations($class);
        $queryNodes = [null]; //add 1 element to force , at the start
        $searchNodes = [];
        $replaceNodes = [];

        /**
         * @todo cache the relationships
         */
        if (count($relationShips) > 0) {
            foreach ($relationShips as $relation) {
                $options = $relation->getOptions();
                $index = isset($options['elasticIndex']) && (int) $options['elasticIndex'] == 0 ? false : true;

                if ($index) {
                    $elasticAlias = $options['elasticAlias'] ?? $options['alias'][0];

                    /**
                     * if we find the alias in the where clause we add it to the table selection
                     * We also clean up some keywords to avoid collision and false positive
                     * example FROM leads as l , l.users as u.
                     */
                    if (Str::contains(str_replace($this->whereClauseCleanup, '', $this->whereClause), $elasticAlias)) {
                        //relationship we index them in lowercase
                        $relationAlias = strtolower($relation->getOptions()['alias']);
                        $queryNodes[] = $this->getFromAlias() . '.' . $relationAlias . ' as ' . $elasticAlias;
                        $searchNodes[] = $options['alias'] . '.';
                        $replaceNodes[] = $elasticAlias . '.';
                    }
                }
            }

            return [
                'searchNodes' => $searchNodes,
                'replaceNodes' => $replaceNodes,
                'nodes' => $queryNodes
            ];
        }

        return [];
    }
}
