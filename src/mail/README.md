# Phalcon\Mailer

Baka email wrapper for Swiftmailer with queue

## Configure

**SMTP**

```php
'email' => [
    'driver' => 'smtp',
    'host' => getenv('EMAIL_HOST'),
    'port' => getenv('EMAIL_PORT'),
    'username' => getenv('EMAIL_USER'),
    'password' => getenv('EMAIL_PASS'),
    'from' => [
        'email' => 'noreply@domain.do',
        'name' => 'YOUR FROM NAME',
    ],
    'debug' => [
        'from' => [
            'email' => 'noreply@domain.do',
            'name' => 'YOUR FROM NAME',
        ],
    ],
];
```

## Setup DI

**createMessage()**

```php
$di->set('mail', function () use ($config, $di) {

    //setup
    $mailer = new \Baka\Mail\Manager($config->email->toArray());

    return $mailer->createMessage();
});
```

**Sending a normal email()**
```php
  $this->mail
    ->to('info@domain.do')
    ->subject('Test Normal Email queue')
    ->content('normal email send via queue')
    ->send();
];

```

**Sending a template normal email()**
```php
  $this->mail
    ->to('info@domain.dom')
    ->subject('Test Template Email queue')
    ->params(['name' => 'test'])
    ->template('email.volt') //you can also use template() default template is email.volt
    ->send();
];

```

**Sending a normal email instantly, without queue()**
```php
  $this->mail
    ->to('info@domain.do')
    ->subject('Test Normal Email now')
    ->content('send normal email now')
    ->sendNow();
];

```

## Events
- `mailer:beforeCreateMessage`
- `mailer:afterCreateMessage`
- `mailer:beforeSend`
- `mailer:afterSend`
- `mailer:beforeAttachFile`
- `mailer:afterAttachFile`


## Setup CLI

```php

use Phalcon\Cli\Task;

/**
 * Class LsTask
 * @description('List directory content', 'The content will be displayed in the standard output')
 */
class MainTask extends Task
{
    use Baka\Mail\JobTrait;
}

```

## Running CLI

`php app.php main mailqueue email_queue`
