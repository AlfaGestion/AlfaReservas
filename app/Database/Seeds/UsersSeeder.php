<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user' => 'marcos',
                'email' => 'marcoslromero23@gmail.com',
                'cuenta' => 'marcos-admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'superadmin' => 1,
                'name' => 'Marcos',
                'active' => 1,
            ],
        ];

        $this->db->table('user')->insertBatch($data);
    }
}
