<?php

namespace Baka\Test\Integration\Cashier;

use Baka\Cashier\Subscription;
use Baka\Database\Apps;
use Baka\Test\Support\Models\Companies;
use Baka\Test\Support\Models\Users;
use Carbon\Carbon;
use PhalconUnitTestCase;
use Stripe\Token;

class CashierTest extends PhalconUnitTestCase
{
    /**
     * Tests.
     */
    public function testSubscriptionsCanBeCreatedAndUpdated()
    {
        $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is null',
            'order' => 'RAND()'
        ]);

        $company = Companies::findFirstOrFail([
            'order' => 'RAND()',
        ]);

        $apps = Apps::findFirstOrFail(1);

        //Create Subscription
        $user->newSubscription('main', 'monthly-10-1', $company, $apps)->create($this->getTestToken());

        //$this->assertEquals(1, count($user->subscriptions));
        $this->assertNotNull($user->subscription('main')->stripe_id);

        $this->assertTrue($user->subscribed('main'));
        $this->assertTrue($user->subscribedToPlan('monthly-10-1', 'main'));
        $this->assertFalse($user->subscribedToPlan('monthly-10-1', 'something'));
        $this->assertFalse($user->subscribedToPlan('monthly-10-2', 'main'));
        $this->assertTrue($user->subscribed('main', 'monthly-10-1'));
        $this->assertFalse($user->subscribed('main', 'monthly-10-2'));
        $this->assertTrue($user->subscription('main')->active());
        $this->assertFalse($user->subscription('main')->cancelled());
        $this->assertFalse($user->subscription('main')->onGracePeriod());

        //Cancel Subscription
        $subscription = $user->subscription('main');
        $subscription->cancel();

        $this->assertFalse($subscription->active());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());

        // Update current plan
        $subscription->swap('monthly-10-2');

        $this->assertEquals('monthly-10-2', $subscription->stripe_plan);
    }

    public function testCreatingSubscriptionWithTrial()
    {
        $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is null',
            'order' => 'RAND()'
        ]);

        $company = Companies::findFirstOrFail([
            'order' => 'RAND()',
        ]);
        $apps = Apps::findFirstOrFail(1);

        // Create Subscription
        $user->newSubscription('main', 'monthly-10-1', $company, $apps)
            ->trialDays(7)->create($this->getTestToken());

        $subscription = $user->subscription('main');

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->onTrial());
        $dt = Carbon::parse($subscription->trial_ends_at);
        $this->assertEquals(Carbon::today()->addDays(7)->day, $dt->day);

        // Cancel Subscription
        $subscription->cancel();

        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onGracePeriod());
    }

    public function testCreatingOneOffInvoices()
    {
        $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is not null',
            'order' => 'RAND()'
        ]);

        // Create Invoice
        $user->createAsStripeCustomer($this->getTestToken());
        $user->invoiceFor('Phalcon PHP Cashier', 1000);

        // Invoice Tests
        $invoice = $user->invoices()[0];
        $this->assertEquals('$10.00', $invoice->total());
        $this->assertEquals('Phalcon PHP Cashier', $invoice->invoiceItems()[0]->asStripeInvoiceItem()->description);
    }

    public function testRefunds()
    {
  $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is not null',
            'order' => 'RAND()'
        ]);
        // Create Invoice
        $user->createAsStripeCustomer($this->getTestToken());
        $invoice = $user->invoiceFor('Phalcon PHP Cashier', 1000);

        // Create the refund
        $refund = $user->refund($invoice->charge);

        // Refund Tests
        $this->assertEquals(1000, $refund->amount);
    }

    protected function getTestToken()
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => date('m', strtotime('+1 month')),
                'exp_year' => date('Y', strtotime('+1 year')),
                'cvc' => '123',
            ],
        ], ['api_key' => getenv('STRIPE_SECRET')])->id;
    }
}
