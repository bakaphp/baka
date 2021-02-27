<?php

namespace Baka\Test\Integration\Router;

use Baka\Router\Route;
use PhalconUnitTestCase;

class RouterTest extends PhalconUnitTestCase
{
    public function testGetRoute()
    {
        $router = Route::get('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
    }

    public function testPostRoute()
    {
        $router = Route::post('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
    }

    public function testPutRoute()
    {
        $router = Route::put('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
    }

    public function testDeleteRoute()
    {
        $router = Route::delete('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
    }

    public function testCrudRoute()
    {
        $router = Route::crud('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
    }

    public function testGetRouteWithAction()
    {
        $router = Route::get('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
    }

    public function testPostRouteWithAction()
    {
        $router = Route::post('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
    }

    public function testPutRouteWithAction()
    {
        $router = Route::put('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
    }

    public function testDeleteRouteWithAction()
    {
        $router = Route::delete('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
    }

    public function testCrudRouteWithAction()
    {
        $router = Route::crud('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
    }

    public function testRouteCollection()
    {
        $router = Route::get('/')->controller('TestController');

        $this->assertTrue(is_array($router->toCollections()));
    }
}
