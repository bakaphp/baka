<?php

declare(strict_types=1);

namespace Baka\Exception;

use Baka\Http\Response\Phalcon as Response;

class HttpException extends Exception
{
    protected $httpCode = Response::BAD_REQUEST;
    protected $httpMessage = 'Bad Request';
    protected $data;

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
     * Get the message DATA from the exception.
     *
     * @return string|null
     */
    public function getData() : ?array
    {
        return is_array($this->data) ? $this->data : [$this->data];
    }

    /**
     * Creates a new instance of Exception.
     *
     * @param string $message
     * @param string $field
     *
     * @return self
     */
    public static function create(string $message, ...$field) : self
    {
        $e = new self($message);
        $e->data = $field;

        return $e;
    }
}
