<?php

namespace Baka\Router\Parsers;

use Baka\Router\Utils\Http;
use Baka\Router\Route;
use Baka\Router\Collection;
use function in_array;

class RouteParser
{
    const ACTIONS = [
        Http::POST => 'create',
        Http::GET => 'index',
        Http::PUT => 'edit',
        Http::PATCH => 'edit',
        Http::DELETE => 'delete',
    ];

    const GET_SPECIFIC_RESOURCE_ACTION = 'getById';
    const SPECIFIC_RESOURCE_PATH = '/{id}';

    protected $route;
    protected $collections = [];

    /**
     * Contructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Parse the route to create collection.
     *
     * @return void
     */
    public function parse(): array
    {
        $this->hasMethod(Http::POST) and $this->addPostCollection();
        $this->hasMethod(Http::GET) and $this->addGetCollection();
        $this->hasMethod(Http::PUT) and $this->addPutCollection();
        $this->hasMethod(Http::PATCH) and $this->addPatchCollection();
        $this->hasMethod(Http::DELETE) and $this->addDeleteCollection();

        return $this->getCollections();
    }

    /**
     * Return the list of collection for the current route.
     *
     * @return array
     */
    public function getCollections() : array
    {
        return $this->collections;
    }

    /**
     * Get POST collection from based on the route.
     *
     * @return array
     */
    protected function addPostCollection(): void
    {
        $collection = Collection::fromRoute($this->route);

        $action = $this->route->getAction() ?? static::ACTIONS[Http::POST];

        $collection->post(
            $this->route->getPattern(),
            $action
        );

        $this->addCollection($collection);
    }

    /**
     * Get GET collection from based on the route.
     *
     * @return array
     */
    protected function addGetCollection(): void
    {
        $collection = Collection::fromRoute($this->route);
        $this->route->useRestConvention() and $collection2 = clone $collection;

        $action = $this->route->getAction() ?? static::ACTIONS[Http::GET];

        $collection->get(
            $this->route->getPattern(),
            $action
        );

        $this->addCollection($collection);

        // If the route has useRestConvention to true, we need to add another GEt collection in order
        // to have one to get a list of resources and other to get a specific resource
        if ($this->route->useRestConvention()) {
            $collection2->get(
                $this->parsePattern($this->route->getPattern()),
                static::GET_SPECIFIC_RESOURCE_ACTION
            );

            $this->addCollection($collection2);
        }
    }

    /**
     * Get PUT collection from based on the route.
     *
     * @return array
     */
    protected function addPutCollection(): void
    {
        $collection = Collection::fromRoute($this->route);

        $action = $this->route->getAction() ?? static::ACTIONS[Http::PUT];

        $collection->put(
            $this->parsePattern($this->route->getPattern()),
            $action
        );

        $this->addCollection($collection);
    }

    /**
     * Get PATCH collection from based on the route.
     *
     * @return array
     */
    protected function addPatchCollection(): void
    {
        $collection = Collection::fromRoute($this->route);

        $action = $this->route->getAction() ?? static::ACTIONS[Http::PATCH];

        $collection->patch(
            $this->parsePattern($this->route->getPattern()),
            $action
        );

        $this->addCollection($collection);
    }

    /**
     * Get DELETE collection from based on the route.
     *
     * @return array
     */
    protected function addDeleteCollection(): void
    {
        $collection = Collection::fromRoute($this->route);

        $action = $this->route->getAction() ?? static::ACTIONS[Http::DELETE];

        $collection->delete(
            $this->parsePattern($this->route->getPattern()),
            $action
        );

        $this->addCollection($collection);
    }

    /**
     * Verify whether the current route has a specific method in its via.
     *
     * @return bool
     */
    protected function hasMethod(string $method): bool
    {
        return in_array($method, $this->route->getVia());
    }

    /**
     * Add a collection to the collections list.
     *
     * @param Collection $collection
     * @return void
     */
    protected function addCollection(Collection $collection): void
    {
        $this->collections[] = $collection;
    }

    /**
     * Transform a pattern in rest convention if needed.
     *
     * @param string $pattern
     * @return string
     */
    protected function parsePattern(string $pattern): string
    {
        $this->route->useRestConvention() and $pattern .= static::SPECIFIC_RESOURCE_PATH;

        return $pattern;
    }
}
