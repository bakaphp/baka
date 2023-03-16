<?php

declare(strict_types=1);

namespace Baka\Http\Response;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Request\Phalcon as Request;
use Error;
use Phalcon\Http\Response;
use Throwable;
use function Baka\envValue;

class Phalcon extends Response
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENTLY_REDIRECT = 308;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const NOT_ACCEPTABLE = 406;
    const SESSION_NOT_FOUND = 499;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const UNPROCESSABLE_ENTITY = 422;

    private array $codes = [
        200 => 'OK',
        301 => 'Moved Permanently',
        302 => 'Found',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        422 => 'Unprocessable Entity',
        499 => 'Session Not Found',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
    ];

    /**
     * Returns the http code description or if not found the code itself.
     *
     * @param int $code
     *
     * @return int|string
     */
    public function getHttpCodeDescription(int $code)
    {
        if (true === isset($this->codes[$code])) {
            return sprintf('%d (%s)', $code, $this->codes[$code]);
        }

        return $code;
    }

    /**
     * Send the response back.
     *
     * @return self
     */
    public function send() : self
    {
        $content = $this->getContent();
        $data = $content;
        $eTag = sha1($content);

        /**
         * At the moment we are only using this format for error msg.
         *
         * @todo change in the future to implements other formats
         */
        if ($this->isServerError() || $this->isClientError()) {
            $timestamp = date('c');
            $hash = sha1($timestamp . $content);

            /** @var array $content */
            $content = json_decode($this->getContent(), true);

            if (!is_array($content)) {
                $content = ['message' => $content];
            }

            $jsonapi = [
                'jsonapi' => [
                    'version' => '1.0',
                ],
            ];
            $meta = [
                'meta' => [
                    'timestamp' => $timestamp,
                    'hash' => $hash,
                ]
            ];

            /**
             * Join the array again.
             */
            $data = $jsonapi + $content + $meta;

            $this->setJsonContent($data);
        }

        $this->setHeader('E-Tag', $eTag);

        return parent::send();
    }

    /**
     * Sets the payload code as Error.
     *
     * @param string $detail
     *
     * @return self
     */
    public function setPayloadError(string $detail = '') : self
    {
        $this->setJsonContent([
            'errors' => [
                'message' => $detail,
                'type' => $this->codes[404]
            ]
        ]);

        return $this;
    }

    /**
     * Traverses the errors collection and sets the errors in the payload.
     *
     * @param mixed $errors
     *
     * @return self
     */
    public function setPayloadErrors($errors) : self
    {
        $data = [];
        foreach ($errors as $error) {
            $data[] = $error->getMessage();
        }

        $this->setJsonContent(['errors' => $data]);

        return $this;
    }

    /**
     * Sets the payload code as Success.
     *
     * @param null|string|array $content The content
     *
     * @return self
     */
    public function setPayloadSuccess($content = []) : self
    {
        $data = is_array($content) ? $content : ['data' => $content];
        $data = isset($data['data']) ? $data : ['data' => $data];

        $this->setJsonContent($data);

        return $this;
    }

    /**
     * Handle the exception we throw from our api.
     *
     * @param Throwable $e
     *
     * @return self
     */
    public function handleException(Throwable $e) : self
    {
        $request = new Request();
        $identifier = $request->getServerAddress();
        $config = $this->getDI()->get('config');

        $httpCode = (method_exists($e, 'getHttpCode')) ? $e->getHttpCode() : 500;
        $httpMessage = (method_exists($e, 'getHttpMessage')) ? $e->getHttpMessage() : 'Internal Server Error';
        $httpSeverity = (method_exists($e, 'getHttpSeverity')) ? $e->getHttpSeverity() : 'error';
        $data = (method_exists($e, 'getData')) ? $e->getData() : [];

        $this->setHeader('Access-Control-Allow-Origin', '*'); //@todo check why this fails on nginx
        $this->setStatusCode($httpCode, $httpMessage);
        $this->setContentType('application/json');
        $this->setJsonContent([
            'errors' => [
                'type' => $httpMessage,
                'severity' => $httpSeverity,
                'identifier' => $identifier,
                'message' => $e->getMessage(),
                'trace' => !$config->app->production ? $e->getTraceAsString() : null,
                'data' => !$config->app->production ? $data : null,
            ],
        ]);

        //Log Errors or Internal Servers Errors in Production
        if (($e instanceof InternalServerErrorException || $e instanceof Error) && (bool) envValue('SENTRY_PROJECT', 0)) {
            $this->getDI()->get('log')->$httpSeverity($e->getMessage(), [$e->getTraceAsString()]);
        }

        return $this;
    }

    /**
     * Is the current response a error response?
     * Error response are anything over a 400 code.
     *
     * @return bool
     */
    public function isServerError() : bool
    {
        return $this->getStatusCode() >= 500;
    }

    /**
     * Client errors.
     *
     * @return bool
     */
    public function isClientError() : bool
    {
        return $this->getStatusCode() > 400 && $this->getStatusCode() < 500;
    }
}
