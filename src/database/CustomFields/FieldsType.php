<?php

namespace Baka\Database\CustomFields;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Model;

class FieldsType extends Model
{
    use HashTableTrait;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $icon;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('custom_fields_types');
    }
}
