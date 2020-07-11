<?php

namespace Baka;

use function function_exists;
use function getenv;
use JsonException;
use ReflectionClass;

if (!function_exists('Baka\basePath')) {
    /**
     * Get the application base path.
     *
     * @return string
     */
    function basePath() : string
    {
        if ($basePath = getenv('APP_BASE_PATH')) {
            return $basePath;
        }

        if (php_sapi_name() == 'cli') {
            return getcwd();
        }

        return  dirname(dirname(getcwd()));
    }
}

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
        $currentDir = basePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);

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
        try {
            json_decode($string);
            return true;
        } catch (JsonException $e) {
            return false;
        }
    }
}

if (!function_exists('Baka\json_decode')) {
    /**
     * Decode a JSON string into an array.
     *
     * @return array
     *
     * @throws JsonException
     */
    function json_decode(string $json)
    {
        return \json_decode($json, $assoc = true, $depth = 512, JSON_THROW_ON_ERROR);
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

if (!function_exists('Baka\getShortClassName')) {
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
