<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

abstract class TenantModel extends Model
{
    protected function shouldUseTenantConnection(): bool
    {
        if (is_cli()) {
            return false;
        }

        try {
            $session = session();
        } catch (\Throwable $e) {
            return false;
        }

        if (!$session) {
            return false;
        }

        if ((int) ($session->get('logueado') ?? 0) === 1) {
            return false;
        }

        if ((int) ($session->get('tenant_active') ?? 0) !== 1) {
            return false;
        }

        $tenantBase = trim((string) ($session->get('tenant_base') ?? ''));
        if ($tenantBase === '') {
            return false;
        }

        return preg_match('/^[A-Za-z0-9_-]+$/', $tenantBase) === 1;
    }

    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        if (!$this->shouldUseTenantConnection()) {
            return;
        }

        $tenantBase = (string) session()->get('tenant_base');
        $databaseConfig = config(\Config\Database::class);
        $connectionConfig = $databaseConfig->default;
        $connectionConfig['database'] = $tenantBase;

        $tenantDb = Database::connect($connectionConfig);
        if (!$tenantDb->tableExists($this->table)) {
            return;
        }

        $this->db = $tenantDb;
    }
}
