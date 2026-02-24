<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClienteFieldsToCustomers extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('customers')) {
            return;
        }

        $fields = [];

        if (!$this->db->fieldExists('email', 'customers')) {
            $fields['email'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('password', 'customers')) {
            $fields['password'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('id_rubro', 'customers')) {
            $fields['id_rubro'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('razon_social', 'customers')) {
            $fields['razon_social'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('customers', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('customers')) {
            return;
        }

        foreach (['email', 'password', 'id_rubro', 'razon_social'] as $field) {
            if ($this->db->fieldExists($field, 'customers')) {
                $this->forge->dropColumn('customers', $field);
            }
        }
    }
}
