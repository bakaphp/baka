<?php

declare(strict_types=1);

namespace Baka\Jobs;

use Baka\Contracts\Metadata\MetadataTrait;
use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Contracts\Queue\QueueableTrait;

abstract class Job implements QueueableJobInterface
{
    use QueueableTrait;
    use MetadataTrait;

    /**
     * Execute de Jobs.
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Dispatch the job with the given arguments.
     *
     * @param mixed $mixed
     *
     * @return void
     */
    public static function dispatch() : PendingDispatch
    {
        return new PendingDispatch(new static(...func_get_args()));
    }
}
