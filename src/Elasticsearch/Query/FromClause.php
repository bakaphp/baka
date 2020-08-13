<?php
declare(strict_types=1);

namespace Baka\Elasticsearch\Query;

use Baka\Contracts\Database\ModelInterface;

class FromClause
{
    protected ModelInterface $model;
    protected string $source;

    /**
     * Constructor.
     *
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
        $this->source = $this->model->getSource();
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
                    $queryNodes[] = $this->getFromAlias() . '.' . $relation->getOptions()['alias'] . ' as ' . $elasticAlias;
                    $searchNodes[] = $options['alias'] . '.';
                    $replaceNodes[] = $elasticAlias . '.';
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
