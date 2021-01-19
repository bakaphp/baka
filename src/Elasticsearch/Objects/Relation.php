<?php

declare(strict_types=1);

namespace Baka\Elasticsearch\Objects;

class Relation
{
    protected array $options;
    protected string $name;

    /**
     * __construct.
     *
     * @param  string $index
     * @param  array $options
     *
     * @return void
     */
    public function __construct(string $index, array $options)
    {
        $this->name = $index;
        $this->options = $options;
    }

    /**
     * getOptions.
     *
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }
}
