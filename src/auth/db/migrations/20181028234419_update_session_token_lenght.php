<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateSessionTokenLenght extends AbstractMigration
{
    public function change()
    {
        $this->table("sessions")->changeColumn('token', 'text', ['null' => false, 'limit' => 65535, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'users_id'])->update();
    }
}
