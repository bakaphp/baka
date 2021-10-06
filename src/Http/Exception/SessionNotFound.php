<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class SessionNotFound extends HttpException
{
    /**
     * Code 499 , is a specific http code for kanvas frontend,
     * when they see this error they will know they have to clear user session on device.
     */
    protected int $httpCode = Response::SESSION_NOT_FOUND;
    protected string $httpMessage = 'Unauthorized';
    protected string $severity = 'warning';
}
