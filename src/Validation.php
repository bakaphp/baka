<?php

declare(strict_types=1);

namespace Baka;

use Baka\Http\Exception\UnprocessableEntityException;
use Phalcon\Messages\Messages;
use Phalcon\Validation as PhalconValidation;
use Phalcon\Validation\ValidationInterface;
use Phalcon\Validation\ValidatorInterface;

/**
 * Class Validation.
 *
 * @package Baka
 */
class Validation extends PhalconValidation
{
    protected array $fields = [];

    /**
     *
     * Overwrite to throw the exception and avoid all the overloaded code
     * Validate a set of data according to a set of rules.
     *
     * @param array|object data
     * @param object entity
     *
     * @return Messages
     */
    public function validate($data = null, $entity = null) : Messages
    {
        $validate = parent::validate($data, $entity);

        if (count($validate)) {
            throw new UnprocessableEntityException($validate[0]->getMessage());
        }

        return $validate;
    }

    /**
     * Overwrite parent.
     *
     * @param mixed $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     */
    public function add($field, ValidatorInterface $validator) : ValidationInterface
    {
        $this->fields[] = $field;
        return parent::add($field, $validator);
    }

    /**
     * Get all the validated values from the validator.
     *
     * @return array
     */
    public function getValues() : array
    {
        $values = [];
        foreach ($this->fields as $field) {
            $values[$field] = $this->getValue($field);
        }

        return $values;
    }
}
