<?php
declare(strict_types=1);

namespace Baka\Contracts\Database;

use Phalcon\Mvc\Model\ResultsetInterface;

interface ModelInterface
{
    public function getId();
    
    public static function getByIdOrFail($id) : ModelInterface;

    public static function findFirstOrFail($parameters = null) : ModelInterface;

    public static function findOrFail($parameters = null) : ResultsetInterface;

    public function saveOrFail($data = null, $whiteList = null) : bool;

    public function updateOrFail($data = null, $whiteList = null) : bool;

    public static function findFirstOrCreate($parameters = null, array $fields = []) : ModelInterface;

    public static function updateOrCreate($parameters = null, array $fields = []) : ModelInterface;

    public function deleteOrFail() : bool;

    public function softDelete();
}
