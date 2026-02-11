<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LocalitiesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'El Bolsón'],
            ['name' => 'Loma del Medio'],
            ['name' => 'Villa Turismo'],
            ['name' => 'Barrio Esperanza'],
            ['name' => 'Barrio Almafuerte'],
            ['name' => 'Barrio Arrayanes'],
            ['name' => 'Mallín Ahogado'],
            ['name' => 'Río Azul'],
            ['name' => 'Los Repollos'],
        ];

        $this->db->table('localities')->insertBatch($data);
    }
}
