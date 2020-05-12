<?php

use Phinx\Migration\AbstractMigration;

class UpdateConfigDateFields extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('user_config');
        $table->addColumn('created_at', 'datetime', ['null' => false, 'after' => 'value'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'after' => 'created_at'])
            ->save();

        if ($this->table('user_config')->hasColumn('added_date')) {
            $this->table('user_config')->removeColumn('added_date')->update();
        }
        if ($this->table('user_config')->hasColumn('updated_date')) {
            $this->table('user_config')->removeColumn('updated_date')->update();
        }
    }
}
