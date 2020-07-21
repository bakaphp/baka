<?php

namespace Baka\Database;

class SystemModules extends Model
{
    public string $name;
    public string $slug;
    public string $model_name;
    public int $apps_id;
    public int $parents_id;
    public ?int $menu_order = null;
    public int $use_elastic;
    public ?string $browse_fields = null;
    public int $show;
}
