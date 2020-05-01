<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class SessionIsAdminToNull extends AbstractMigration
{
    public function change()
    {
        $this->table("sessions")->changeColumn('is_admin', 'enum', ['null' => true, 'default' => "0", 'limit' => 1, 'values' => ['0','1'], 'after' => 'logged_in'])->update();
    }
}
