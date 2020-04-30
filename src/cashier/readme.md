## Build Status and Join chats us:

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bakaphp/cashier/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/cashier/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bakaphp/cashier/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/cashier/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/bakaphp/cashier/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/cashier/build-status/master)

## Introduction

Phalcon Cashier provides an expressive, fluent interface to [Stripe's](https://stripe.com) subscription billing services. It handles almost all of the boilerplate subscription billing code you are dreading writing. In addition to basic subscription management, Cashier can handle coupons, swapping subscription, subscription "quantities", cancellation grace periods, and even generate invoice PDFs.

## Test Setup
You will need to set the following details locally and on your Stripe account in order to test:

### Local

#### Database Configuration

```sql

CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(45) DEFAULT NULL,
  `profile_image` varchar(45) DEFAULT NULL,
  `website` varchar(45) DEFAULT NULL,
  `users_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` int(11) DEFAULT NULL
);

CREATE TABLE `apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` int(11) DEFAULT NULL
);

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `stripe_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `stripe_plan` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `trial_ends_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ends_at` timestamp NULL DEFAULT NULL
);


ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

/*To execute this commands a users table must be created*/

IF (EXISTS (SELECT * 
                 FROM INFORMATION_SCHEMA.TABLES 
                 WHERE  TABLE_NAME = 'users'))
BEGIN
    ALTER TABLE `users` ADD `stripe_id` VARCHAR(200) NULL;
    ALTER TABLE `users` ADD `card_brand` VARCHAR(200) NULL;
    ALTER TABLE `users` ADD `card_last_four` VARCHAR(200) NULL;
    ALTER TABLE `users` ADD `trial_ends_at` timestamp NULL DEFAULT NULL;
    ALTER TABLE `users` ADD `active_subscription_id` VARCHAR(200) NULL DEFAULT NULL;
END


```
#### Add some parameter in config.php such as like below

```php
'stripe' => [
    'model'      => 'App\Models\Users',
    'secretKey'  => null,
    'publishKey' => null
]
```

### Model Setup

#### Add Billable Model to Users MODEL

```php
use Phalcon\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;
}

```

### Stripe
#### Plans
    * monthly-10-1 ($10)
    * monthly-10-2 ($10)
#### Coupons
    * coupon-1 ($5)

## Official Documentation

You can how to using it at [here](https://github.com/duythien/cashier/wiki/Using-Phalcon-Cashier). Also it is inspiring by Laravel so you can take look on the [Laravel website](http://laravel.com/docs/billing).

## Contributing

Thank you for considering contributing to the Cashier. You can read the contribution guide lines [here](contributing.md).

## License

Phalcon Cashier is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
