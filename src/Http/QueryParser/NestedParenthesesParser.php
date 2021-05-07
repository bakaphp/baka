<?php

namespace Baka\Http\QueryParser;

class NestedParenthesesParser
{
    // something to keep track of parens nesting
    protected array $stack = [];
    // current level
    protected array $currentScope = [];
    // input string to parse
    protected string $query = '';
    // query split
    protected ?array $querySplit = null;
    // current character offset in string
    protected int $currentPosition = 0;

    protected ?string $lastJoiner = null;
    // start of text-buffer
    protected ?int $bufferStartAt = null;
    protected int $length = 0;

    // Ignore current char meaning on the iteration
    protected bool $ignoreMode = false;

    protected array $additionalQueryFields = [];

    /**
     * Convert query string to associated parse array.
     *
     * @param string $query
     *
     * @return array
     */
    public function parse(string $query) : array
    {
        if (!$query) {
            // no string, no data
            return [];
        }

        $this->currentScope = [];
        $this->stack = [];
        $this->query = $query;
        $this->querySplit = mb_str_split($query);
        $this->length = mb_strlen($this->query);

        foreach ($this->querySplit as $currentPosition => $value) {
            $this->currentPosition = $currentPosition;

            if (QueryParser::QUOTE_CHAR == $value) {
                $this->ignoreMode = !$this->ignoreMode;
            }

            if ($this->ignoreMode) {
                continue;
            }

            if (QueryParser::isAValidJoiner($value)) {
                $this->lastJoiner = $value;
                $this->push();
                continue;
            }

            switch ($value) {
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

        $this->overwriteCurrentScope();
        return $this->currentScope;
    }

    /**
     * Add elements to the current scope.
     *
     * @return void
     */
    protected function push() : void
    {
        if (null === $this->bufferStartAt) {
            return;
        }
        // extract string from buffer start to current currentPosition
        $buffer = mb_substr($this->query, $this->bufferStartAt, $this->currentPosition - $this->bufferStartAt);
        // clean buffer
        $this->bufferStartAt = null;

        preg_match('/^[._a-zA-Z0-9]+/', $buffer, $matches);

        // throw token into current scope
        $this->currentScope[] = [
            'comparison' => $buffer,
            'joiner' => $this->lastJoiner,
            'key' => !empty($matches) ? $matches[0] : null
        ];
    }

    /**
     * Overwrite the current scope by adding the additional query fields.
     *
     * @return void
     */
    protected function overwriteCurrentScope() : void
    {
        foreach ($this->currentScope as $index => $currentScope) {
            foreach ($currentScope as $key => $value) {
                if (isset($this->additionalQueryFields[$value['key']])) {
                    $this->currentScope[$index][$key] = $this->additionalQueryFields[$value['key']];

                    //if we overwrite remove
                    unset($this->additionalQueryFields[$value['key']]);
                }
            }
        }

        //add additional queries if we didn't overwrite
        if (isset($this->currentScope[0])) {
            /**
             * Overwrite the last joiner when combining query and additional params
             * by leaving it blank we use the default query behavior AND.
             */
            $this->currentScope[0][count($this->currentScope[0]) - 1]['joiner'] = '';
            $this->currentScope[0] = (array_values(($this->currentScope[0] + $this->additionalQueryFields)));
        }
    }

    /**
     * Set additional params.
     *
     * @param array $additionalQueryFields
     *
     * @return void
     */
    public function setAdditionalQueryFields(array $additionalQueryFields)
    {
        $newAdditionalQueryFields = [];

        /**
         * Additional Params
         * ['follows_rs.users_id', ':', $this->userData->getId()],
         *  0 -> key
         *  1 -> operation
         *  2 -> value
         *  3 -> join operator.
         */
        foreach ($additionalQueryFields as $query) {
            $newAdditionalQueryFields[$query[0]] = [
                'comparison' => implode('', array_slice($query, 0, 3)),
                'joiner' => isset($query[3]) && QueryParser::isAValidJoiner($query[3]) ? $query[3] : ',',
                'key' => $query[0]
            ];
        }

        $this->additionalQueryFields = $newAdditionalQueryFields;
    }
}
