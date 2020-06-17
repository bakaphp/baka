<?php

namespace Baka\Blameable;

use Baka\Database\Model;

class AuditsDetails extends Model
{
    public int $audits_id;
    public string $field_name;
    public ?string $old_value = null;
    public ?String $old_value_text = null;
    public ?string $new_value = null;
    public ?string $new_value_text = null;

    /**
     * Init.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('audits_details');
        $this->belongsTo('audits_id', '\Baka\Blameable\Audits', 'id');
    }
}
