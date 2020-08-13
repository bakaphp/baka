<?php
declare(strict_types=1);

namespace Baka\Contracts\CustomFields;

use Phalcon\Mvc\ModelInterface;

interface CustomFieldModelInterface
{
    public function getCustomFieldPrimaryKey() : string;

    public function getCustomFields() : array;

    public function getAllCustomFields() : array;

    public function getAll() : array;

    public function getCustomField(string $name) : ?ModelInterface;

    public function deleteAllCustomFields() : bool;

    public function setCustomFields(array $fields) : void;

    public function hasCustomFields() : bool;

    public function reCacheCustomFields() : void;
}
