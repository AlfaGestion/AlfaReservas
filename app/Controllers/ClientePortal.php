<?php

namespace App\Controllers;

use Config\Database;

class ClientePortal extends BaseController
{
    public function index(string $codigo)
    {
        if (!preg_match('/^[0-9]{9}$/', $codigo)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $dbAlfa = Database::connect('alfareserva');
        $cliente = $dbAlfa->table('clientes c')
            ->select('c.codigo, c.base, c.razon_social, c.habilitado, c.link, r.descripcion AS rubro')
            ->join('rubros r', 'r.id = c.id_rubro', 'left')
            ->where('c.codigo', $codigo)
            ->where('c.habilitado', 1)
            ->get()
            ->getRowArray();

        if (!$cliente) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $databaseName = (string) ($cliente['base'] ?? '');
        if ($databaseName === '') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $stats = $this->getTenantStats($databaseName);
        $branding = $this->getBranding($codigo);

        return view('cliente_portal/index', [
            'cliente' => $cliente,
            'stats' => $stats,
            'branding' => $branding,
        ]);
    }

    private function getTenantStats(string $databaseName): array
    {
        $dbAlfa = Database::connect('alfareserva');
        $exists = $dbAlfa->query(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1',
            [$databaseName]
        )->getRowArray();

        if (!$exists) {
            return [
                'database' => $databaseName,
                'tables' => [],
                'error' => 'La base del cliente no existe.',
            ];
        }

        $tables = ['user', 'clientes', 'reservas', 'catalogo'];
        $result = [];
        foreach ($tables as $table) {
            $tableExists = $dbAlfa->query(
                "SELECT TABLE_NAME FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1",
                [$databaseName, $table]
            )->getRowArray();

            if (!$tableExists) {
                $result[$table] = ['exists' => false, 'count' => 0];
                continue;
            }

            $countRow = $dbAlfa->query(
                "SELECT COUNT(*) AS total FROM `{$databaseName}`.`{$table}`"
            )->getRowArray();

            $result[$table] = [
                'exists' => true,
                'count' => (int) ($countRow['total'] ?? 0),
            ];
        }

        return [
            'database' => $databaseName,
            'tables' => $result,
            'error' => null,
        ];
    }

    private function getBranding(string $codigo): array
    {
        $tenantDir = FCPATH . 'assets/tenants/' . $codigo . '/';
        $logoCandidates = ['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp'];
        $backgroundCandidates = ['fondo.jpg', 'fondo.png', 'fondo.webp', 'background.jpg', 'background.png', 'background.webp'];

        $logoUrl = base_url('alfa.png');
        foreach ($logoCandidates as $file) {
            if (is_file($tenantDir . $file)) {
                $logoUrl = base_url('assets/tenants/' . $codigo . '/' . $file);
                break;
            }
        }

        $backgroundUrl = null;
        foreach ($backgroundCandidates as $file) {
            if (is_file($tenantDir . $file)) {
                $backgroundUrl = base_url('assets/tenants/' . $codigo . '/' . $file);
                break;
            }
        }

        return [
            'logo' => $logoUrl,
            'background' => $backgroundUrl,
            'tenantDir' => 'public/assets/tenants/' . $codigo . '/',
        ];
    }
}

