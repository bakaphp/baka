<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Exception\HttpException;
use Baka\Http\Response\Phalcon as Response;

class UnprocessableEntityException extends HttpException
{
    protected int $httpCode = Response::UNPROCESSABLE_ENTITY;
    protected string $httpMessage = 'Unprocessable Entity';
    protected string $severity = 'alert';
}
