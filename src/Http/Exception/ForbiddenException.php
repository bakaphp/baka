<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class ForbiddenException extends HttpException
{
    protected int $httpCode = Response::FORBIDDEN;
    protected string $httpMessage = 'Forbidden';
    protected string $severity = 'warning';
}
