<?php

namespace Baka\Http\Contracts\Api;

use function Baka\isSwooleServer;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;

trait ResponseTrait
{
    /**
     * Send a response when needed.
     *
     * @param mixed $content
     * @param int $statusCode
     * @param string $statusMessage
     *
     * @return Response
     *
     */
    protected function response($content, int $statusCode = 200, string $statusMessage = 'OK') : Response
    {
        $response = [
            'statusCode' => $statusCode,
            'statusMessage' => $statusMessage,
            'content' => $content,
        ];

        if ($this->config->application->debug->logRequest) {
            $this->log->addInfo('RESPONSE', $response);
        }

        //in order to use the current response instead of having to create a new object , this is needed for swoole servers
        //$response = $this->response ?? new Response();
        $this->response->setStatusCode($statusCode, $statusMessage);
        $this->response->setContentType('application/vnd.api+json', 'UTF-8');
        $this->response->setJsonContent($content);

        return $this->response;
    }

    /**
     * Halt execution after setting the message in the response.
     *
     * @param Micro  $api
     * @param int    $status
     * @param string $message
     *
     * @return mixed
     */
    protected function halt(Micro $api, int $status, string $message)
    {
        $apiResponse = !isSwooleServer() ? new Response() : $this->response;

        $apiResponse
            ->setPayloadError($message)
            ->setStatusCode($status)
            ->send();

        $api->stop();
    }
}
