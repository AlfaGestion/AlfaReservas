<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BookingSlots extends Migration
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
            'date' => [
                'type' => 'DATE',
            ],
            'id_field' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'time_from' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'time_until' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'booking_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'active' => [
                'type'       => 'BIT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['date', 'id_field', 'time_from', 'time_until', 'active'], 'uniq_booking_slots_active');
        $this->forge->addKey('booking_id');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('booking_slots');
    }

    public function down()
    {
        $this->forge->dropTable('booking_slots');
    }
}
