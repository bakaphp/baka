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
    public function getPostData(): array
    {
        $data = $this->getPost() ?: $this->getJsonRawBody(true);

        return $data ?: [];
    }

    /**
     * Get the data from a POST request.
     *
     * @return void
     */
    public function getPutData()
    {
        $data = $this->getPut() ?: $this->getJsonRawBody(true);

        return $data ?: [];
    }

    /**
     * Is this request paginated?
     *
     * @return boolean
     */
    public function withPagination() : bool
    {
        return $this->getQuery('format', 'string') == 'true';
    }

    /**
     * Is this a request requesting relationships
     *
     * @return boolean
     */
    public function withRelationships() : bool
    {
        return $this->hasQuery('relationships');
    }
}
