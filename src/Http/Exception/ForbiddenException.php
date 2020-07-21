<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Http\Response\Phalcon as Response;
use Baka\Exception\HttpException;

class ForbiddenException extends HttpException
{
    protected $httpCode = Response::FORBIDDEN;
    protected $httpMessage = 'Forbidden';
}
