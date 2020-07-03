<?php

declare(strict_types=1);

namespace Baka\Cashier\Controllers;

use Baka\Http\Api\BaseController;
use Baka\Contracts\Cashier\StripeWebhookHandlersTrait;

/**
 * Class PaymentsController.
 *
 * Class to handle payment webhook from our cashier library
 *
 * @package Gewaer\Api\Controllers
 *
 * @property Log $log
 *
 */
class WebhookController extends BaseController
{
    use StripeWebhookHandlersTrait;
}
