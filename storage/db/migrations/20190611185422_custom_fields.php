<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CustomFields extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('custom_fields', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'identity' => 'enable'])
            ->addColumn('companies_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'id'])
            ->addColumn('user_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'companies_id'])
            ->addColumn('apps_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'user_id'])
            ->addColumn('custom_fields_modules_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'apps_id'])
            ->addColumn('fields_type_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'custom_fields_modules_id'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after' => 'id','after'=> 'fields_type_id'])
            ->addColumn('label', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after' => 'id','after'=> 'name'])
            ->create();


        $table = $this->table('custom_fields_settings', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11])
            ->addColumn('companies_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'id'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'companies_id'])
            ->addColumn('value', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'name'])
            ->create();

        $table = $this->table('custom_fields_types', ['name' => false, 'primary_key' => ['name'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4'])
            ->addColumn('description', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4', 'after'=> 'name'])
            ->addColumn('icon', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'description'])
            ->create();

        $table = $this->table('custom_fields_types_settings', ['custom_fields_types_id' => false, 'primary_key' => ['custom_fields_types_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('custom_fields_types_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'custom_fields_types_id'])
            ->addColumn('value', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'name'])
            ->create();

        $table = $this->table('custom_fields_values', ['custom_fields_id' => false, 'primary_key' => ['custom_fields_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('custom_fields_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11])
            ->addColumn('value', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'custom_fields_id'])
            ->addColumn('is_default', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'value'])
            ->create();

        $table = $this->table('custom_fields_modules', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '', 'row_format' => 'Dynamic']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11])
            ->addColumn('companies_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 11,'after'=> 'id'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'companies_id'])
            ->addColumn('value', 'string', ['null' => false, 'limit' => 64, 'collation' => 'utf8mb4_unicode_ci', 'encoding' => 'utf8mb4','after'=> 'name'])
            ->create();
    }
}
