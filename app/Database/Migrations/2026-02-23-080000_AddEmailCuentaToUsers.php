<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailCuentaToUsers extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users') && !$this->db->fieldExists('email', 'users')) {
            $this->forge->addColumn('users', [
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'user',
                ],
            ]);
        }

        if ($this->db->tableExists('users') && !$this->db->fieldExists('cuenta', 'users')) {
            $this->forge->addColumn('users', [
                'cuenta' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'email',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('cuenta', 'users')) {
            $this->forge->dropColumn('users', 'cuenta');
        }

        if ($this->db->tableExists('users') && $this->db->fieldExists('email', 'users')) {
            $this->forge->dropColumn('users', 'email');
        }
    }
}
