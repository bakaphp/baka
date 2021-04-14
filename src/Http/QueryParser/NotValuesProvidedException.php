<?php

namespace Baka\Http\QueryParser;

use Exception;

class NotValuesProvidedException extends Exception
{
    /**
     * Constructor.
     *
     * @param string     $field
     * @param int        $code
     * @param Exception $previous
     */
    public function __construct(string $message = 'Not values provided to compare.', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
