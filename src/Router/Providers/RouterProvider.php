<?php

declare(strict_types=1);

namespace Baka\Router\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Micro;

class RouterProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        /** @var Micro $application */
        $application = $container->getShared('application');

        $this->attachRoutes($application, $container);
    }

    /**
     * Attache the routes to the application; lazy loaded.
     *
     * @param Micro $application
     * @param DiInterface $container
     */
    protected function attachRoutes(Micro $application, DiInterface $container)
    {
        $routeMiddlewares = [];

        foreach ($this->getCollections() as $collection) {
            $application->mount($collection);

            $key = $collection->getCollectionIdentifier();
            $routeMiddlewares[$key] = $collection;
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
    protected function getCollections() : array
    {
        return [];
    }
}
