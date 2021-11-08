<?php

declare(strict_types=1);

namespace Baka\Contracts\Response;

use Baka\Http\Response\Phalcon as Response;
use function Baka\isSwooleServer;
use Phalcon\Mvc\Micro;

/**
 * Trait ResponseTrait.
 *
 * @package Canvas\Traits
 */
trait ResponseTrait
{
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
