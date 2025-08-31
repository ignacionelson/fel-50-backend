<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserFieldsFirstNameLastNamePhoneUuid extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('first_name', 'string', ['limit' => 100, 'null' => false, 'after' => 'name'])
              ->addColumn('last_name', 'string', ['limit' => 100, 'null' => false, 'after' => 'first_name'])
              ->addColumn('phone_number', 'string', ['limit' => 20, 'null' => true, 'after' => 'last_name'])
              ->addColumn('uuid', 'char', ['limit' => 36, 'null' => false, 'after' => 'id'])
              ->addIndex(['uuid'], ['unique' => true])
              ->update();
    }
}
