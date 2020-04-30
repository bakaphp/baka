<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Exception;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Companies extends Model
{
    const DEFAULT_COMPANY = 'DefaulCompany';

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $profile_image;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $website;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $users_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $updated_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->hasMany('id', 'Baka\Auth\Models\CompanySettings', 'id', ['alias' => 'settings']);
    }

    /**
     * Model validation
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'name',
            new PresenceOf([
                'model' => $this,
                'required' => true,
            ])
        );

        return $this->validate($validator);
    }

    /**
     * Register a company given a user and name
     *
     * @param  Users  $user
     * @param  string $name
     * @return Companies
     */
    public static function register(Users $user, string $name): Companies
    {
        $company = new self();
        $company->name = $name;
        $company->users_id = $user->getId();

        if (!$company->save()) {
            throw new Exception(current($company->getMessages()));
        }

        return $company;
    }

    /**
     * After creating the company
     *
     * @return void
     */
    public function afterCreate()
    {
        //setup the user notificatoin setting
        $companySettings = new CompanySettings();
        $companySettings->company_id = $this->getId();
        $companySettings->name = 'notifications';
        $companySettings->value = $this->user->email;
        if (!$companySettings->save()) {
            throw new Exception(current($companySettings->getMessages()));
        }

        //multi user asociation
        $usersAssociatedCompany = new UsersAssociatedCompany();
        $usersAssociatedCompany->users_id = $this->user->getId();
        $usersAssociatedCompany->company_id = $this->getId();
        $usersAssociatedCompany->identify_id = $this->user->getId();
        $usersAssociatedCompany->user_active = 1;
        $usersAssociatedCompany->user_role = 'admin';
        if (!$usersAssociatedCompany->save()) {
            throw new Exception(current($usersAssociatedCompany->getMessages()));
        }

        //now that we setup de company and associated with the user we need to setup this as its default company
        if (!UserConfig::findFirst(['conditions' => 'users_id = ?0 and name = ?1', 'bind' => [$this->user->getId(), self::DEFAULT_COMPANY]])) {
            $userConfig = new UserConfig();
            $userConfig->users_id = $this->user->getId();
            $userConfig->name = self::DEFAULT_COMPANY;
            $userConfig->value = $this->getId();

            if (!$userConfig->save()) {
                throw new Exception(current($userConfig->getMessages()));
            }
        }
    }

    /**
     * Get the default company the users has selected
     *
     * @param  Users  $user
     * @return Companies
     */
    public static function getDefaultByUser(Users $user): Companies
    {
        //verify the user has a default company
        $defaultCompany = UserConfig::findFirst([
            'conditions' => 'users_id = ?0 and name = ?1',
            'bind' => [$user->getId(), self::DEFAULT_COMPANY],
        ]);

        //found it
        if ($defaultCompany) {
            return self::findFirst($defaultCompany->value);
        }

        //second try
        $defaultCompany = UsersAssociatedCompany::findFirst([
            'conditions' => 'users_id = ?0 and user_active =?1',
            'bind' => [$user->getId(), 1],
        ]);

        if ($defaultCompany) {
            return self::findFirst($defaultCompany->company_id);
        }

        throw new Exception(_("User doesn't have an active company"));
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'companies';
    }
}
