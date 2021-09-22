<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class UnauthorizedException extends HttpException
{
    protected string $httpCode = Response::UNAUTHORIZED;
    protected string $httpMessage = 'Unauthorized';
    protected string $severity = 'warning';
}
