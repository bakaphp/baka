<?php

namespace Baka\Database;

class SystemModules extends Model
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $slug;

    /**
     *
     * @var string
     */
    public $model_name;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $parents_id;

    /**
     *
     * @var integer
     */
    public $menu_order;

    /**
     *
     * @var integer
     */
    public $use_elastic;
    /**
     *
     * @var string
     */
    public $browse_fields;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'system_modules';
    }
}
