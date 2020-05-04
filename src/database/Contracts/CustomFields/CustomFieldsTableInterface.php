<?php

namespace Baka\Database\Contracts\CustomFields;

/**
 * Trait to implemente everything needed from a simple CRUD in a API
 *
 */
interface CustomFieldsTableInterface
{
    /**
     * Set the custom primary field id
     *
     * @param int $id
     */
    public function setCustomId(int $id);
}
