<?php
declare(strict_types=1);

namespace Baka\Contracts\CustomFields;

/**
 * Trait to implemented everything needed from a simple CRUD in a API.
 *
 * @deprecated version 1
 */
interface CustomFieldsTableInterface
{
    /**
     * Set the custom primary field id.
     *
     * @param int $id
     */
    public function setCustomId(int $id);
}
