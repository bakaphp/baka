<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Http\Response\Phalcon as Response;
use Baka\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    protected $httpCode = Response::UNAUTHORIZED;
    protected $httpMessage = 'Unauthorized';
}
