<?php

declare(strict_types=1);

namespace Baka\Contracts\EventsManager;

use Phalcon\Di;
use Phalcon\Events\ManagerInterface as EventsManager;

/**
 * Phalcon\Traits\EventManagerAwareTrait.
 *
 * Trait for event processing
 *
 * @package Phalcon\Traits
 */

trait EventManagerAwareTrait
{
    /**
     * @var EventsManager
     */
    protected $eventsManager = null;

    /**
     * set event manager.
     *
     * @param EventsManager $eventsManager
     */
    public function setEventsManager(EventsManager $manager)
    {
        $this->eventsManager = $manager;
    }

    /**
     * return event manager.
     *
     * @return EventsManager | null
     */
    public function getEventsManager()
    {
        $di = Di::getDefault();

        if (!empty($this->eventsManager)) {
            $manager = $this->eventsManager;
        } elseif ($di->has('eventsManager')) {
            $manager = $di->get('eventsManager');
        }

        if (isset($manager) && $manager instanceof EventsManager) {
            return $manager;
        }

        return null;
    }

    /**
     * Checking if event manager is defined - fire event.
     *
     * @param string $event
     * @param object $source
     * @param mixed $data
     * @param boolean $cancelable
     *
     */
    public function fire($event, $source, $data = null, $cancelable = true)
    {
        if ($manager = $this->getEventsManager()) {
            $manager->fire($event, $source, $data, $cancelable);
        }
    }
}
