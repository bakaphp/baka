<?php

declare(strict_types=1);

namespace Baka\Contracts\Queue;

use Baka\Queue\Queue;

trait QueueableTrait
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = Queue::JOBS;

    /**
     * whether to retry the job on fail or not.
     *
     * @var bool
     */
    public bool $useRetry = false;

    /**
     * Delay time in seconds between each retry.
     *
     * @var bool
     */
    public int $retryDelay = 0;

    /**
     * Set the desired queue for the job.
     *
     * @param  string $queue
     *
     * @return $this
     */
    public function onQueue(string $queue)
    {
        $this->queue = $queue;
        return $this;
    }
}
