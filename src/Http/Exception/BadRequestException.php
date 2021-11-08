<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

/**
 * Using this exception when the user is trying to process something incorrectly
 * - Form validation
 * - Login validation.
 */
class BadRequestException extends HttpException
{
    protected int $httpCode = Response::BAD_REQUEST;
    protected string $httpMessage = 'Bad Request';
    protected string $severity = 'error';
}
