<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateSubscriptions extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('sources', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'language_id',
            ])
            ->addColumn('update_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'update_at',
            ])
            ->removeColumn('added_date')
            ->removeColumn('updated_date')
            ->save();
        $this->table('user_config', [
                'id' => false,
                'primary_key' => ['users_id', 'name'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('update_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'update_at',
            ])
            ->removeColumn('updated_at')
            ->save();
        $this->table('audits', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('entity_id', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->save();
        $this->table('subscriptions', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('user_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'id',
            ])
            ->changeColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'user_id',
            ])
            ->changeColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'companies_id',
            ])
            ->addColumn('apps_plans_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'apps_id',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_plans_id',
            ])
            ->changeColumn('stripe_id', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->changeColumn('stripe_plan', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stripe_id',
            ])
            ->changeColumn('quantity', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'stripe_plan',
            ])
            ->changeColumn('trial_ends_at', 'timestamp', [
                'null' => true,
                'default' => null,
                'after' => 'quantity',
            ])
            ->addColumn('grace_period_ends', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'trial_ends_at',
            ])
            ->addColumn('next_due_payment', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'grace_period_ends',
            ])
            ->changeColumn('ends_at', 'timestamp', [
                'null' => true,
                'default' => null,
                'after' => 'next_due_payment',
            ])
            ->addColumn('payment_frequency_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'ends_at',
            ])
            ->addColumn('trial_ends_days', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'payment_frequency_id',
            ])
            ->addColumn('is_freetrial', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'trial_ends_days',
            ])
            ->addColumn('is_active', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'is_freetrial',
            ])
            ->addColumn('is_cancelled', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'is_active',
            ])
            ->addColumn('paid', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '3',
                'after' => 'is_cancelled',
            ])
            ->addColumn('charge_date', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'paid',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'charge_date',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
            ])
            ->removeColumn('plans_id')
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['apps_plans_id'], [
                'name' => 'apps_plans_id',
                'unique' => false,
            ])
            ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
                'unique' => false,
                'limit' => '191',
            ])
            ->addIndex(['stripe_plan'], [
                'name' => 'stripe_plan',
                'unique' => false,
                'limit' => '191',
            ])
            ->addIndex(['trial_ends_at'], [
                'name' => 'trial_ends_at',
                'unique' => false,
            ])
            ->addIndex(['is_freetrial'], [
                'name' => 'is_freetrial',
                'unique' => false,
            ])
            ->addIndex(['is_active'], [
                'name' => 'is_active',
                'unique' => false,
            ])
            ->addIndex(['paid'], [
                'name' => 'paid',
                'unique' => false,
            ])
            ->addIndex(['charge_date'], [
                'name' => 'charge_date',
                'unique' => false,
            ])
            ->addIndex(['ends_at'], [
                'name' => 'ends_at',
                'unique' => false,
            ])
            ->removeIndexByName("company_id")
            ->save();
    }
}
