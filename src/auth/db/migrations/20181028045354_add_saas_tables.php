<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddSaasTables extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('companies', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '					', 'row_format' => 'Compact']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'identity' => 'enable'])
            ->addColumn('name', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'id'])
            ->addColumn('profile_image', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'name'])
            ->addColumn('website', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_image'])
            ->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'website'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'users_id'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->addColumn('is_deleted', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'updated_at'])
            ->save();

        $table = $this->table('companies');
        if ($table->hasIndex('name')) {
            $table->removeIndexByName('name')->save();
        }
        $table = $this->table('companies');
        $table->addIndex(['name', 'users_id'], ['name' => 'name', 'unique' => true])->save();
        $table = $this->table('companies');
        if ($table->hasIndex('users_id')) {
            $table->removeIndexByName('users_id')->save();
        }

        $table = $this->table('companies');
        $table->addIndex(['users_id'], ['name' => 'users_id', 'unique' => false])->save();
        $table = $this->table('company_settings', ['id' => false, 'primary_key' => ['name'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('company_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'comment' => 'tabla donde se guardan las configuraciones en key value de los diferentes modelos

- general, zoho key, mandrill email settings
- modulo leads, agent default, rotation default , etc'])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'company_id'])
            ->addColumn('value', 'text', ['null' => false, 'limit' => MysqlAdapter::TEXT_LONG, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'name'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'value'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->addColumn('is_deleted', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'updated_at'])
            ->save();

        $table = $this->table('company_settings');
        if ($table->hasIndex('index4')) {
            $table->removeIndexByName('index4')->save();
        }
        $table = $this->table('company_settings');
        $table->addIndex(['name'], ['name' => 'index4', 'unique' => false])->save();
        $table = $this->table('company_settings');
        if ($table->hasIndex('index5')) {
            $table->removeIndexByName('index5')->save();
        }
        $table = $this->table('company_settings');
        $table->addIndex(['company_id', 'name'], ['name' => 'index5', 'unique' => false])->save();

        $table = $this->table('suscriptions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'identity' => 'enable'])
            ->addColumn('plans_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'id'])
            ->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'plans_id'])
            ->addColumn('apps_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'users_id'])
            ->addColumn('stripe_id', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'apps_id'])
            ->addColumn('company_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'stripe_id'])
            ->addColumn('stripe_plan', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'company_id'])
            ->addColumn('quantity', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'stripe_plan'])
            ->addColumn('trial_ends_at', 'datetime', ['null' => true, 'after' => 'quantity'])
            ->addColumn('ends_at', 'datetime', ['null' => true, 'after' => 'trial_ends_at'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'ends_at'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->addColumn('is_deleted', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'updated_at'])
            ->save();

        $table = $this->table('suscriptions');
        
        if ($table->hasIndex('company_id')) {
            $table->removeIndexByName('company_id')->save();
        }

        $table = $this->table('suscriptions');
        $table->addIndex(['company_id'], ['name' => 'company_id', 'unique' => true])->save();
        $table = $this->table('users_associated_company', ['id' => false, 'primary_key' => ['users_id', 'company_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10])
            ->addColumn('company_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'users_id'])
            ->addColumn('identify_id', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'company_id'])
            ->addColumn('user_active', 'boolean', ['null' => false, 'default' => '1', 'limit' => MysqlAdapter::INT_TINY, 'precision' => 3, 'after' => 'identify_id'])
            ->addColumn('user_role', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'user_active'])
            ->save();

        $table = $this->table('users_associated_company');
        if ($table->hasIndex('users_id')) {
            $table->removeIndexByName('users_id')->save();
        }
        $table = $this->table('users_associated_company');
        $table->addIndex(['users_id', 'company_id'], ['name' => 'users_id', 'unique' => true])->save();
    }
}
