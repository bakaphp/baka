<?php

namespace Baka\Router;

use Baka\Router\Parsers\MiddlewareParser;
use Baka\Support\Str;
use Phalcon\Mvc\Micro\Collection as PhCollection;

class Collection extends PhCollection
{
    protected ?Route $route = null;
    protected static array $index = [];
    public static array $collectionMiddleWare = [];

    protected bool $hasMiddleware = false;
    protected bool $reUse = false;

    /**
     * Create a new instance of Collection based on Route instance.
     *
     * @param Route $route
     *
     * @return self
     */
    final public static function fromRoute(Route $route) : self
    {
        $totalMiddleware = count($route->getMiddlewares());
        $middlewareKey = implode('-', $route->getMiddlewares());
        $key = $route->getController() . '_middleware_' . $middlewareKey;

        //cant use static method on test
        $collection = isset(self::$index[$key]) && !defined('API_TESTS') ? self::$index[$key] : false;

        if (!$collection) {
            $collection = new self();
            $collection->route = $route;
            $collection->hasMiddleware = $totalMiddleware > 0;
            $collection->setHandler($route->getHandler(), true);

            self::$index[$key] = $collection;
        } else {
            $collection->reUse = true;
        }

        return $collection;
    }

    /**
     * Do we have middleware ?
     *
     * @return bool
     */
    public function hasMiddleware() : bool
    {
        return $this->hasMiddleware;
    }

    /**
     * Is this collection reused?
     *  we don't want to create a new collection each time we need to attach handlers.
     *
     * @return bool
     */
    public function isReused() : bool
    {
        return $this->reUse;
    }

    /**
     * Return collection's middlewares.
     *
     * @return array
     */
    public function getMiddlewares() : array
    {
        $middlewares = [];

        foreach ($this->route->getMiddlewares() as $notation) {
            $middlewareParser = new MiddlewareParser($notation);
            $middlewares[] = $middlewareParser->parse();
        }

        return $middlewares;
    }

    /**
     * Generate this collection middleware mapping for fast access.
     *
     * @param self $collection
     *
     * @return void
     */
    public static function generateMiddlewareMapping(self $collection) : void
    {
        foreach ($collection->getHandlers() as $handler) {
            self::$collectionMiddleWare[$collection->getCollectionIdentifier($handler)] = $collection;
        }
    }

    /**
     * Return a unique identifier for the current collection.
     *
     * @return string
     */
    public function getCollectionIdentifier(array $handler) : string
    {
        //$this->getHandlers()[0][0] whats the router method? GET , POST, PUT , DELETE
        //$this->getHandlers()[0][1] what the prefix
        /*
        [0] => Array
            (
                [0] => GET
                [1] => /v1
                [2] => index
                [3] =>
            )
        */

        return Str::slug(
            $handler[0] . '-' . $handler[1] . '-' . $this->getHandler() . '-' . $handler[2]
        );
    }
}
