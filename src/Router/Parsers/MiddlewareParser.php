<?php

namespace Baka\Router\Parsers;

use Baka\Router\Middleware;
use Baka\Support\Arr;
use Baka\Support\Str;

class MiddlewareParser
{
    const MIDDLEWARE_KEY_DELIMITER = '@';
    const EVENT_DELIMITER = ':';
    const PARAMETER_DELIMITER = ',';

    protected string $middlewareNotation;

    /**
     * Constructor.
     *
     * @param string $middlewareNotation
     */
    public function __construct(string $middlewareNotation)
    {
        $this->middlewareNotation = $middlewareNotation;
        /*    $this->extractMiddlewareKey();
           $this->extractEvent();
           $this->extractParameters(); */
    }

    /**
     * Attach the element's to the middleware.
     *
     * @return Middleware
     */
    public function parse() : Middleware
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
    protected function extractMiddlewareKey() : string
    {
        return current(
            explode(
                static::MIDDLEWARE_KEY_DELIMITER,
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
    protected function extractEvent() : string
    {
        if (Str::includes(static::EVENT_DELIMITER, $this->middlewareNotation)) {
            return Str::firstStringBetween(
                $this->middlewareNotation,
                static::MIDDLEWARE_KEY_DELIMITER,
                static::EVENT_DELIMITER
            );
        }

        return Arr::last(
            explode(static::MIDDLEWARE_KEY_DELIMITER, $this->middlewareNotation)
        );
    }

    /**
     * Extract the params.
     *
     * @return array
     */
    protected function extractParameters() : array
    {
        if (Str::includes(static::EVENT_DELIMITER, $this->middlewareNotation)) {
            $parameters = Arr::last(
                explode(
                    static::EVENT_DELIMITER,
                    $this->middlewareNotation
                )
            );

            return explode(static::PARAMETER_DELIMITER, $parameters);
        }

        return [];
    }
}
