<?php

declare(strict_types=1);

namespace Baka\Redis;

use Redis;
use RuntimeException;
use Swoole\Coroutine\Channel;
use function Baka\envValue;

class Pool
{
    protected Channel $pool;

    /**
     * RedisPool constructor.
     *
     * @param int $size max connections
     */
    public function __construct(int $size = 100)
    {
        $this->pool = new Channel($size);
        for ($i = 0; $i < $size; $i++) {
            $redis = new Redis();
            $res = $redis->pconnect(
                envValue('REDIS_HOST', '127.0.0.1'),
                (int) envValue('REDIS_PORT', 6379)
            );

            if ($res == false) {
                throw new RuntimeException('failed to connect redis server.');
            } else {
                $this->put($redis);
            }
        }
    }

    /**
     * Get the current redis instance.
     *
     * @return Redis
     */
    public function get() : Redis
    {
        return $this->pool->pop();
    }

    /**
     * Add redis to the pool.
     *
     * @param Redis $redis
     *
     * @return void
     */
    public function put(Redis $redis) : void
    {
        $this->pool->push($redis);
    }

    /**
     * Close the redis connection.
     *
     * @return void
     */
    public function close() : void
    {
        $this->pool->close();
        $this->pool = null;
    }
}
