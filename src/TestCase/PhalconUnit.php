<?php

namespace Baka\TestCase;

use Codeception\Test\Unit;
use Phalcon\Di;
use Faker\Factory;

abstract class PhalconUnit extends Unit
{
    /**
     * @var Faker\Factory
     */
    protected $faker;

    /**
     * @var \Phalcon\Config
     */
    protected $config;

    protected $di;

    /**
     * Setup your Phalcon DI configuration
     *
     * @return void
     */
    abstract protected function configureDI() : void;
    abstract protected function setConfiguration() : void;

    /**
     * Load the PhalconPHP Di
     *
     * @return Phalcon\DI
     */
    protected function setupDI() : void
    {
        DI::reset();

        $this->di = new DI();
    }

    /**
     * Get the PhalconDI
     *
     * @return DI
     */
    protected function getDI() : DI 
    {
        return $this->di;
    }

    /**
     * Deprecated
     *
     * @deprecated v1
     * @return DI
     */
    protected function _getDI(): DI 
    {
        return $this->getDI();
    }

    /**
     * this runs before everyone.
     */
    protected function setUp() : void
    {
        $this->setupDI();
        $this->setConfiguration();
        $this->configureDI();
        $this->faker = Factory::create();
    }
}
