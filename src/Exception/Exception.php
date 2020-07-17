<?php

declare(strict_types=1);

namespace Baka\Exception;

use Phalcon\Exception as PhException;

class Exception extends PhException
{
    protected array $data = [];

    /**
     * Creates a new instance of Exception.
     *
     * @param string $message
     * @param string $field
     *
     * @return self
     */
    public static function create(string $message, ...$field) : self
    {
        $e = new self($message);
        $e->data = $field;

        return $e;
    }
    
    /**
     * Get Data Information
     */
    public function getData() : ?array
    {
        return $this->data;
    }    
}
