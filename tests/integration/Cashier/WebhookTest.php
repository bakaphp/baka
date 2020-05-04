<?php

use App\Models\Users;
use Phalcon\Cashier\Controllers\WebhookController;

class WebhookTest extends PhalconUnitTestCase
{
    /**
     * Pending Payment.
     *
     * @return void
     */
    public function testPendingPayment()
    {
        $user = Users::findFirst(2);
        $webhook = new WebHookController();
        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $webhook->handleChargePending($payload);

        $this->assertEquals('Webhook Handled', $result);
    }

    /**
     * Failed Payment.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function testFailedPayment()
    {
        $user = Users::findFirst(2);
        $webhook = new WebHookController();
        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $webhook->handleChargeFailed($payload);

        $this->assertEquals('Webhook Handled', $result);
    }

    /**
     * Successful Payment.
     *
     * @return void
     */
    public function testSucceededPayment()
    {
        $user = Users::findFirst(2);
        $webhook = new WebHookController();
        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $webhook->handleChargeSucceeded($payload);

        $this->assertEquals('Webhook Handled', $result);
    }

    /**
     * Successful Payment.
     *
     * @return void
     */
    public function testSubscriptionUpdate()
    {
        $user = Users::findFirst(2);
        $webhook = new WebHookController();
        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $webhook->handleCustomerSubscriptionUpdated($payload);

        $this->assertEquals('Webhook Handled', $result);
    }

    /**
     * Successful Payment.
     *
     * @return void
     */
    public function testFreeTrialEnding()
    {
        $user = Users::findFirst(2);
        $trialEnd = time();

        $webhook = new WebHookController();
        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'trial_end' => $trialEnd
                ]
            ]
        ];

        $result = $webhook->handleCustomerSubscriptionTrialwillend($payload);

        $this->assertEquals('Webhook Handled', $result);
    }
}
