<?php

namespace Baka\Database;

class SystemModules extends Model
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $model_name = null;
    public ?int $apps_id = null;
    public int $parents_id = 0;
    public ?int $menu_order = null;
    public int $use_elastic = 0;
    public ?string $browse_fields = null;
    public int $show = 0;
}
