<?php

namespace Baka\Test\Support\Models;

use Baka\Contracts\CustomFields\CustomFieldsTableInterface;
use Baka\Database\Model;

class LeadsCustomFields extends Model implements CustomFieldsTableInterface
{
    public function initialize()
    {
        $this->setSource('leads_custom_fields');
    }

    /**
     * Set the custom primary field id.
     *
     * @param int $id
     */
    public function setCustomId($id)
    {
        $this->leads_id = $id;
    }
}
