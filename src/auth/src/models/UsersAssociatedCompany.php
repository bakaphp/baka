<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class UsersAssociatedCompany extends Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $users_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $company_id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $identify_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $user_active;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $user_role;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'company']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'users_associated_company';
    }
}
