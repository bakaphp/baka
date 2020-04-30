<?php

namespace Baka\Blameable;

class AuditsDetails extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var string
     */
    public $id;

    /**
     *
     * @var string
     */
    public $audits_id;

    /**
     *
     * @var string
     */
    public $field_name;

    /**
     *
     * @var string
     */
    public $old_value;

    /**
    * @var string
    */
    public $old_value_text;

    /**
     * @var string
     */
    public $new_value;

    /**
     * @var string
     */
    public $new_value_text;

    /**
     * Init.
     *
     * @return void
     */
    public function initialize()
    {
        $this->belongsTo('audits_id', '\Baka\Blameable\Audits', 'id');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'audits_details';
    }
}
