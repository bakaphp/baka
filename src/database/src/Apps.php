<?php
namespace Baka\Database;

class Apps extends Model
{
    /**
     *
     * @var integer
     */
    public $id;
    /**
     *
     * @var string
     */
    public $name;
    /**
     *
     * @var string
     */
    public $description;
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
        return 'apps';
    }
}
