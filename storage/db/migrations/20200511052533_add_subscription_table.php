<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddSubscriptionTable extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_520_ci';");
        $this->table('subscriptions', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'identity' => 'enable',
            ])
            ->addColumn('plans_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'plans_id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'user_id',
            ])
            ->addColumn('stripe_id', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'apps_id',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'stripe_id',
            ])
            ->addColumn('stripe_plan', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'companies_id',
            ])
            ->addColumn('quantity', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'stripe_plan',
            ])
            ->addColumn('trial_ends_at', 'datetime', [
                'null' => true,
                'after' => 'quantity',
            ])
            ->addColumn('ends_at', 'datetime', [
                'null' => true,
                'after' => 'trial_ends_at',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'ends_at',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->addIndex(['companies_id'], [
                'name' => 'company_id',
                'unique' => true,
            ])
            ->create();
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'banned',
            ])
            ->addColumn('stripe_id', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'system_modules_id',
            ])
            ->addColumn('card_last_four', 'string', [
                'null' => true,
                'limit' => 4,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'stripe_id',
            ])
            ->addColumn('card_brand', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'card_last_four',
            ])
            ->addColumn('trial_ends_at', 'timestamp', [
                'null' => true,
                'after' => 'card_brand',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'trial_ends_at',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->save();

        $this->table('subscription')->drop()->save();
    }
}
