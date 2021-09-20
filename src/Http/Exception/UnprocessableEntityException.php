<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class UnprocessableEntityException extends HttpException
{
    protected $httpCode = Response::UNPROCESSABLE_ENTITY;
    protected $httpMessage = 'Unprocessable Entity';
    protected $severity = 'alert';
}
