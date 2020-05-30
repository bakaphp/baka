<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Http\Response\Phalcon as Response;
use Baka\Exception\HttpException;

class UnprocessableEntityException extends HttpException
{
    protected $httpCode = Response::UNPROCESSABLE_ENTITY;
    protected $httpMessage = 'Unprocessable Entity';
    protected $data;
}
