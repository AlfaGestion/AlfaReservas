<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Configuracion extends Migration
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
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'valor' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('clave');
        $this->forge->createTable('ta_configuracion');

        $defaultText = "Aviso importante\n\n"
            . "Queremos informarles que el día <fecha> las canchas permanecerán cerradas.\n"
            . "Pedimos disculpas por las molestias que esto pueda ocasionar.\n\n"
            . "De todas formas, ya pueden reservar normalmente las horas para fechas posteriores.\n"
            . "Muchas gracias por la comprensión y por seguir eligiéndonos.";

        $this->db->table('ta_configuracion')->insert([
            'clave' => 'texto_cierre',
            'valor' => $defaultText,
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('ta_configuracion');
    }
}
