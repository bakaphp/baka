<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

/**
 * Critical error from the app , will send alerts.
 */
class InternalServerErrorException extends HttpException
{
    protected $httpCode = Response::INTERNAL_SERVER_ERROR;
    protected $httpMessage = 'Internal Server Error';
    protected $severity = 'emergency';
}
