<?php

declare(strict_types=1);

namespace Baka\Exception;

use Baka\Http\Response\Phalcon as Response;

class HttpException extends Exception
{
    protected int $httpCode = Response::BAD_REQUEST;
    protected string $httpMessage = 'Bad Request';
    protected string $severity = 'notice';

    /**
     * Get the http status code of the exception.
     *
     * @return string
     */
    public function getHttpCode() : int
    {
        return $this->httpCode;
    }

    /**
     * Get the message string from the exception.
     *
     * @return string
     */
    public function getHttpMessage() : string
    {
        return $this->httpMessage;
    }

    /**
     * Get the severity of the exception.
     *
     * @return string
     */
    public function getHttpSeverity() : string
    {
        return $this->severity;
    }
}
