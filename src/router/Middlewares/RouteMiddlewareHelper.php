<?php

declare(strict_types=1);

namespace Baka\Router\Middlewares;

use Phalcon\Mvc\Micro;
use Phalcon\Utils\Slug;
use Baka\Router\Middleware;

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
     * @return array
     */
    public function getRouteMiddlewares(string $event = null) : array
    {
        $routeIfentifier = $this->getRouteIdentifier($this->api);

        $middlewares = $this->api->getSharedService('routeMiddlewares')[$routeIfentifier] ?? [];

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
    public function getRouteIdentifier(): string
    {
        $activeHanlder = $this->api->getActiveHandler();

        //post, get, put, patch? what methos is this route
        $routeMethod = $this->api->di->get('router')->getMatchedRoute()->getHttpMethods();
        $routePattern = $this->api->di->get('router')->getMatchedRoute()->getPattern();

        return  strtolower(Slug::generate(
            $routeMethod . '-' . $routePattern . '-' . ($activeHanlder[0])->getDefinition() . '-' . $activeHanlder[1]
        ));
    }

    /**
     * Get the middleware class.
     *
     * @param Middleware $middleware
     * @return string
     */
    public function getClass(Middleware $middleware): string
    {
        $key = $middleware->getMiddlewareKey();

        return $this->routeMiddlewares[$key];
    }

    /**
     * Is the route on this middleware?
     *
     * @param string $key
     * @return boolean
     */
    protected function isInRouteMiddlewares(string $key) : bool
    {
        return isset($this->routeMiddlewares[$key]);
    }
}
