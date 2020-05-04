<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class OtherModels extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('apps', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'identity' => 'enable'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after' => 'id','after'=> 'name'])
            ->addColumn('description', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after' => 'id','after'=> 'description'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'description'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->addColumn('is_deleted', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'updated_at'])
            ->create();

        $table = $this->table('system_modules', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'identity' => 'enable'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'id'])
            ->addColumn('slug', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'name'])
            ->addColumn('model_name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'slug'])
            ->addColumn('menu_order', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'model_name'])
            ->addColumn('browse_fields', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'menu_order'])
            ->addColumn('apps_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'browse_fields'])
            ->addColumn('parents_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'apps_id'])
            ->addColumn('use_elastic', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'parents_id'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'use_elastic'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->addColumn('is_deleted', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'updated_at'])
            ->create();

        $table = $this->table('leads', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'identity' => 'enable'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'id'])
            ->addColumn('system_modules_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'name'])
            ->addColumn('apps_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'system_modules_id'])
            ->addColumn('companies_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'apps_id'])
            ->addColumn('companies_branch_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'companies_id'])
            ->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'companies_branch_id'])
            ->addColumn('sequence_logic', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'users_id'])
            ->addColumn('description', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'sequence_logic'])
            ->addColumn('criterias', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'description'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'criterias'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->addColumn('is_deleted', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'updated_at'])
            ->create();
    
        $table = $this->table('leads_settings');
        $table->create();

        $table = $this->table('leads_custom_fields');
        $table->create();
    
    }
}
