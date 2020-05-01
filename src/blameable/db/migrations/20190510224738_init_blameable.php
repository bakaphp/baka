<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class InitBlameable extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8mb4';");
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_520_ci';");
        
        $this->table('audits', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entity_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('model_name', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entity_id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'model_name',
            ])
            ->addColumn('ip', 'string', [
                'null' => false,
                'limit' => 15,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('type', 'char', [
                'null' => false,
                'limit' => 1,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'ip',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'type',
            ])
        ->addIndex(['entity_id'], [
                'name' => 'idx1',
                'unique' => false,
            ])
        ->addIndex(['model_name'], [
                'name' => 'idx2',
                'unique' => false,
            ])
        ->addIndex(['users_id'], [
                'name' => 'idx3',
                'unique' => false,
            ])
        ->addIndex(['type'], [
                'name' => 'idx4',
                'unique' => false,
            ])
        ->addIndex(['model_name', 'type'], [
                'name' => 'idx5',
                'unique' => false,
            ])
        ->addIndex(['entity_id', 'model_name', 'type'], [
                'name' => 'idx6',
                'unique' => false,
            ])
        ->create();

        $this->table('audits_details', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('audits_id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('field_name', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'audits_id',
            ])
            ->addColumn('old_value', 'text', [
                'null' => true,
                'default' => 'NULL',
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'field_name',
            ])
            ->addColumn('old_value_text', 'text', [
                'null' => true,
                'default' => 'NULL',
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_value',
            ])
            ->addColumn('new_value', 'text', [
                'null' => true,
                'default' => 'NULL',
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_value_text',
            ])
            ->addColumn('new_value_text', 'text', [
                'null' => true,
                'default' => 'NULL',
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'new_value',
            ])
        ->addIndex(['audits_id'], [
                'name' => 'idx1',
                'unique' => false,
            ])
        ->addIndex(['field_name'], [
                'name' => 'field_name',
                'unique' => false,
            ])
            ->create();
    }
}
