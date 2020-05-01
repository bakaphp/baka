<?php

namespace Baka\Router\Parsers;

use Baka\Support\Str;
use Baka\Support\Arr;
use Baka\Router\Middleware;

class MiddlewareParser
{
    const MIDDLEWARE_KEY_DELIMETER = '@';
    const EVENT_DELIMETER = ':';
    const PARAMETER_DELIMETER = ',';

    protected $middlewareNotation;

    /**
     * Constructor.
     *
     * @param string $middlewareNotation
     */
    public function __construct(string $middlewareNotation)
    {
        $this->middlewareNotation = $middlewareNotation;
        $this->extractMiddlewareKey();
        $this->extractEvent();
        $this->extractParameters();
    }

    /**
     * Attach the element's to the middleware.
     *
     * @return Middleware
     */
    public function parse(): Middleware
    {
        $middlewareKey = $this->extractMiddlewareKey();
        $event = $this->extractEvent();
        $parameters = $this->extractParameters();

        $middleware = new Middleware($middlewareKey);
        if ($event) {
            $middleware->event($event);
        }
        if ($parameters) {
            $middleware->parameters($parameters);
        }

        return $middleware;
    }

    /**
     * Return the key for this middleware.
     *
     * @return string
     */
    protected function extractMiddlewareKey(): string
    {
        return current(
            explode(
                static::MIDDLEWARE_KEY_DELIMETER,
                $this->middlewareNotation,
                -1
            )
        );
    }

    /**
     * Extract the event of the middleware.
     *
     * @return string
     */
    protected function extractEvent(): string
    {
        if (Str::includes(static::EVENT_DELIMETER, $this->middlewareNotation)) {
            return Str::firstStringBetween(
                $this->middlewareNotation,
                static::MIDDLEWARE_KEY_DELIMETER,
                static::EVENT_DELIMETER
            );
        }

        return Arr::last(
            explode(static::MIDDLEWARE_KEY_DELIMETER, $this->middlewareNotation)
        );
    }

    /**
     * Extract the params.
     *
     * @return array
     */
    protected function extractParameters(): array
    {
        if (Str::includes(static::EVENT_DELIMETER, $this->middlewareNotation)) {
            $paramters = Arr::last(
                explode(
                    static::EVENT_DELIMETER,
                    $this->middlewareNotation
                )
            );

            return explode(static::PARAMETER_DELIMETER, $paramters);
        }

        return [];
    }
}
