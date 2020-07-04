<?php

declare(strict_types=1);

namespace Baka\Jobs;

use Baka\Auth\UserProvider;
use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Queue\Queue;
use Phalcon\Di;

class PendingDispatch
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    /**
     * Create a new pending job dispatch.
     *
     * @param  QueueableJobInterface  $job
     * @return void
     */
    public function __construct(QueueableJobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * Set the desired queue for the job.
     *
     * @param  string  $queue
     * @return $this
     */
    public function onQueue(string $queue)
    {
        $this->job->onQueue($queue);
        return $this;
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        $jobData = [
            'userData' => UserProvider::get(),
            'class' => get_class($this->job),
            'job' => $this->job
        ];

        return Queue::send(
            $this->job->queue,
            serialize($jobData)
        );
    }
}
