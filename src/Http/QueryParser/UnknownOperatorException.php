<?php

namespace Baka\Http\QueryParser;

use Exception;

class UnknownOperatorException extends Exception
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * Constructor.
     *
     * @param string     $operator
     * @param int        $code
     * @param Exception $previous
     */
    public function __construct(string $operator, $code = 0, Exception $previous = null)
    {
        $this->operator = $operator;
        parent::__construct('Unknown operator: ' . $this->getOperator(), $code, $previous);
    }

    /**
     * Get the operator which can not be queried.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }
}
