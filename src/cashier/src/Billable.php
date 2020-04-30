<?php

namespace Phalcon\Cashier;

use Phalcon\Di\FactoryDefault;
use Exception;
use Carbon\Carbon;
use InvalidArgumentException;
use Stripe\Token as StripeToken;
use Stripe\Charge as StripeCharge;
use Stripe\Refund as StripeRefund;
use Stripe\Invoice as StripeInvoice;
use Stripe\Customer as StripeCustomer;
use Stripe\InvoiceItem as StripeInvoiceItem;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Phalcon\Mvc\Model;
use Baka\Database\Apps;

trait Billable
{
    /**
     * The Stripe API key.
     *
     * @var string
     */
    protected static $stripeKey;

    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @param  int   $amount
     * @param  array $options
     * @return \Stripe\Charge
     *
     * @throws \Stripe\Error\Card
     */
    public function charge($amount, array $options = [])
    {
        $options = array_merge([
            'currency' => $this->preferredCurrency(),
        ], $options);

        $options['amount'] = $amount;
        if (!array_key_exists('source', $options) && $this->stripe_id) {
            $options['customer'] = $this->stripe_id;
        }
        if (!array_key_exists('source', $options) && !array_key_exists('customer', $options)) {
            throw new InvalidArgumentException('No payment source provided.');
        }
        return StripeCharge::create($options, ['api_key' => $this->getStripeKey()]);
    }

    /**
     * Refund a customer for a charge.
     *
     * @param  string $charge
     * @param  array  $options
     * @return \Stripe\Charge
     *
     * @throws \Stripe\Error\Refund
     */
    public function refund($charge, array $options = [])
    {
        $options['charge'] = $charge;

        return StripeRefund::create($options, ['api_key' => $this->getStripeKey()]);
    }

    /**
     * Determines if the customer currently has a card on file.
     *
     * @return bool
     */
    public function hasCardOnFile()
    {
        return (bool)$this->card_brand;
    }

    /**
     * Add an invoice item to the customer's upcoming invoice.
     *
     * @param  string  $description
     * @param  int  $amount
     * @param  array  $options
     * @return \Stripe\InvoiceItem
     *
     * @throws \InvalidArgumentException
     */
    public function tab($description, $amount, array $options = [])
    {
        if (!$this->stripe_id) {
            throw new InvalidArgumentException(class_basename($this) . ' is not a Stripe customer. See the createAsStripeCustomer method.');
        }
        $options = array_merge([
            'customer' => $this->stripe_id,
            'amount' => $amount,
            'currency' => $this->preferredCurrency(),
            'description' => $description,
        ], $options);

        return StripeInvoiceItem::create(
            $options,
            ['api_key' => $this->getStripeKey()]
        );
    }

    /**
     * Invoice the customer for the given amount and generate an invoice immediately.
     *
     * @param  string  $description
     * @param  int  $amount
     * @param  array  $options
     * @return \Laravel\Cashier\Invoice|bool
     */
    public function invoiceFor($description, $amount, array $options = [])
    {
        $this->tab($description, $amount, $options);
        return $this->invoice();
    }

    /**
     * Begin creating a new subscription.
     *
     * @param string $subscription
     * @param string $plan
     */
    public function newSubscription($subscription, $plan, Model $company, Apps $apps)
    {
        return new SubscriptionBuilder($this, $subscription, $plan, $company, $apps);
    }

    /**
     * Determine if the user is on trial.
     *
     * @param  string      $subscription
     * @param  string|null $plan
     * @return bool
     */
    public function onTrial($subscription = 'default', $plan = null)
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($subscription);

        if (is_null($plan)) {
            return $subscription && $subscription->onTrial();
        }

        return $subscription && $subscription->onTrial() &&
        $subscription->stripe_plan === $plan;
    }

    /**
     * Determine if the user is on a "generic" trial at the user level.
     *
     * @return bool
     */
    public function onGenericTrial()
    {
        $trialEndsAt = new \DateTime($this->trial_ends_at);

        return $this->trial_ends_at && Carbon::now()->lt(Carbon::instance($trialEndsAt));
    }

    /**
     * Determine if the user has a given subscription.
     *
     * @param  string      $subscription
     * @param  string|null $plan
     * @return bool
     */
    public function subscribed($subscription = 'default', $plan = null)
    {
        $subscription = $this->subscription($subscription);

        if (is_null($subscription)) {
            return false;
        }

        if (is_null($plan)) {
            return $subscription->valid();
        }

        return $subscription->valid() && $subscription->stripe_plan === $plan;
    }

    /**
     * Get a subscription instance by name.
     *
     * @param string $subscription
     */
    public function subscription($subscription = 'default')
    {
        $subscriptions = $this->subscriptions();

        foreach ($subscriptions as $object) {
            if ($object->name === $subscription) {
                return $object;
            }
        }
        return null;
    }

    /**
     * Get all of the subscriptions for the user.
     */
    public function subscriptions()
    {
        $this->hasMany(
            'id',
            Subscription::class,
            'user_id',
            [
                'alias' => 'subscriptions',
                'params' => ['order' => 'id DESC']
            ]
        );
        return $this->getRelated('subscriptions');
    }

    /**
     * Invoice the billable entity outside of regular billing cycle.
     *
     * @return StripeInvoice|bool
     */
    public function invoice()
    {
        if ($this->stripe_id) {
            try {
                return StripeInvoice::create(['customer' => $this->stripe_id], $this->getStripeKey())->pay();
            } catch (StripeErrorInvalidRequest $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the entity's upcoming invoice.
     */
    public function upcomingInvoice()
    {
        try {
            $stripeInvoice = StripeInvoice::upcoming(
                ['customer' => $this->stripe_id],
                ['api_key' => $this->getStripeKey()]
            );

            return new Invoice($this, $stripeInvoice);
        } catch (StripeErrorInvalidRequest $e) {
        }
    }

    /**
     * Find an invoice by ID.
     *
     * @param string $id
     */
    public function findInvoice($id)
    {
        try {
            $stripeInvoice = StripeInvoice::retrieve($id, $this->getStripeKey());

            $stripeInvoice->lines = StripeInvoice::retrieve($id, $this->getStripeKey())
                        ->lines
                        ->all(['limit' => 1000]);
            return new Invoice($this, $stripeInvoice);
        } catch (Exception $e) {
        }
    }

    /**
     * Find an invoice or throw a 404 error.
     *
     * @param string $id
     */
    public function findInvoiceOrFail($id)
    {
        $invoice = $this->findInvoice($id);

        if (is_null($invoice)) {
            throw new NotFoundHttpException;
        }

        if ($invoice->customer !== $this->stripe_id) {
            throw new AccessDeniedHttpException;
        }

        return $invoice;
    }

    /**
     * Create an invoice download Response.
     *
     * @param string $id
     * @param array  $data
     * @param string $storagePath
     * @todo
     */
    public function downloadInvoice($id, array $data, $storagePath = null)
    {
    }

    /**
     * Get a collection of the entity's invoices.
     *
     * @param bool  $includePending
     * @param array $parameters
     */
    public function invoices($includePending = false, $parameters = [])
    {
        $invoices = [];
        $parameters = array_merge(['limit' => 24], $parameters);
        $stripeInvoices = $this->asStripeCustomer()->invoices($parameters);

        // Here we will loop through the Stripe invoices and create our own custom Invoice
        // instances that have more helper methods and are generally more convenient to
        // work with than the plain Stripe objects are. Then, we'll return the array.
        if (!is_null($stripeInvoices)) {
            foreach ($stripeInvoices->data as $invoice) {
                if ($invoice->paid || $includePending) {
                    $invoices[] = new Invoice($this, $invoice);
                }
            }
        }
        return $invoices;
    }

    /**
     * Get an array of the entity's invoices.
     *
     * @param array $parameters
     */
    public function invoicesIncludingPending(array $parameters = [])
    {
        return $this->invoices(true, $parameters);
    }

    /**
    * Get a collection of the entity's cards.
    *
    * @param  array  $parameters
    * @return array
    */
    public function cards($parameters = [])
    {
        $cards = [];
        $parameters = array_merge(['limit' => 24], $parameters);
        $stripeCards = $this->asStripeCustomer()->sources->all(
            ['object' => 'card'] + $parameters
        );

        if (!is_null($stripeCards)) {
            foreach ($stripeCards->data as $card) {
                $cards[] = new Card($this, $card);
            }
        }

        return $cards;
    }

    /**
     * Get the default card for the entity.
     *
     * @return \Stripe\Card|null
     */
    public function defaultCard()
    {
        $customer = $this->asStripeCustomer();
        foreach ($customer->sources->data as $card) {
            if ($card->id === $customer->default_source) {
                return $card;
            }
        }
    }

    /**
     * Update customer's credit card.
     *
     * @param  string $token
     * @return void
     */
    public function updateCard($token)
    {
        $customer = $this->asStripeCustomer();

        $token = StripeToken::retrieve($token, ['api_key' => $this->getStripeKey()]);

        // If the given token already has the card as their default source, we can just
        // bail out of the method now. We don't need to keep adding the same card to
        // the user's account each time we go through this particular method call.
        if ($token->card->id === $customer->default_source) {
            return;
        }

        $card = $customer->sources->create(['source' => $token]);

        $customer->default_source = $card->id;

        $customer->save();

        // Next, we will get the default source for this user so we can update the last
        // four digits and the card brand on this user record in the database, which
        // is convenient when displaying on the front-end when updating the cards.
        $source = $customer->default_source
            ? $customer->sources->retrieve($customer->default_source)
            : null;

        $this->fillCardDetails($source);

        $this->save();
    }

    /**
     * Synchronises the customer's card from Stripe back into the database.
     *
     * @return $this
     */
    public function updateCardFromStripe()
    {
        $defaultCard = $this->defaultCard();
        if ($defaultCard) {
            $this->fillCardDetails($defaultCard)->save();
        } else {
            $this->card_brand = null;
            $this->card_last_four = null;
            $this->update();
        }
        return $this;
    }

    /**
     * Fills the model's properties with the source from Stripe.
     *
     * @param  \Stripe\Card|\Stripe\BankAccount|null  $card
     * @return $this
     */
    protected function fillCardDetails($card)
    {
        if ($card instanceof StripeCard) {
            $this->card_brand = $card->brand;
            $this->card_last_four = $card->last4;
        } elseif ($card instanceof StripeBankAccount) {
            $this->card_brand = 'Bank Account';
            $this->card_last_four = $card->last4;
        }
        return $this;
    }

    /**
    * Deletes the entity's cards.
    *
    * @return void
    */
    public function deleteCards()
    {
        foreach ($this->cards() as $card) {
            $card->delete();
        }

        $this->updateCardFromStripe();
    }

    /**
     * Apply a coupon to the billable entity.
     *
     * @param  string $coupon
     * @return void
     */
    public function applyCoupon($coupon)
    {
        $customer = $this->asStripeCustomer();

        $customer->coupon = $coupon;

        $customer->save();
    }

    /**
     * Determine if the user is actively subscribed to one of the given plans.
     *
     * @param  array|string $plans
     * @param  string       $subscription
     * @return bool
     */
    public function subscribedToPlan($plans, $subscription = 'default')
    {
        $subscription = $this->subscription($subscription);

        if (!$subscription || !$subscription->valid()) {
            return false;
        }

        foreach ((array) $plans as $plan) {
            if ($subscription->stripe_plan === $plan) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the entity is on the given plan.
     *
     * @param  string $plan
     * @return bool
     */
    public function onPlan($plan)
    {
        return !is_null(
            $this->subscriptions->first(
                function ($key, $value) use ($plan) {
                    return $value->stripe_plan === $plan && $value->valid();
                }
            )
        );
    }

    /**
     * Determine if the entity has a Stripe customer ID.
     *
     * @return bool
     */
    public function hasStripeId()
    {
        return !is_null($this->stripe_id);
    }

    /**
     * Create a Stripe customer for the given user.
     *
     * @param  string $token
     * @param  array  $options
     * @return StripeCustomer
     */
    public function createAsStripeCustomer($token, array $options = [])
    {
        $options = array_key_exists('email', $options)
            ? $options : array_merge($options, ['email' => $this->email]);

        // Here we will create the customer instance on Stripe and store the ID of the
        // user from Stripe. This ID will correspond with the Stripe user instances
        // and allow us to retrieve users from Stripe later when we need to work.
        $customer = StripeCustomer::create(
            $options,
            $this->getStripeKey()
        );

        $this->stripe_id = $customer->id;

        $this->save();

        // Next we will add the credit card to the user's account on Stripe using this
        // token that was provided to this method. This will allow us to bill users
        // when they subscribe to plans or we need to do one-off charges on them.
        if (!is_null($token)) {
            $this->updateCard($token);
        }

        return $customer;
    }

    /**
     * Get the Stripe customer for the user.
     *
     * @return \Stripe\Customer
     */
    public function asStripeCustomer()
    {
        return StripeCustomer::retrieve($this->stripe_id, $this->getStripeKey());
    }

    /**
     * Get the Stripe supported currency used by the entity.
     *
     * @return string
     */
    public function preferredCurrency()
    {
        return Cashier::usesCurrency();
    }

    /**
     * Get the tax percentage to apply to the subscription.
     *
     * @return int
     */
    public function taxPercentage()
    {
        return 0;
    }

    /**
     * Get the Stripe API key.
     *
     * @return string
     */
    public static function getStripeKey()
    {
        if (static::$stripeKey) {
            return static::$stripeKey;
        }
        $di = FactoryDefault::getDefault();
        $stripe = $di->getConfig()->stripe;

        return $stripe->secretKey ?: getenv('STRIPE_SECRET');
    }

    /**
     * Set the Stripe API key.
     *
     * @param  string $key
     * @return void
     */
    public static function setStripeKey($key)
    {
        static::$stripeKey = $key;
    }

    /**
     * @link https://stripe.com/docs/api/php#create_card_token
     * @param $option
     * @return bool
     */
    public function createCardToken($option)
    {
        $object = StripToken::create($option, ['api_key' => $this->getStripeKey()]);
        if (is_object($object)) {
            $token = $object->__toArray(true);
            return $token['id'] ?: false;
        }
        return false;
    }

    /**
     * Update default payment method with new card.
     * @param string $customerId
     * @param string $token
     * @return StripeCustomer
     */
    public function updatePaymentMethod(string $customerId, string $token)
    {
        $customer = StripeCustomer::update($customerId, ['source' => $token], $this->getStripeKey());

        if (is_object($customer)) {
            return $customer;
        }
        return false;
    }

    /**
     * Create a new Invoice Item.
     * @param array $data Stripe Invoice Item data
     */
    public function createInvoiceItem(array $data)
    {
        $invoiceItem = StripeInvoiceItem::create($data, $this->getStripeKey());

        if (is_object($invoiceItem)) {
            return $invoiceItem;
        }

        return false;
    }

    /**
     * Create and send new Invoice to a customer.
     * @param string $customerId Stripe customer id
     * @param array $options
     */
    public function sendNewInvoice(string $customerId, array $options)
    {
        $invoice = StripeInvoice::create([
            'customer' => $customerId,
            'billing' => isset($options['billing']) ? $options['billing'] : 'send_invoice',
            'days_until_due' => isset($options['days_until_due']) ? $options['days_until_due'] : 30,
        ], $this->getStripeKey());

        if (is_object($invoice)) {
            //Send invoice email to user
            if ($invoice->sendInvoice()) {
                return $invoice;
            }
        }

        return false;
    }
}
