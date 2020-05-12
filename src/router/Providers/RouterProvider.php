<?php

declare(strict_types=1);

namespace Baka\Router\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Micro;

class RouterProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        /** @var Micro $application */
        $application = $container->getShared('application');

        $this->attachRoutes($application);
        $this->attachRouteMiddlewares($container);
    }

    /**
     * Attache the routes to the application; lazy loaded.
     *
     * @param Micro $application
     */
    protected function attachRoutes(Micro $application)
    {
        foreach ($this->getCollections() as $collection) {
            $application->mount($collection);
        }
    }

    /**
     * Attache routes' middlewares.
     *
     * @param DiInterface $container
     */
    protected function attachRouteMiddlewares(DiInterface $container)
    {
        $routeMiddlewares = [];

        foreach ($this->getCollections() as $collection) {
            $key = $collection->getCollectionIdentifier();
            $middlewares = $collection->getMiddlewares();

            if ($middlewares) {
                $routeMiddlewares[$key] = $middlewares;
            }
        }

        $container->setShared(
            'routeMiddlewares',
            function () use ($routeMiddlewares) {
                return $routeMiddlewares;
            }
        );
    }

    /**
     * Return the array of collections.
     *
     * @return array
     */
    protected function getCollections(): array
    {
        return [];
    }
}
