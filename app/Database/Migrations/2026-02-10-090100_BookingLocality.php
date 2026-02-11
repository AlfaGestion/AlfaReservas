<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BookingLocality extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bookings', [
            'locality' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'phone',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('bookings', 'locality');
    }
}
