<?php

namespace Baka\Router\Utils;

class Helper
{
    /**
     * Trim slashes.
     *
     * @param string $str
     * @return string
     */
    public static function trimSlahes(string $str): string
    {
        return trim($str, '/');
    }
}
