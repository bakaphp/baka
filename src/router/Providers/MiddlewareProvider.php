<?php

declare(strict_types=1);

namespace Baka\Router\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;
use Baka\Router\Middlewares\RouteMiddleware;

class MiddlewareProvider implements ServiceProviderInterface
{
    protected $globalMiddlewares = [];
    protected $routeMiddlewares = [];

    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
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
