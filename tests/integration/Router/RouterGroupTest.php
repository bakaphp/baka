<?php

namespace Baka\Test\Integration\Router;

use Baka\Router\Collection;
use Baka\Router\Route;
use Baka\Router\RouteGroup;
use PhalconUnitTestCase;

class RouterGroupTest extends PhalconUnitTestCase
{
    public function testGroup()
    {
        $group = [
            Route::get('/')->controller('TestController')
        ];

        $publicRoutesGroup = RouteGroup::from($group)
            ->defaultNamespace('Canvas\Api\Controllers')
            ->defaultPrefix('/v1');

        $this->assertIsArray($publicRoutesGroup->getRoutes());
        $this->assertIsArray($publicRoutesGroup->getMiddlewares());
        $this->assertTrue(empty($publicRoutesGroup->getMiddlewares()));
    }

    public function testGroupWithMiddleware()
    {
        $group = [
            Route::get('/')->controller('TestController')
        ];

        $publicRoutesGroup = RouteGroup::from($group)
            ->defaultNamespace('Canvas\Api\Controllers')
            ->addMiddlewares('auth.jwt@before', 'auth.acl@before', 'auth.activeStatus@before')
            ->defaultPrefix('/v1');

        $this->assertIsArray($publicRoutesGroup->getRoutes());
        $this->assertIsArray($publicRoutesGroup->getMiddlewares());
        $this->assertIsArray(($publicRoutesGroup->getMiddlewares()));
        $this->assertTrue(!empty($publicRoutesGroup->getMiddlewares()));
    }

    public function testRouteCollection()
    {
        $group = [
            Route::get('/')->controller('TestController'),
            Route::post('/')->controller('TestController')
        ];

        $publicRoutesGroup = RouteGroup::from($group)
            ->defaultNamespace('Canvas\Api\Controllers')
            ->addMiddlewares('auth.jwt@before', 'auth.acl@before', 'auth.activeStatus@before')
            ->defaultPrefix('/v1');

        $this->assertIsArray($publicRoutesGroup->toCollections());
        $this->assertTrue($publicRoutesGroup->toCollections()[0] instanceof Collection);
    }
}
