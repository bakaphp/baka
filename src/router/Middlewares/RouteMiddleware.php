<?php

declare(strict_types=1);

namespace Baka\Router\Middlewares;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Baka\Router\Middleware;

class RouteMiddleware implements MiddlewareInterface
{
    protected $helper;

    /**
     * Construct.
     *
     * @param Micro $api
     * @param array $routeMiddlewares
     */
    public function __construct(Micro $api, array $routeMiddlewares)
    {
        $this->helper = new RouteMiddlewareHelper($api, $routeMiddlewares);
    }

    /**
     * Before execute route.
     *
     * @param mixed $event
     * @param mixed $api
     * @param mixed $contex
     * @return bool
     */
    public function beforeExecuteRoute($event, $api, $contex)
    {
        foreach ($this->helper->getRouteMiddlewares(Middleware::BEFORE) as $middleware) {
            if (!$this->executeMiddleware($middleware, $api)) {
                return false;
            };
        }

        return true;
    }

    /**
     * After executing route.
     *
     * @param mixed $event
     * @param mixed $api
     * @param mixed $contex
     * @return void
     */
    public function afterExecuteRoute($event, $api, $contex)
    {
        foreach ($this->helper->getRouteMiddlewares(Middleware::AFTER) as $middleware) {
            if (!$this->executeMiddleware($middleware, $api)) {
                return false;
            };
        }

        return true;
    }

    /**
     * Call me.
     *
     * @param Micro $api
     *
     * @return bool
     */
    public function call(Micro $api)
    {
        return true;
    }

    /**
     * Execute the middleware.
     *
     * @param Middleware $middleware
     * @param Micro $api
     * @return void
     */
    protected function executeMiddleware(Middleware $middleware, Micro $api)
    {
        $middlewareClass = $this->helper->getClass(
            $middleware
        );

        $middlewareInstance = new $middlewareClass();

        return $middlewareInstance->call(
            $api,
            ...$middleware->getParameters()
        );
    }
}
