<?php

declare(strict_types=1);

namespace Baka\Contracts\Request;

use Baka\Support\Str;

trait RequestJwtTrait
{
    /**
     * @return string
     */
    public function getBearerTokenFromHeader() : string
    {
        if (Str::contains($this->getHeader('Authorization'), 'Basic')) {
            return '';
        }

        return str_replace(['Bearer ', 'Authorization'], '', $this->getHeader('Authorization'));
    }

    /**
     * @return bool
     */
    public function isEmptyBearerToken() : bool
    {
        return empty($this->getBearerTokenFromHeader());
    }

    abstract public function getHeader(string $header) : string;
}
