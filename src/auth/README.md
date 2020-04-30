# Baka Auth

MC Auth Library to avoid having to redo a user signup flow for apps.

## Testing

```bash
codecept run
```

## JWT
Add JWT to your configuration

```php
'jwt' => [
    'secretKey' => getenv('JWT_SECURITY_HASH'),
    'expirationTime' => '1 hour', #strtotime
    'payload' => [
        'exp' => 1440,
        'iss' => 'phalcon-jwt-auth',
    ],
    'ignoreUri' => [
        'regex:auth',
        'regex:webhook',
        'regex:/users',
    ],
],
```

## Database 

We use phinx for migration , to update this project db structure just run

`vendor/bin/phinx-migrations generate` 

To run this migration from your project just add the path of the db location to your phinx.php path 

```
<?php

return [
    'paths' => [
        'migrations' => [
            getenv('PHINX_CONFIG_DIR') . '/db/migrations',
            '/home/baka/auth/db/migrations',
        ],
```

`vendor/bin/phinx migrate -e (enviorment : development | production)`


## Using

Add this to your service.php

```php
/**
* UserData dependency injection for the system
*
* @return Session
*/
$di->set('userData', function () use ($config, $auth) {
    $data = $auth->data();

    $session = new Baka\Auth\Models\Sessions();
    $request = new Phalcon\Http\Request();

    if (!empty($data) && !empty($data['sessionId'])) {
        //user
        if (!$user = Baka\Auth\Models\Users::getByEmail($data['email'])) {
            throw new Exception('User not found');
        }

        return $session->check($user, $data['sessionId'], $request->getClientAddress(), 1);
    } else {
        throw new Exception('User not found');
    }
});
```

## Generate migration files

```bash
$ phalcon migration --action=run --migrations=migrations --config=</path/to/config.php>
```

## Import migration into project

```bash
$ phalcon migration --action=run --migrations=vendor/baka/auth/migrations/
```

## Router

```php
$router->post('/auth', [
    '\Your\NameSpace\AuthController',
    'login',
]);

$router->post('/auth/signup', [
    '\Your\NameSpace\AuthController',
    'signup',
]);

$router->post('/auth/logout', [
    '\Your\NameSpace\AuthController',
    'logout',
]);

# get email for new password
$router->post('/auth/recover', [
    '\Your\NameSpace\AuthController',
    'recover',
]);

# update new password
$router->put('/auth/{key}/reset', [
    '\Your\NameSpace\AuthController',
    'reset',
]);

# active the account
$router->put('/auth/{key}/activate', [
    '\Your\NameSpace\AuthController',
    'activate',
]);

```

## Social logins

```json
"hybridauth/hybridauth": "dev-3.0.0-Remake",
```

```php
<?php
'social_config' => [
    // required
    "callback" => getenv('SOCIAL_CONNECT_URL'),
    // required
    "providers" => [
        "Facebook" => [
            "enabled" => true,
            "callback" => getenv('SOCIAL_CONNECT_URL').'/Facebook',
            "keys" => ["id" => getenv('FB_ID'), "secret" => getenv('FB_SECRET')], //production
        ]
    ],
],
```

And configure the links and callback link (SOCIAL_CONNECT_URL) to
http://site.com/users/social/{site}
Example:
http://site.com/users/social/Facebook

You need to add this to your registration process to idenfity social login

```volt
{% if socialConnect %}
    <input type="hidden" name="socialConnect" value="1">
{% endif %}
```
