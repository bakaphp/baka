<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CustomFilters extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('custom_filters_conditions', ['custom_filter_id' => false, 'primary_key' => ['custom_filter_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('custom_filter_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11])
            ->addColumn('position', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'custom_filter_id'])
            ->addColumn('conditional', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'position'])
            ->addColumn('value', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'conditional'])
            ->create();


        $table = $this->table('custom_filters', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'identity' => 'enable'])
            ->addColumn('system_modules_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'id'])
            ->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'system_modules_id'])
            ->addColumn('apps_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'users_id'])
            ->addColumn('companies_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'apps_id'])
            ->addColumn('companies_branch_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'companies_id'])
            ->addColumn('fields_type_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'companies_branch_id'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'fields_type_id'])
            ->addColumn('sequence_logic', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'name'])
            ->addColumn('description', 'string', ['null' => false, 'limit' => 200, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'sequence_logic'])
            ->addColumn('total_conditions', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'description'])
            ->create();
    }
}
