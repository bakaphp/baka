<?php

namespace Baka\Test\Integration\Router;

use Baka\Router\Collection;
use Baka\Router\Route;
use PhalconUnitTestCase;

class CollectionsTest extends PhalconUnitTestCase
{
    public function testCreateCollection()
    {
        $router = Route::get('/')->controller('TestController');

        $collection = Collection::fromRoute($router);

        $action = $router->getAction();

        $collection->post(
            $router->getPattern(),
            $action
        );

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertTrue($collection->isLazy());
        $this->assertEquals('TestController', $collection->getHandler());
    }
}
