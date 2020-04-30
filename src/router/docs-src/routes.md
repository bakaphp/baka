# Routes

## Basic Usage

```php-inline
use Baka\Router\Route;

$route = Route::post('user');
$route->controller('userController');
$route->namespace('App\Api\Controllers');

// OR

Route::post('user')
    ->controller('userController')
    ->namespace('App\Api\Controllers');

```

## Advanced Usage

```php-inline
use Baka\Router\Route;

$route = Route::post('user');
```

### Setting a Prefix

The prefix method is used to set a prefix to the Route.

```php-inline
// Adding Prefix
$route->prefix('admin');
```

!!! warning
    You shouldn't write a `/` at the end of the prefix.

### Setting the Controller

The controller method is used to pass the name of the controller class that will handler the request.

```php-inline
// Adding Controller
$route->controller('customController');
```

!!! Tip
    You can pass a controller class property to avoid passing the namespace.
    ```php-inline
    $route->controller(customController::class);
    ```

!!! Info
    If no controller was set, the Router generates a controller name based on the path given.
    In this case the controller name generated would be `userController`. See **setDefaultController** method in [Route](https://github.com/bakaphp/router/blob/master/src/Route.php).

## Setting the Action
## Setting the Namespace
## Setting Http Verbs
## Setting Middlewares


```php-inline
// Adding Action
$route->action('save');

// Adding Namespace
$route->namespace('App\Api\Controllers');

// Adding Middlewares
$route->middlewares(
    'custom.middleware@before',
    'another.middleware@before',
);

// OR

Route::post('user')
->prefix('/v1')
->controller('userController')
->action('save')
->namespace('App\Api\Controllers')
->middlewares(
    'custom.middleware@before',
    'another.middleware@before',
);
```
