<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RubrosAndClientesRelation extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('rubros')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'descripcion' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('descripcion');
            $this->forge->createTable('rubros');
        }

        if (!$this->db->tableExists('clientes')) {
            return;
        }

        if (!$this->db->fieldExists('id_rubro', 'clientes')) {
            $this->forge->addColumn('clientes', [
                'id_rubro' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'base',
                ],
            ]);
            $this->db->query('ALTER TABLE clientes ADD INDEX idx_clientes_id_rubro (id_rubro)');
        }

        // Migra datos legacy desde clientes.rubro a la nueva tabla rubros.
        if ($this->db->fieldExists('rubro', 'clientes')) {
            $legacyRubros = $this->db->query(
                "SELECT DISTINCT TRIM(rubro) AS descripcion FROM clientes WHERE rubro IS NOT NULL AND TRIM(rubro) <> ''"
            )->getResultArray();

            foreach ($legacyRubros as $row) {
                $descripcion = (string) ($row['descripcion'] ?? '');
                if ($descripcion === '') {
                    continue;
                }

                $exists = $this->db->table('rubros')->where('descripcion', $descripcion)->get()->getRowArray();
                if (!$exists) {
                    $this->db->table('rubros')->insert(['descripcion' => $descripcion]);
                }
            }

            $this->db->query(
                "UPDATE clientes c
                 JOIN rubros r ON r.descripcion = c.rubro
                 SET c.id_rubro = r.id
                 WHERE c.rubro IS NOT NULL AND TRIM(c.rubro) <> ''"
            );

            $this->forge->dropColumn('clientes', 'rubro');
        }

        $fkExists = $this->db->query(
            "SELECT 1
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'clientes'
               AND COLUMN_NAME = 'id_rubro'
               AND REFERENCED_TABLE_NAME = 'rubros'
             LIMIT 1"
        )->getRowArray();

        if (!$fkExists) {
            $this->db->query(
                'ALTER TABLE clientes ADD CONSTRAINT fk_clientes_rubros FOREIGN KEY (id_rubro) REFERENCES rubros(id)'
            );
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('clientes')) {
            if ($this->db->tableExists('rubros')) {
                $this->forge->dropTable('rubros', true);
            }
            return;
        }

        if (!$this->db->fieldExists('rubro', 'clientes')) {
            $this->forge->addColumn('clientes', [
                'rubro' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'after'      => 'base',
                ],
            ]);
        }

        if ($this->db->tableExists('rubros') && $this->db->fieldExists('id_rubro', 'clientes')) {
            $this->db->query(
                "UPDATE clientes c
                 LEFT JOIN rubros r ON r.id = c.id_rubro
                 SET c.rubro = r.descripcion"
            );

            $fkExists = $this->db->query(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'clientes'
                   AND COLUMN_NAME = 'id_rubro'
                   AND REFERENCED_TABLE_NAME = 'rubros'
                 LIMIT 1"
            )->getRowArray();

            if ($fkExists && !empty($fkExists['CONSTRAINT_NAME'])) {
                $this->db->query('ALTER TABLE clientes DROP FOREIGN KEY ' . $fkExists['CONSTRAINT_NAME']);
            }

            $this->db->query('ALTER TABLE clientes DROP INDEX idx_clientes_id_rubro');
            $this->forge->dropColumn('clientes', 'id_rubro');
        }

        if ($this->db->tableExists('rubros')) {
            $this->forge->dropTable('rubros', true);
        }
    }
}

