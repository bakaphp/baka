<?php

namespace Baka\Blameable;

class Audits extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var string
     */
    public $id;

    /**
     *
     * @var int
     */
    public $users_id;

    /**
     *
     * @var string
     */
    public $entity_id;

    /**
     *
     * @var string
     */
    public $model_name;

    /**
     *
     * @var string
     */
    public $ip;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     * add the relationships.
     *
     * @return void
     */
    public function initialize()
    {
        $this->hasMany('id', '\Baka\Blameable\AuditsDetails', 'audits_id', ['alias' => 'details']);
        $this->belongsTo('users_id', '\Baka\Auth\Users', 'id', ['alias' => 'user']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'audits';
    }

    /**
     * Get custom fields.
     *
     * @param mixed $records
     * @return void
     */
    public function getCustomFields($records)
    {
        return $records;
    }
}
