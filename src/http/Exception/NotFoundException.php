<?php

declare(strict_types=1);

namespace Baka\Http\Exception;

use Baka\Http\Response\Phalcon as Response;
use Baka\Exception\HttpException;

class NotFoundException extends HttpException
{
    protected $httpCode = Response::NOT_FOUND;
    protected $httpMessage = 'Not Found';
    protected $data;
}
