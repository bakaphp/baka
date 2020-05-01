<?php

namespace Baka\Router\Utils;

class Http
{
    const POST = 'post';
    const GET = 'get';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';

    const METHODS = [
        self::POST,
        self::GET,
        self::PUT,
        self::PATCH,
        self::DELETE,
    ];
}
