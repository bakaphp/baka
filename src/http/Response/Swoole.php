<?php

/**
 * thanks to https://github.com/limingxinleo.
 */

namespace Baka\Http\Response;

use Exception;
use Phalcon\Http\Cookie;
use Phalcon\Http\Response as PhResponse;
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
        $this->response->end($this->_content);

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
    public function handleException(Throwable $e) : Response
    {
        //reset di
        $response = parent::handleException($e);
        $this->resetDi();

        return $response;
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
