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
        $this->assertTrue(in_array('get', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testPostRoute()
    {
        $router = Route::post('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('post', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testPutRoute()
    {
        $router = Route::put('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('put', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testDeleteRoute()
    {
        $router = Route::delete('/')->controller('TestController');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('delete', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testCrudRoute()
    {
        $router = Route::crud('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('get', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testGetRouteWithAction()
    {
        $router = Route::get('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('get', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testPostRouteWithAction()
    {
        $router = Route::post('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('post', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testPutRouteWithAction()
    {
        $router = Route::put('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('put', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testDeleteRouteWithAction()
    {
        $router = Route::delete('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('delete', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testCrudRouteWithAction()
    {
        $router = Route::crud('/')->controller('TestController')->action('index');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('get', $router->getVia()));
        $this->assertTrue($router->getController() === 'TestController');
    }

    public function testNoVia()
    {
        $router = Route::crud('/users')->notVia('post');

        $this->assertTrue($router instanceof Route);
        $this->assertTrue(in_array('post', $router->getNotVia()));
        $this->assertTrue(empty($router->getController()));
    }

    public function testRouteCollection()
    {
        $router = Route::get('/')->controller('TestController');

        $this->assertTrue(is_array($router->toCollections()));
    }
}
