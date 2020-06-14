<?php

namespace Baka\Database;

class SystemModules extends Model
{
    public string $name;
    public string $slug;
    public string $model_name;
    public int $apps_id;
    public int $parents_id;
    public ?int $menu_order;
    public int $use_elastic;
    public string $browse_fields;
    public int $show;
}
