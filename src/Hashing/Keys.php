<?php

declare(strict_types=1);

namespace Baka\Hashing;

use Phalcon\Security\Random;

class Keys
{
    /**
     * Given a length generate a save url Hash
     * based on Phalcon.
     *
     * @param int $length
     *
     * @return string
     */
    public static function make(int $length = 32) : string
    {
        $random = new Random();

        return $random->base64Safe($length);
    }
}
