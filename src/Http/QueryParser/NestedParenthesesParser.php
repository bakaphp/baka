<?php

namespace Baka\Http\QueryParser;

class NestedParenthesesParser
{
    // something to keep track of parens nesting
    protected $stack = null;
    // current level
    protected $currentScope = null;
    // input string to parse
    protected $query = null;
    // current character offset in string
    protected $currentPosition = null;

    protected $lastJoiner = null;
    // start of text-buffer
    protected $bufferStartAt = null;

    // Ignore current char meaning on the iteration
    protected $ignoreMode = false;

    public function parse($query)
    {
        if (!$query) {
            // no string, no data
            return [];
        }

        $this->currentScope = [];
        $this->stack = [];
        $this->query = $query;

        $this->length = mb_strlen($this->query);

        // look at each character
        for ($this->currentPosition = 0; $this->currentPosition < $this->length; ++$this->currentPosition) {
            if (QueryParser::QUOTE_CHAR == $this->query[$this->currentPosition]) {
                $this->ignoreMode = !$this->ignoreMode;
            }

            if ($this->ignoreMode) {
                continue;
            }

            if (QueryParser::isAValidJoiner($this->query[$this->currentPosition])) {
                $this->lastJoiner = $this->query[$this->currentPosition];
                $this->push();
                continue;
            }
            switch ($this->query[$this->currentPosition]) {
                case '(':
                    $this->push();
                    // push current scope to the stack an begin a new scope
                    array_push($this->stack, $this->currentScope);
                    $this->currentScope = [];
                    break;
                case ')':
                    $this->push();
                    // save current scope
                    $t = $this->currentScope;
                    // get the last scope from stack
                    $this->currentScope = array_pop($this->stack);
                    // add just saved scope to current scope
                    $this->currentScope[] = $t;
                    break;
                default:
                    // remember the offset to do a string capture later
                    // could've also done $buffer .= $query[$currentPosition]
                    // but that would just be wasting resources
                    if (null === $this->bufferStartAt) {
                        $this->bufferStartAt = $this->currentPosition;
                    }
            }
        }

        if ($this->bufferStartAt < $this->length) {
            $this->push();
        }

        return $this->currentScope;
    }

    protected function push()
    {
        if (null === $this->bufferStartAt) {
            return;
        }
        // extract string from buffer start to current currentPosition
        $buffer = mb_substr($this->query, $this->bufferStartAt, $this->currentPosition - $this->bufferStartAt);
        // clean buffer
        $this->bufferStartAt = null;
        // throw token into current scope
        $this->currentScope[] = [
            'comparison' => $buffer,
            'joiner' => $this->lastJoiner,
        ];
    }
}
