<?php

namespace Baka\Router;

use Baka\Router\Utils\Helper;
use function array_push;

class RouteGroup
{
    protected $defaultPrefix;
    protected $defaultNamespace;
    protected $defaultAction;
    protected $routes = [];
    protected $middlewares = [];

    /**
     * Constructor.
     *
     * @param array $routes
     */
    public function __construct(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    /**
     * Set from to the group.
     *
     * @param array $routes
     * @return self
     */
    public static function from(array $routes): self
    {
        return new self($routes);
    }

    /**
     * Add the routes to the group.
     *
     * @param Route $route
     * @return self
     */
    public function addRoute(Route $route): self
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * Add the middleware to this group.
     *
     * @param array ...$middlewares
     * @return self
     */
    public function addMiddlewares(...$middlewares): self
    {
        array_push($this->middlewares, ...$middlewares);

        return $this;
    }

    /**
     * Set default namespace for this group.
     *
     * @param string $defaultNamespace
     * @return self
     */
    public function defaultNamespace(string $defaultNamespace): self
    {
        $this->defaultNamespace = Helper::trimSlahes($defaultNamespace);

        return $this;
    }

    /**
     * Default prefix.
     *
     * @param string $defaultPrefix
     * @return self
     */
    public function defaultPrefix(string $defaultPrefix): self
    {
        $this->defaultPrefix = Helper::trimSlahes($defaultPrefix);

        return $this;
    }

    /**
     * Default action.
     *
     * @param string $defaultAction
     * @return self
     */
    public function defaultAction(string $defaultAction): self
    {
        $this->defaultAction = Helper::trimSlahes($defaultAction);

        return $this;
    }

    /**
     * get Default prefix.
     *
     * @return string|null
     */
    public function getDefaultPrefix(): ?string
    {
        return $this->defaultPrefix;
    }

    /**
     * get Default namespace.
     *
     * @return string|null
     */
    public function getDefaultNamespace(): ?string
    {
        return $this->defaultNamespace;
    }

    /**
     * Get default action.
     *
     * @return string|null
     */
    public function getDefaultAction(): ?string
    {
        return $this->defaultAction;
    }

    /**
     * Get routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get middleware.
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Add routes.
     *
     * @param array $routes
     * @return self
     */
    public function withRoutes(array $routes): self
    {
        $new = clone $this;

        foreach ($routes as $route) {
            $new->addRoute($route);
        }

        return $new;
    }

    /**
     * Add namespace.
     *
     * @param string $defaultNamespace
     * @return self
     */
    public function withNamespace(string $defaultNamespace): self
    {
        $new = clone $this;
        $new->defaultNamespace($defaultNamespace);

        return $new;
    }

    /**
     * Add prefix.
     *
     * @param string $defaultPrefix
     * @return self
     */
    public function withPrefix(string $defaultPrefix): self
    {
        $new = clone $this;
        $new->defaultPrefix($defaultPrefix);

        return $new;
    }

    /**
     * Add action.
     *
     * @param string $defaultAction
     * @return self
     */
    public function withAction(string $defaultAction): self
    {
        $new = clone $this;
        $new->defaultAction($defaultAction);

        return $new;
    }

    /**
     * Set Options.
     *
     * @param Route $route
     * @return Route
     */
    protected function setOptions(Route $route): Route
    {
        $route = $this->setDefaultOptions($route);
        if ($this->getMiddlewares()) {
            $route->middlewares(...$this->getMiddlewares());
        }
        return $route;
    }

    /**
     * Set default options.
     *
     * @param Route $route
     * @return Route
     */
    protected function setDefaultOptions(Route $route): Route
    {
        if (!$route->getPrefix() && $this->getDefaultPrefix()) {
            $route->prefix($this->getDefaultPrefix());
        }

        if (!$route->getNamespace() && $this->getDefaultNamespace()) {
            $route->namespace($this->getDefaultNamespace());
        }

        if (!$route->getAction() && $this->getDefaultAction()) {
            $route->action($this->getDefaultAction());
        }

        return $route;
    }

    /**
     * Conver to a collection.
     *
     * @return array
     */
    public function toCollections(): array
    {
        $collections = [];

        foreach ($this->routes as $route) {
            $route = $this->setOptions($route);
            array_push($collections, ...$route->toCollections());
        }

        return $collections;
    }
}
