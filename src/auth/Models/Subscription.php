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

    public int $plans_id;
    public int $users_id;
    public int $apps_id;
    public int $stripe_id;
    public int $company_id;
    public string $stripe_plan;
    public int $quantity;
    public string $trial_ends_at;
    public string $ends_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('subscription');
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->belongsTo('company_id', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'company']);
    }

    /**
     * Model validation.
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
     * Start a free trial to the system.
     *
     * @param  Companies $company
     *
     * @return Subscription
     */
    public static function startFreeTrial(Companies $company) : self
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
}
