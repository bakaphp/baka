<?php

declare(strict_types=1);

namespace Baka;

use Baka\Http\Exception\UnprocessableEntityException;
use Phalcon\Validation as PhalconValidation;
use Phalcon\Messages\Messages;
/**
 * Class Validation.
 *
 * @package Baka
 */
class Validation extends PhalconValidation
{
    /**
     *
     * Overwrite to throw the exception and avoid all the overloaded code
     * Validate a set of data according to a set of rules.
     *
     * @param array|object data
     * @param object entity
     *
     * @return \Phalcon\Validation\Message\Group
     */
    public function validate($data = null, $entity = null) : Messages
    {
        $validate = parent::validate($data, $entity);

        if (count($validate)) {
            throw new UnprocessableEntityException($validate[0]->getMessage());
        }

        return $validate;
    }
}
