<?php

declare(strict_types=1);

namespace Baka\Http\Request;

use Phalcon\Http\Request;

class Phalcon extends Request
{
    /**
     * Get the data from a POST request.
     *
     * @return array
     */
    public function getPostData() : array
    {
        $data = $this->getPost() ?: $this->getJsonRawBody(true);

        return $this->cleanUp($data) ?: [];
    }

    /**
     * Get the data from a POST request.
     *
     * @return void
     */
    public function getPutData() : array
    {
        $data = $this->getPut() ?: $this->getJsonRawBody(true);

        /**
         * @todo get help from phalcon
         * using browserkit with Phalcon4 + Put we have to relay on
         * $_REQUEST
         */
        $data = $data ?: $this->get();

        return $this->cleanUp($data) ?: [];
    }

    /**
     * Is this request paginated?
     *
     * @return bool
     */
    public function withPagination() : bool
    {
        return $this->getQuery('format', 'string') == 'true';
    }

    /**
     * Is this a request requesting relationships.
     *
     * @return bool
     */
    public function withRelationships() : bool
    {
        return $this->hasQuery('relationships');
    }

    /**
     * Clean up input data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function cleanUp(array $data) : array
    {
        return filter_var($data, FILTER_CALLBACK, ['options' => 'trim']);
    }
}
