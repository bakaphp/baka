<?php

/**
 * thanks to https://github.com/limingxinleo.
 */

namespace Baka\Http\Response;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Request\Swoole as RequestSwoole;
use Baka\Http\Response\Phalcon as PhResponse;
use Error;
use Exception;
use Phalcon\Di;
use Phalcon\Http\Cookie;
use swoole_http_response;
use Throwable;

class Swoole extends PhResponse
{
    protected $response;

    /**
     * Set the swoole response object.
     *
     * @param swoole_http_response $response
     *
     * @return void
     */
    public function init(swoole_http_response $response) : void
    {
        $this->response = $response;
        $this->_sent = false;
        $this->_content = null;
        $this->setStatusCode(200);
    }

    /**
     * Send the response.
     *
     * @return PhResponse
     */
    public function send() : PhResponse
    {
        if ($this->_sent) {
            throw new Exception('Response was already sent');
        }

        $this->_sent = true;
        // get phalcon headers
        $headers = $this->getHeaders();

        foreach ($headers->toArray() as $key => $val) {
            //if the key has spaces this breaks postman, so we remove this headers
            //example: HTTP/1.1 200 OK || HTTP/1.1 401 Unauthorized
            if (!preg_match('/\s/', $key)) {
                $this->response->header($key, $val);
            }
        }

        /** @var Cookies $cookies */
        $cookies = $this->getCookies();
        if ($cookies) {
            /** @var Cookie $cookie */
            foreach ($cookies->getCookies() as $cookie) {
                $this->response->cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiration(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->getSecure(),
                    $cookie->getHttpOnly()
                );
            }
        }

        //set swoole response
        $this->response->status($this->getStatusCode());
        $this->response->end($this->getContent());

        //reset di
        $this->resetDi();

        return $this;
    }

    /**
     * Handle the exception we throw from our api.
     *
     * @param Throwable $e
     *
     * @return Response
     */
    public function handleException(Throwable $e) : PhResponse
    {
        //reset di
        $request = new RequestSwoole();
        $identifier = $request->getServerAddress();
        $config = Di::getDefault()->getConfig();

        $httpCode = (method_exists($e, 'getHttpCode')) ? $e->getHttpCode() : 404;
        $httpMessage = (method_exists($e, 'getHttpMessage')) ? $e->getHttpMessage() : 'Not Found';
        $data = (method_exists($e, 'getData')) ? $e->getData() : [];

        $this->setHeader('Access-Control-Allow-Origin', '*'); //@todo check why this fails on nginx
        $this->setStatusCode($httpCode, $httpMessage);
        $this->setContentType('application/json');
        $this->setJsonContent([
            'errors' => [
                'type' => $httpMessage,
                'identifier' => $identifier,
                'message' => $e->getMessage(),
                'trace' => !$config->app->production ? $e->getTraceAsString() : null,
                'data' => !$config->app->production ? $data : null,
            ],
        ]);

        //Log Errors or Internal Servers Errors in Production
        if ($e instanceof InternalServerErrorException ||
            $e instanceof Error ||
            $config->app->production) {
            Di::getDefault()->getLog()->$e->getHttpSeverity()($e->getMessage(), [$e->getTraceAsString()]);
        }

        $this->resetDi();

        return $this;
    }

    /**
     * Given Swoole behavior we need to reset the DI and Close the DB connection
     * What happens if you don't do this? You will cache the DI request and always get the same info ;).
     *
     * @return void
     */
    protected function resetDi() : void
    {
        $this->_sent = false;
        $this->getDi()->get('db')->close();
        $this->getDi()->reset();
    }
}
