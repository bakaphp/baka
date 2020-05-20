<?php

namespace Baka\Database\CustomFields;

use Baka\Database\Model;
use Baka\Database\Contracts\HashTableTrait;

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
     * Returns the name of the table associated to the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields_types';
    }
}
