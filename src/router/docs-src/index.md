# Getting Started

A tool to create multiple [Phalcon](https://phalconphp.com/en/)'s [Collection](https://docs.phalconphp.com/3.4/en/api/Phalcon_Mvc_Micro_Collection)s with easy.


## Requirements

* PHP >=7.1
* [Phalcon](https://phalconphp.com/en/) 3.4 or higher.


## Install

``` bash
composer require baka/router
```

## Usage

```php-inline
use Baka\Router\RouteGroup;
use Baka\Router\Route;

$routes = [
    Route::get('status')
    // Method: GET 
    // Paths: /status, /status/{id:[0-9]+}
    // Namespace: 
    // Controller: StatusController
    // Action: index
];

// Group all the routes that has shared configutarions like namespace
$routeGroup = RouteGroup::from($routes)
->defaultNamespace('App\Api\Controllers');

// Mount collections to the app
$app = new \Phalcon\Mvc\Micro();

foreach ($routeGroup->toCollections() as $collection){ 
    $app->mount($collection);
}
```