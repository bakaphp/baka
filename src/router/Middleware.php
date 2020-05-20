<?php

namespace Baka\Router;

use InvalidArgumentException;

class Middleware
{
    const BEFORE = 'before';
    const AFTER = 'after';

    const EVENTS = [self::AFTER, self::BEFORE];

    protected $middlewareKey;
    protected $parameters = [];
    protected $event = self::BEFORE;

    /**
     * Construct.
     *
     * @param string $middlewareKey
     */
    public function __construct(string $middlewareKey)
    {
        $this->middlewareKey = $middlewareKey;
    }

    /**
     * Middleware Events.
     *
     * @param string $event
     * @return void
     */
    public function event(string $event): void
    {
        if (!in_array($event, static::EVENTS)) {
            throw new InvalidArgumentException('Only before and after are accepted events.');
        }
        $this->event = $event;
    }

    /**
     * Params.
     *
     * @param array $parameters
     * @return void
     */
    public function parameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Middleware key.
     *
     * @return string
     */
    public function getMiddlewareKey(): string
    {
        return $this->middlewareKey;
    }

    /**
     * Get params.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get events.
     *
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }
}
