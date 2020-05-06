<?php

namespace Baka;

use function function_exists;
use function getenv;
use ReflectionClass;

if (!function_exists('Baka\appPath')) {
    /**
     * Get the application path.
     *
     * @param  string $path
     *
     * @return string
     */
    function appPath(string $path = '') : string
    {
        $currentDir = dirname(dirname(getcwd())) . ($path ? DIRECTORY_SEPARATOR . $path : $path);

        /**
         * since we are calling this file from the diferent path we have to verify if its cli.
         *
         * @todo look for a better solution , hate this
         */
        if (php_sapi_name() == 'cli') {
            $currentDir = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        return $currentDir;
    }
}

if (!function_exists('Baka\envValue')) {
    /**
     * Gets a variable from the environment, returns it properly formatted or the
     * default if it does not exist.
     *
     * @param string     $variable
     * @param mixed|null $default
     *
     * @return mixed
     */
    function envValue(string $variable, $default = null)
    {
        $return = $default;
        $value = getenv($variable);
        $values = [
            'false' => false,
            'true' => true,
            'null' => null,
        ];

        if (false !== $value) {
            $return = $values[$value] ?? $value;
        }

        return $return;
    }
}

if (!function_exists('Baka\appUrl')) {
    /**
     * Constructs a URL for links with resource and id.
     *
     * @param string $resource
     * @param int    $recordId
     *
     * @return array|false|mixed|string
     */
    function appUrl(string $resource, int $recordId)
    {
        return sprintf(
            '%s/%s/%s',
            envValue('APP_URL'),
            $resource,
            $recordId
        );
    }
}

if (!function_exists('Baka\paymentGatewayIsActive')) {
    /**
     * Do we have a payment metho actived on the app?
     *
     * @return boolean
     */
    function paymentGatewayIsActive() : bool
    {
        return !empty(getenv('STRIPE_SECRET')) ? true : false;
    }
}

if (!function_exists('Baka\isJson')) {
    /**
     * Given a string determine if its a json.
     *
     * @param string $string
     *
     * @return boolean
     */
    function isJson(string $string) : bool
    {
        json_decode($string);
        return (bool ) (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('Baka\isSwooleServer')) {
    /**
     * Are we running a Swoole Server for this app?
     *
     * @return boolean
     */
    function isSwooleServer() : bool
    {
        return defined('ENGINE') && ENGINE === 'SWOOLE' ? true : false;
    }
}

if (!function_exists('Baka\getClassName')) {
    /**
     * Are we running a Swoole Server for this app?
     *
     * @return boolean
     */
    function getShortClassName(object $object) : string
    {
        return (new ReflectionClass($object))->getShortName();
    }
}
