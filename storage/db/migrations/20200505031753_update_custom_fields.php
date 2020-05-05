<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateCustomFields extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('leads_custom_fields', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('leads_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('custom_fields_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'leads_id',
            ])
            ->addColumn('value', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'custom_fields_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'value',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->create();
        $this->table('leads_settings', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->create();

        $this->table('leads', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->changeColumn('firstname', 'string', [
                'null' => false,
                'default' => '\'\'',
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
            ->changeColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'firstname',
            ])
            ->changeColumn('lastname', 'string', [
                'null' => false,
                'default' => '\'\'',
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_id',
            ])
            ->changeColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'lastname',
            ])
            ->changeColumn('companies_branch_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->changeColumn('leads_owner_id', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_branch_id',
            ])
            ->changeColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'leads_owner_id',
            ])
            ->changeColumn('sequence_logic', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->changeColumn('description', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'sequence_logic',
            ])
            ->changeColumn('criterias', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'criterias',
            ])
            ->changeColumn('is_active', 'boolean', [
                'null' => true,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'created_at',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'is_active',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->save();
    }
}
