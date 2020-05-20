<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UsersUpdatedAtFix extends AbstractMigration
{
    public function change()
    {
        $this->table("users")->changeColumn('karma', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'language'])->update();
        $this->table("users")->changeColumn('votes', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'signed' => false, 'after' => 'karma'])->update();
        $this->table("users")->changeColumn('votes_points', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'votes'])->update();
        $this->table("users")->changeColumn('banned', 'char', ['null' => false, 'default' => "N", 'limit' => 1, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'votes_points'])->update();
        $this->table("users")->changeColumn('created_at', 'datetime', ['null' => true, 'after' => 'banned'])->update();
        $table = $this->table("users");
        $table->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])->save();
        $table->save();
        if($this->table('users')->hasColumn('modified_at')) {
            $this->table("users")->removeColumn('modified_at')->update();
        }
        if($this->table('users')->hasColumn('update_at')) {
            $this->table("users")->removeColumn('update_at')->update();
        }
    }
}
