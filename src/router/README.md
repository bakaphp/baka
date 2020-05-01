# Baka Router

A tool to create multiple Phalcon [Collection](https://docs.phalconphp.com/3.4/en/api/Phalcon_Mvc_Micro_Collection)s with easy. 

## Requirements

- This package requires PHP 7.1 or higher.
- Phalcon 3.4 or higher.

## Installation

You can install the package via composer:

``` bash
composer require baka/router
```

## Basic Usage

``` php
require_once __DIR__ . '/vendor/autoload.php';

use Baka\Router\RouteGroup;
use Baka\Router\Route;
use Baka\Router\Utils\Http;

$routes = [
    Route::add('u')->controller('UsersController')->via(Http::GET, Http::POST),
    Route::get('custom-fields'),
    Route::put('users')->action('editUser'),
    Route::add('companies')->middlewares(
        'custom.middleware@before:10,12',
        'custom.middleware2@after'
    ),
];

$anotherRoute = new Route('companies');

$anotherRoute->prefix('/v2')
->controller('CompaniesController')
->namespace('App\\Api\\Controllers')
->via('get','put','post');

$routeGroup = RouteGroup::from($routes)
->addRoute(Route::put('products')->action('edit'))
->addRoute($anotherRoute)
->addMiddlewares('extra.middleware@before')
->defaultNamespace('App\\Default\\Controllers')
->defaultAction('call');

$collections = $routeGroup->toCollections();

var_dump($collections); // 16 Collection instances


// Mount collections to the app

$app = new \Phalcon\Mvc\Micro();

foreach ($collections as $collection){ 
    $app->mount($collection);
}
```
