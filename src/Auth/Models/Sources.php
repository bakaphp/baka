<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Baka\Http\Exception\NotFoundException;

class Sources extends Model
{
    public string $title;
    public string $url;
    public int $pv_order;
    public int $ep_order;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->hasMany('id', 'Baka\Auth\Models\UserLinkedSources', 'source_id', ['alias' => 'linkedSource']);
    }

    /**
     * Get a source by its title.
     */
    public static function getByTitle(string $title) : Sources
    {
        $sourceData = self::findFirstByTitle($title);

        if (!$sourceData) {
            throw new NotFoundException(_('Importing site is not currently supported.'));
        }

        return $sourceData;
    }
}
