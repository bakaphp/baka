<?php

namespace Baka\Test\Integration\Cashier;

use Baka\Http\Contracts\Api\ResponseTrait;
use Baka\Test\Support\Models\Users;
use Phalcon\Cashier\Traits\StripeWebhookHandlersTrait;
use Phalcon\Http\Response;
use PhalconUnitTestCase;

class WebhookTest extends PhalconUnitTestCase
{
    use StripeWebhookHandlersTrait;
    use ResponseTrait;

    public $response;

    /**
     * this runs before everyone.
     */
    protected function setUp() : void
    {
        parent::setUp();
        $this->response = new Response();
    }

    /**
     * Pending Payment.
     *
     * @return void
     */
    public function testPendingPayment()
    {
        $user = Users::findFirst(2);

        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $this->handleChargePending($payload);

        $this->assertTrue($result instanceof Response);
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

        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $this->handleChargeFailed($payload);

        $this->assertTrue($result instanceof Response);
    }

    /**
     * Successful Payment.
     *
     * @return void
     */
    public function testSucceededPayment()
    {
        $user = Users::findFirst(2);

        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $this->handleChargeSucceeded($payload);

        $this->assertTrue($result instanceof Response);
    }

    /**
     * Successful Payment.
     *
     * @return void
     */
    public function testSubscriptionUpdate()
    {
        $user = Users::findFirst(2);

        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'amount' => 500
                ]
            ]
        ];

        $result = $this->handleCustomerSubscriptionUpdated($payload);

        $this->assertTrue($result instanceof Response);
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

        $payload = [
            'data' => [
                'object' => [
                    'customer' => $user->stripe_id,
                    'trial_end' => $trialEnd
                ]
            ]
        ];

        $result = $this->handleCustomerSubscriptionTrialwillend($payload);

        $this->assertTrue($result instanceof Response);
    }
}
