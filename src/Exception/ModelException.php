<?php

declare(strict_types=1);

namespace Baka\Exception;

use Baka\Http\Response\Phalcon as Response;

/**
 * @deprecated version 0.1.5
 */
class ModelException extends HttpException
{
    protected int $httpCode = Response::NOT_FOUND;
    protected string $httpMessage = 'Not Found';
}
