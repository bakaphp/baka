<?php

declare(strict_types=1);

namespace Baka\Router\Middlewares;

use Baka\Router\Middleware;
use Phalcon\Mvc\Micro;
use Phalcon\Utils\Slug;

class RouteMiddlewareHelper
{
    protected $api;
    protected $routeMiddlewares;

    /**
     * Constructore.
     *
     * @param Micro $api
     * @param array $routeMiddlewares
     */
    public function __construct(Micro $api, array $routeMiddlewares)
    {
        $this->api = $api;
        $this->routeMiddlewares = $routeMiddlewares;
    }

    /**
     * Get the current middleware for the given route.
     *
     * @param string $event
     *
     * @return array
     */
    public function getRouteMiddlewares(string $event = null) : array
    {
        $routeIdentifier = $this->getRouteIdentifier($this->api);

        $middlewares = $this->api->getSharedService('routeMiddlewares')[$routeIdentifier] ?? [];

        return array_filter($middlewares, function (Middleware $middleware) use ($event) {
            $foundRouteMiddleware = $this->isInRouteMiddlewares(
                $middleware->getMiddlewareKey()
            );

            if ($event) {
                return $foundRouteMiddleware && $event === $middleware->getEvent();
            }

            return $foundRouteMiddleware;
        });
    }

    /**
     * Get the route identifiers.
     *
     * @return string
     */
    public function getRouteIdentifier() : string
    {
        $activeHandler = $this->api->getActiveHandler();

        //post, get, put, patch? what method is this route
        $routeMethod = $this->api->di->get('router')->getMatchedRoute()->getHttpMethods();
        $routePattern = $this->api->di->get('router')->getMatchedRoute()->getPattern();

        return  strtolower(Slug::generate(
            $routeMethod . '-' . $routePattern . '-' . ($activeHandler[0])->getDefinition() . '-' . $activeHandler[1]
        ));
    }

    /**
     * Get the middleware class.
     *
     * @param Middleware $middleware
     *
     * @return string
     */
    public function getClass(Middleware $middleware) : string
    {
        $key = $middleware->getMiddlewareKey();

        return $this->routeMiddlewares[$key];
    }

    /**
     * Is the route on this middleware?
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isInRouteMiddlewares(string $key) : bool
    {
        return isset($this->routeMiddlewares[$key]);
    }
}
