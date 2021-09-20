<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class UnauthorizedException extends HttpException
{
    protected $httpCode = Response::UNAUTHORIZED;
    protected $httpMessage = 'Unauthorized';
    protected $severity = 'warning';
}
