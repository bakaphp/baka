<?php

namespace Baka\Http\QueryParser;

use Exception;

class NotQueryableFieldException extends Exception
{
    /**
     * @var string
     */
    protected $field;

    /**
     * Constructor.
     *
     * @param string     $field
     * @param int        $code
     * @param Exception $previous
     */
    public function __construct(string $field, $code = 0, Exception $previous = null)
    {
        $this->field = $field;
        parent::__construct('Field is not queryable: ' . $this->getField(), $code, $previous);
    }

    /**
     * Get the field which can not be queried.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
