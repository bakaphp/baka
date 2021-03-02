<?php

namespace Baka\Test\Integration\Router;

use Baka\Router\Route;
use PhalconUnitTestCase;

class RouterTest extends PhalconUnitTestCase
{
    public function testGetRoute()
    {
        $router = Route::get('/')->controller('TestController');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('get', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testPostRoute()
    {
        $router = Route::post('/')->controller('TestController');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('post', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testPutRoute()
    {
        $router = Route::put('/')->controller('TestController');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('put', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testDeleteRoute()
    {
        $router = Route::delete('/')->controller('TestController');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('delete', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testCrudRoute()
    {
        $router = Route::crud('/')->controller('TestController')->action('index');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('get', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testGetRouteWithAction()
    {
        $router = Route::get('/')->controller('TestController')->action('index');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('get', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testPostRouteWithAction()
    {
        $router = Route::post('/')->controller('TestController')->action('index');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('post', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testPutRouteWithAction()
    {
        $router = Route::put('/')->controller('TestController')->action('index');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('put', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testDeleteRouteWithAction()
    {
        $router = Route::delete('/')->controller('TestController')->action('index');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('delete', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testCrudRouteWithAction()
    {
        $router = Route::crud('/')->controller('TestController')->action('index');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('get', $router->getVia());
        $this->assertEquals('TestController', $router->getController());
    }

    public function testNoVia()
    {
        $router = Route::crud('/users')->notVia('post');

        $this->assertInstanceOf(Route::class, $router);
        $this->assertContains('post', $router->getNotVia());
        $this->assertEmpty($router->getController());
    }

    public function testRouteCollection()
    {
        $router = Route::get('/')->controller('TestController');

        $this->assertIsArray($router->toCollections());
    }
}
