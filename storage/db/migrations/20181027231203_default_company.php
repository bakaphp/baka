<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class DefaultCompany extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('default_company', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'timezone'])->save();
        $this->table('users')->changeColumn('city_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_MEDIUM, 'precision' => 7, 'signed' => false, 'after' => 'default_company'])
            ->changeColumn('state_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'city_id'])
            ->changeColumn('country_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_SMALL, 'precision' => 5, 'signed' => false, 'after' => 'state_id'])
            ->changeColumn('profile_privacy', 'enum', ['null' => false, 'default' => '0', 'limit' => 1, 'values' => ['0', '1'], 'after' => 'country_id'])
            ->changeColumn('interests', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_MEDIUM, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_privacy'])
            ->changeColumn('profile_image', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'interests'])
            ->changeColumn('profile_remote_image', 'string', ['null' => true, 'limit' => 255, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_image'])
            ->changeColumn('profile_header', 'string', ['null' => true, 'limit' => 192, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'profile_remote_image'])
            ->changeColumn('profile_header_mobile', 'string', ['null' => true, 'limit' => 192, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_header'])
            ->changeColumn('user_active', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'profile_header_mobile'])
            ->changeColumn('user_level', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'user_active'])
            ->changeColumn('user_login_tries', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'user_level'])
            ->changeColumn('user_last_login_try', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'user_login_tries'])
            ->changeColumn('session_time', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'user_last_login_try'])
            ->changeColumn('session_page', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'session_time'])
            ->changeColumn('welcome', 'integer', ['null' => false, 'default' => '0', 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'session_page'])
            ->changeColumn('user_activation_key', 'string', ['null' => true, 'limit' => 64, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'welcome'])
            ->changeColumn('user_activation_email', 'string', ['null' => true, 'limit' => 64, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'user_activation_key'])
            ->changeColumn('user_activation_forgot', 'string', ['null' => true, 'limit' => 100, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'user_activation_email'])
            ->changeColumn('language', 'string', ['null' => true, 'limit' => 5, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'user_activation_forgot'])
            ->changeColumn('modified_at', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'language'])
            ->changeColumn('karma', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'modified_at'])
            ->changeColumn('votes', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'karma'])
            ->changeColumn('votes_points', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'votes'])
            ->changeColumn('banned', 'char', ['null' => false, 'default' => 'N', 'limit' => 1, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'votes_points'])
            ->changeColumn('created_at', 'datetime', ['null' => true, 'after' => 'banned'])
            ->changeColumn('update_at', 'datetime', ['null' => true, 'after' => 'created_at']);
        $table->save();
    }
}
