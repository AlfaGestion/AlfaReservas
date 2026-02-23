<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHabilitadoLinkToClientes extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('clientes')) {
            return;
        }

        if (!$this->db->fieldExists('habilitado', 'clientes')) {
            $this->forge->addColumn('clientes', [
                'habilitado' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'after'      => 'email',
                ],
            ]);
        }

        if (!$this->db->fieldExists('link', 'clientes')) {
            $this->forge->addColumn('clientes', [
                'link' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'habilitado',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('clientes')) {
            return;
        }

        if ($this->db->fieldExists('link', 'clientes')) {
            $this->forge->dropColumn('clientes', 'link');
        }

        if ($this->db->fieldExists('habilitado', 'clientes')) {
            $this->forge->dropColumn('clientes', 'habilitado');
        }
    }
}

