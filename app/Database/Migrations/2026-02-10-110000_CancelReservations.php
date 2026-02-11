<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CancelReservations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cancel_date' => [
                'type' => 'DATE',
            ],
            'field_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'field_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('cancel_date');
        $this->forge->createTable('cancel_reservations');
    }

    public function down()
    {
        $this->forge->dropTable('cancel_reservations');
    }
}
