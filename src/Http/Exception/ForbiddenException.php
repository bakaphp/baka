<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class ForbiddenException extends HttpException
{
    protected $httpCode = Response::FORBIDDEN;
    protected $httpMessage = 'Forbidden';
    protected $severity = 'warning';
}
