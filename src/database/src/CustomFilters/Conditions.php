<?php

namespace Baka\Database\CustomFilters;

use Baka\Database\Model;
use Baka\Database\Contracts\HashTableTrait;

class Conditions extends Model
{
    /**
     * @var integer
     */
    public $custom_filter_id;

    /**
     * @var int
     */
    public $position;

    /**
     * @var string
     */
    public $conditional;

    /**
     * @var string
     */
    public $value;

    /**
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_filters_conditions';
    }

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->belongsTo('custom_filter_id', '\Baka\Database\CustomFilters\CustomFilters', 'id', ['alias' => 'filter']);
    }
}
