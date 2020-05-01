<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Exception;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class Subscription extends Model
{
    const FREE_TRIAL = 1;
    const DEFAULT_APP = 1;

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
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $plans_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $users_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $apps_id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $stripe_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $company_id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $stripe_plan;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $quantity;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $trial_ends_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $ends_at;

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
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'company']);
    }

    /**
     * Model validation
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();

        // Unique values
        $validator->add(
            'company_id',
            new Uniqueness([
                'model' => $this,
                'message' => _('This company already has an subscription.'),
            ])
        );

        return $this->validate($validator);
    }

    /**
     * Start a free trial to the system
     *
     * @param  Companies $company
     * @return Subscription
     */
    public static function startFreeTrial(Companies $company): Suscriptions
    {
        $subscription = new self();
        $subscription->plans_id = self::FREE_TRIAL;
        $subscription->users_id = $company->users_id;
        $subscription->apps_id = self::DEFAULT_APP;
        $subscription->stripe_id = '';
        $subscription->company_id = $company->getId();
        $subscription->stripe_plan = 'Free Trial';
        $subscription->quantity = 1;
        $subscription->trial_ends_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        $subscription->ends_at = date('Y-m-d H:i:s', strtotime('+30 days'));

        if (!$subscription->save()) {
            throw new Exception(current($subscription->getMessages()));
        }

        return $subscription;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'suscriptions';
    }
}
