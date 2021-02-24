<?php

declare(strict_types=1);

namespace Baka\Http\Request;

use Phalcon\Http\Request;

class Phalcon extends Request
{
    protected bool $inputSanitize = false;

    /**
     * Get the data from a POST request.
     *
     * @return array
     */
    public function getPostData() : array
    {
        $data = $this->getPost() ?: $this->getJsonRawBody(true);

        return $this->filterSanitize($data) ?: [];
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

        return $this->filterSanitize($data) ?: [];
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
    protected function filterSanitize(array $data) : array
    {
        return $this->inputSanitize ?
            filter_var($data, FILTER_CALLBACK, ['options' => 'self::cleanUp']) :
            $data;
    }

    /**
     * Clean up the value.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    protected function cleanUp(?string $value) : ?string
    {
        return strlen($value) !== 0 ? trim($value) : null;
    }

    /**
     * Enable sanitize.
     *
     * @return void
     */
    public function enableSanitize() : void
    {
        $this->inputSanitize = true;
    }

    /**
     * Enable sanitize.
     *
     * @return void
     */
    public function disableSanitize() : void
    {
        $this->inputSanitize = false;
    }
}
