<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Auth extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8';");
        $this->execute("ALTER DATABASE COLLATE='utf8_general_mysql500_ci';");
        $table = $this->table('session_keys', ['id' => false, 'primary_key' => ['sessions_id', 'users_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('sessions_id', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8'])
            ->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'signed' => false, 'after' => 'sessions_id'])
            ->addColumn('last_ip', 'string', ['null' => true, 'limit' => 39, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'users_id'])
            ->addColumn('last_login', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'last_ip'])
            ->save();

        $table = $this->table('session_keys');
        if ($table->hasIndex('last_login')) {
            $table->removeIndexByName('last_login')->save();
        }

        $table = $this->table('session_keys');
        $table->addIndex(['last_login'], ['name' => 'last_login', 'unique' => false])->save();
        $table = $this->table('session_keys');

        if ($table->hasIndex('user_id')) {
            $table->removeIndexByName('user_id')->save();
        }

        $table = $this->table('session_keys');
        $table->addIndex(['users_id'], ['name' => 'user_id', 'unique' => false])->save();
        $table = $this->table('session_keys');
        if ($table->hasIndex('session_id')) {
            $table->removeIndexByName('session_id')->save();
        }

        $table = $this->table('session_keys');
        $table->addIndex(['sessions_id'], ['name' => 'session_id', 'unique' => false])->save();
        $table = $this->table('sessions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('id', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8'])
            ->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'signed' => false, 'after' => 'id'])
            ->addColumn('token', 'string', ['null' => false, 'limit' => 255, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'users_id'])
            ->addColumn('start', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'token'])
            ->addColumn('time', 'integer', ['null' => false, 'default' => '0', 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'start'])
            ->addColumn('ip', 'string', ['null' => false, 'limit' => 39, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'time'])
            ->addColumn('page', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'ip'])
            ->addColumn('logged_in', 'enum', ['null' => false, 'default' => '0', 'limit' => 1, 'values' => ['0', '1'], 'after' => 'page'])
            ->addColumn('is_admin', 'enum', ['null' => false, 'default' => '0', 'limit' => 1, 'values' => ['0', '1'], 'after' => 'logged_in'])
            ->save();

        $table = $this->table('sessions');
        if ($table->hasIndex('user_id')) {
            $table->removeIndexByName('user_id')->save();
        }
        $table = $this->table('sessions');
        $table->addIndex(['users_id'], ['name' => 'user_id', 'unique' => false])->save();
        $table = $this->table('sessions');
        if ($table->hasIndex('time')) {
            $table->removeIndexByName('time')->save();
        }
        $table = $this->table('sessions');
        $table->addIndex(['time'], ['name' => 'time', 'unique' => false])->save();
        $table = $this->table('sessions');
        if ($table->hasIndex('logged_in')) {
            $table->removeIndexByName('logged_in')->save();
        }
        $table = $this->table('sessions');
        $table->addIndex(['logged_in'], ['name' => 'logged_in', 'unique' => false])->save();
        $table = $this->table('sessions');
        if ($table->hasIndex('start')) {
            $table->removeIndexByName('start')->save();
        }
        $table = $this->table('sessions');
        $table->addIndex(['start'], ['name' => 'start', 'unique' => false])->save();
        $table = $this->table('sources', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_SMALL, 'precision' => 5, 'signed' => false, 'identity' => 'enable'])
            ->addColumn('title', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'id'])
            ->addColumn('url', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'title'])
            ->addColumn('language_id', 'string', ['null' => true, 'limit' => 5, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'url'])
            ->addColumn('added_date', 'datetime', ['null' => false, 'after' => 'language_id'])
            ->addColumn('updated_date', 'datetime', ['null' => true, 'after' => 'added_date'])
            ->save();

        $table = $this->table('sources');
        if ($table->hasIndex('unq1')) {
            $table->removeIndexByName('unq1')->save();
        }
        $table = $this->table('sources');
        $table->addIndex(['url'], ['name' => 'unq1', 'unique' => true])->save();

        $table = $this->table('user_config', ['id' => false, 'primary_key' => ['users_id', 'name'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'signed' => false])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'users_id'])
            ->addColumn('value', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'name'])
            ->addColumn('added_date', 'datetime', ['null' => false, 'after' => 'value'])
            ->addColumn('updated_date', 'datetime', ['null' => true, 'after' => 'added_date'])
            ->save();

        $table = $this->table('user_linked_sources', ['id' => false, 'primary_key' => ['users_id', 'source_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('users_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'signed' => false])
            ->addColumn('source_id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_SMALL, 'precision' => 5, 'signed' => false, 'after' => 'users_id'])
            ->addColumn('source_users_id', 'string', ['null' => false, 'limit' => 30, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'source_id'])
            ->addColumn('source_users_id_text', 'string', ['null' => true, 'limit' => 255, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'source_users_id'])
            ->addColumn('source_username', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'source_users_id_text'])
            ->save();

        $table = $this->table('user_linked_sources');
        if ($table->hasIndex('user_id')) {
            $table->removeIndexByName('user_id')->save();
        }
        $table = $this->table('user_linked_sources');
        $table->addIndex(['users_id'], ['name' => 'user_id', 'unique' => false])->save();
        $table = $this->table('user_linked_sources');
        if ($table->hasIndex('source_user_id')) {
            $table->removeIndexByName('source_user_id')->save();
        }
        $table = $this->table('user_linked_sources');
        $table->addIndex(['source_users_id'], ['name' => 'source_user_id', 'unique' => false])->save();
        $table = $this->table('user_linked_sources');
        if ($table->hasIndex('source_user_id_text')) {
            $table->removeIndexByName('source_user_id_text')->save();
        }
        $table = $this->table('user_linked_sources');
        $table->addIndex(['source_users_id_text'], ['name' => 'source_user_id_text', 'unique' => false])->save();
        $table = $this->table('user_linked_sources');
        if ($table->hasIndex('source_username')) {
            $table->removeIndexByName('source_username')->save();
        }
        $table = $this->table('user_linked_sources');
        $table->addIndex(['source_username'], ['name' => 'source_username', 'unique' => false])->save();
        $table = $this->table('user_linked_sources');
        if ($table->hasIndex('user_id_2')) {
            $table->removeIndexByName('user_id_2')->save();
        }

        $table = $this->table('user_linked_sources');
        $table->addIndex(['users_id', 'source_users_id_text'], ['name' => 'user_id_2', 'unique' => false])->save();
        $table = $this->table('users', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_general_ci', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'signed' => false, 'identity' => 'enable'])
            ->addColumn('email', 'string', ['null' => false, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'id'])
            ->addColumn('password', 'string', ['null' => false, 'limit' => 255, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'email'])
            ->addColumn('firstname', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'password'])
            ->addColumn('lastname', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'firstname'])
            ->addColumn('user_role', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'lastname'])
            ->addColumn('displayname', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'user_role'])
            ->addColumn('registered', 'datetime', ['null' => false, 'after' => 'displayname'])
            ->addColumn('lastvisit', 'datetime', ['null' => false, 'after' => 'registered'])
            ->addColumn('dob', 'date', ['null' => false, 'after' => 'lastvisit'])
            ->addColumn('sex', 'enum', ['null' => false, 'default' => 'U', 'limit' => 1, 'values' => ['U', 'M', 'F'], 'after' => 'dob'])
            ->addColumn('timezone', 'string', ['null' => false, 'default' => 'America/New_York', 'limit' => 128, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'sex'])
            ->addColumn('city_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_MEDIUM, 'precision' => 7, 'signed' => false, 'after' => 'timezone'])
            ->addColumn('state_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'city_id'])
            ->addColumn('country_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_SMALL, 'precision' => 5, 'signed' => false, 'after' => 'state_id'])
            ->addColumn('profile_privacy', 'enum', ['null' => false, 'default' => '0', 'limit' => 1, 'values' => ['0', '1'], 'after' => 'country_id'])
            ->addColumn('interests', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_MEDIUM, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_privacy'])
            ->addColumn('profile_image', 'string', ['null' => true, 'limit' => 45, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'interests'])
            ->addColumn('profile_remote_image', 'string', ['null' => true, 'limit' => 255, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_image'])
            ->addColumn('profile_header', 'string', ['null' => true, 'limit' => 192, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'profile_remote_image'])
            ->addColumn('profile_header_mobile', 'string', ['null' => true, 'limit' => 192, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'profile_header'])
            ->addColumn('user_active', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'profile_header_mobile'])
            ->addColumn('user_level', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'user_active'])
            ->addColumn('user_login_tries', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'user_level'])
            ->addColumn('user_last_login_try', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'user_login_tries'])
            ->addColumn('session_time', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'user_last_login_try'])
            ->addColumn('session_page', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'session_time'])
            ->addColumn('welcome', 'integer', ['null' => false, 'default' => '0', 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'session_page'])
            ->addColumn('user_activation_key', 'string', ['null' => true, 'limit' => 64, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'welcome'])
            ->addColumn('user_activation_email', 'string', ['null' => true, 'limit' => 64, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'user_activation_key'])
            ->addColumn('user_activation_forgot', 'string', ['null' => true, 'limit' => 100, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'user_activation_email'])
            ->addColumn('language', 'string', ['null' => true, 'limit' => 5, 'collation' => 'utf8_bin', 'encoding' => 'utf8', 'after' => 'user_activation_forgot'])
            ->addColumn('modified_at', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'language'])
            ->addColumn('karma', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'modified_at'])
            ->addColumn('votes', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'karma'])
            ->addColumn('votes_points', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'votes'])
            ->addColumn('banned', 'char', ['null' => false, 'default' => 'N', 'limit' => 1, 'collation' => 'utf8_general_ci', 'encoding' => 'utf8', 'after' => 'votes_points'])
            ->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'banned'])
            ->addColumn('update_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->save();

        $table = $this->table('users');
        if ($table->hasIndex('unq1')) {
            $table->removeIndexByName('unq1')->save();
        }
        $table = $this->table('users');
        $table->addIndex(['email'], ['name' => 'unq1', 'unique' => true])->save();
        $table = $this->table('users');
        if ($table->hasIndex('unq2')) {
            $table->removeIndexByName('unq2')->save();
        }
        $table = $this->table('users');
        $table->addIndex(['displayname'], ['name' => 'unq2', 'unique' => true])->save();
        $table = $this->table('users');
        if ($table->hasIndex('idx1')) {
            $table->removeIndexByName('idx1')->save();
        }
        $table = $this->table('users');
        $table->addIndex(['city_id'], ['name' => 'idx1', 'unique' => false])->save();
        $table = $this->table('users');
        if ($table->hasIndex('idx2')) {
            $table->removeIndexByName('idx2')->save();
        }
        $table = $this->table('users');
        $table->addIndex(['state_id'], ['name' => 'idx2', 'unique' => false])->save();
        $table = $this->table('users');
        if ($table->hasIndex('idx3')) {
            $table->removeIndexByName('idx3')->save();
        }
        $table = $this->table('users');
        $table->addIndex(['country_id'], ['name' => 'idx3', 'unique' => false])->save();
        $table = $this->table('banlist', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8', 'collation' => 'utf8_bin', 'comment' => '', 'row_format' => 'Compact']);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_MEDIUM, 'precision' => 7, 'signed' => false, 'identity' => 'enable'])
            ->addColumn('users_id', 'integer', ['null' => false, 'default' => '0', 'limit' => MysqlAdapter::INT_BIG, 'precision' => 19, 'after' => 'id'])
            ->addColumn('ip', 'string', ['null' => false, 'default' => '', 'limit' => 35, 'collation' => 'latin1_swedish_ci', 'encoding' => 'latin1', 'after' => 'users_id'])
            ->addColumn('email', 'string', ['null' => true, 'limit' => 255, 'collation' => 'latin1_swedish_ci', 'encoding' => 'latin1', 'after' => 'ip'])
            ->save();

        $table = $this->table('banlist');
        if ($table->hasIndex('ban_ip_user_id')) {
            $table->removeIndexByName('ban_ip_user_id')->save();
        }
        $table = $this->table('banlist');
        $table->addIndex(['ip', 'users_id'], ['name' => 'ban_ip_user_id', 'unique' => false])->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
