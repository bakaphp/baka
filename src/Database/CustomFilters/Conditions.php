<?php

namespace Baka\Database\CustomFilters;

use Baka\Database\Model;

class Conditions extends Model
{
    public int $custom_filter_id;
    public int $position;
    public string $conditional;
    public string $value;

    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize() : void
    {
        $this->setSource('custom_filters_conditions');
        $this->belongsTo(
            'custom_filter_id',
            CustomFilters::class,
            'id',
            [
                'alias' => 'filter'
            ]
        );
    }
}
