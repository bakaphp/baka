<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddCustomFieldsv2 extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('audits_details', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('old_value', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'field_name',
            ])
            ->changeColumn('old_value_text', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_value',
            ])
            ->changeColumn('new_value', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_value_text',
            ])
            ->changeColumn('new_value_text', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'new_value',
            ])
            ->save();

        $this->table('leads', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('email', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
            ->save();

        $this->table('apps_custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'identity' => 'enable',
                'precision' => '20',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->addColumn('model_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('entity_id', 'biginteger', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_BIG,
                'after' => 'model_name',
                'precision' => '20',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entity_id',
            ])
            ->addColumn('label', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('value', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'label',
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
            ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->addIndex(['model_name'], [
                'name' => 'model_name',
                'unique' => false,
            ])
            ->addIndex(['entity_id'], [
                'name' => 'entity_id',
                'unique' => false,
            ])
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->addIndex(['label'], [
                'name' => 'label',
                'unique' => false,
            ])
            ->addIndex(['companies_id', 'model_name', 'entity_id'], [
                'name' => 'companies_id_model_name_entity_id',
                'unique' => false,
            ])
            ->addIndex(['model_name', 'entity_id'], [
                'name' => 'model_name_2',
                'unique' => false,
            ])
            ->addIndex(['model_name', 'entity_id', 'name'], [
                'name' => 'model_name_3',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['companies_id', 'model_name', 'entity_id', 'name'], [
                'name' => 'companies_id_model_name_entity_id_name',
                'unique' => false,
            ])
            ->create();
    }
}
