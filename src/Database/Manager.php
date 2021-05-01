<?php

declare(strict_types=1);

namespace Baka\Database;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\ModelInterface;

class Manager extends ModelManager
{
    /**
     * Overwrite the connection manager for Phalcon Model ,
     * we need this to avoid using getShared and get a new instance of the DB on ech request
     * thus allowing us to use swoole coroutines without Swoole\Error: Socket# has already been bound to another coroutine.
     *
     * @param ModelInterface $model
     * @param mixed $connectionServices
     *
     * @return AdapterInterface
     */
    protected function getConnection(ModelInterface $model, $connectionServices) : AdapterInterface
    {
        $service = $this->_getConnectionService($model, $connectionServices);

        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound(
                    'the services related to the ORM'
                )
            );
        }

        /**
         * Request the connection service from the DI.
         */
        $connection = $container->get($service);

        if (!is_object($connection)) {
            throw new Exception('Invalid injected connection service');
        }

        return $connection;
    }
}
