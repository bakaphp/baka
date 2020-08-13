<?php

declare(strict_types=1);

namespace Baka;

use Exception;
use Phalcon\Di;

class Redis
{
    /**
     * Redis get function with Callable function.
     *
     * @param string $key
     * @param callable $callback
     *
     * @return mixed
     */
    public static function get(string $key, callable $callback)
    {
        if ($redis = Di::getDefault()->get('redis')) {
            $data = $redis->get($key);

            if (!is_callable($callback)) {
                throw new Exception("Key value '{$key}' not in cache and not available callback to populate cache");
            }

            return !empty($data) ? $callback($data) : false;
        }

        return ;
    }
}
