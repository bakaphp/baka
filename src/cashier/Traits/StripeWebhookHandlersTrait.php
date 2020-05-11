<?php

namespace Phalcon\Cashier\Traits;

use Baka\Auth\Models\Users;
use Exception;
use Phalcon\Http\Response;

/**
 * Trait WebhookHandlers.
 *
 * @package Phalcon\Cashier\Traits
 *
 * @property Users $user
 * @property Subscriptions $subscriptions
 *
 */
trait StripeWebhookHandlersTrait
{
    /**
     * Handle stripe webhoook calls.
     *
     * @return Response
     */
    public function handleWebhook() : Response
    {
        //we cant processs if we dont find the stripe header
        if (!$this->request->hasHeader('Stripe-Signature')) {
            throw new Exception('Route not found for this call');
        }

        $request = $this->request->getPost();

        if (empty($request)) {
            $request = $this->request->getJsonRawBody(true);
        }
        $type = str_replace('.', '', ucwords(str_replace('_', '', $request['type']), '.'));
        $method = 'handle' . $type;

        $payloadContent = json_encode($request);
        $this->log->info("Webhook Handler Method: {$method} \n");
        $this->log->info("Payload: {$payloadContent} \n");

        if (method_exists($this, $method)) {
            return $this->{$method}($request);
        } else {
            return $this->response(['Missing Method to Handled']);
        }
    }

    /**
     * Handle customer subscription updated.
     *
     * @param  array $payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            $subscription = $user->getAllSubscriptions('stripe_id =' . $payload['data']['object']['id']);

            if (is_object($subscription)) {
                $subscription->markAsCancelled();
            }
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle customer subscription free trial ending.
     *
     * @param  array $payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionTrialwillend(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle customer updated.
     *
     * @param  array $payload
     *
     * @return Response
     */
    protected function handleCustomerUpdated(array $payload, ?string $method = null) : Response
    {
        if ($user = Users::findFirstByStripeId($payload['data']['object']['id'])) {
            $user->updateCardFromStripe();
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle customer source deleted.
     *
     * @param  array $payload
     *
     * @return Response
     */
    protected function handleCustomerSourceDeleted(array $payload, ?string $method = null) : Response
    {
        if ($user = Users::findFirstByStripeId($payload['data']['object']['customer'])) {
            $user->updateCardFromStripe();
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle deleted customer.
     *
     * @param  array $payload
     *
     * @return Response
     */
    protected function handleCustomerDeleted(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['id']);
        if ($user) {
            foreach ($user->subscriptions as $subscription) {
                $subscription->skipTrial()->markAsCancelled();
            }

            $user->stripe_id = null;
            $user->trial_ends_at = null;
            $user->card_brand = null;
            $user->card_last_four = null;
            $user->update();
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle sucessfull payment.
     *
     * @todo send email
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleChargeSucceeded(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle bad payment.
     *
     * @todo send email
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleChargeFailed(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle looking for refund.
     *
     * @todo send email
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleChargeDisputeCreated(array $payload, ?string $method = null) : Response
    {
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle pending payments.
     *
     * @todo send email
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleChargePending(array $payload, ?string $method = null) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Send webhook related emails to user.
     *
     * @param Users $user
     * @param array $payload
     *
     * @return void
     */
    protected static function sendWebhookResponseEmail(Users $user, array $payload, ?string $method = null) : void
    {
        // $subject = '';
        // $content = '';
        // Di::getDefault()->getMail()
        //     ->to($user->email)
        //     ->subject($subject)
        //     ->content($content)
        //     ->sendNow();
    }
}
