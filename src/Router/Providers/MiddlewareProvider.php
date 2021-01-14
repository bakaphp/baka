<?php

declare(strict_types=1);

namespace Baka\Router\Providers;

use Baka\Router\Middlewares\RouteMiddleware;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;

class MiddlewareProvider implements ServiceProviderInterface
{
    protected $globalMiddlewares = [];
    protected $routeMiddlewares = [];

    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        /** @var Micro $application */
        $application = $container->getShared('application');
        /** @var Manager $eventsManager */
        $eventsManager = $container->getShared('eventsManager');
        // $eventsManager->enablePriorities(true);

        $this->attachMiddleware($application, $eventsManager);

        $application->setEventsManager($eventsManager);
    }

    /**
     * Attaches the middleware to the application.
     *
     * @param Micro   $application
     * @param Manager $eventsManager
     */
    protected function attachMiddleware(Micro $application, Manager $eventsManager)
    {
        /**
         * Get the events manager and attach the middleware to it.
         */
        foreach ($this->globalMiddlewares as $class => $function) {
            $eventsManager->attach('micro', new $class());
            $application->{$function}(new $class());
        }

        $routeMiddleware = new RouteMiddleware($application, $this->routeMiddlewares);

        $eventsManager->attach('micro', $routeMiddleware);
        $application->before($routeMiddleware);
        $application->after($routeMiddleware);
    }
}
