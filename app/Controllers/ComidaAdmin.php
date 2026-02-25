<?php

namespace App\Controllers;

use Config\Database;

class ComidaAdmin extends BaseController
{
    public function login(string $codigo)
    {
        $cliente = $this->resolveClienteComida($codigo);
        return view('comida/admin_login', ['cliente' => $cliente]);
    }

    public function doLogin(string $codigo)
    {
        $cliente = $this->resolveClienteComida($codigo);
        $base = (string) $cliente['base'];
        $usuario = trim((string) $this->request->getVar('usuario'));
        $password = (string) $this->request->getVar('password');

        if ($usuario === '' || $password === '') {
            return redirect()->to('/pedidos/' . $codigo . '/admin/login')->with('msg', [
                'type' => 'danger',
                'body' => 'Debe ingresar usuario y contrasena.',
            ]);
        }

        $dbAlfa = Database::connect('alfareserva');
        $userTableExists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'user' LIMIT 1",
            [$base]
        )->getRowArray();

        $hasActiveUsers = false;
        if ($userTableExists) {
            $activeUsersRow = $dbAlfa->query(
                "SELECT COUNT(*) AS total FROM `{$base}`.`user` WHERE active = 1"
            )->getRowArray();
            $hasActiveUsers = (int) ($activeUsersRow['total'] ?? 0) > 0;
        }

        // Fallback: si no hay usuarios activos en la base, habilita credencial de emergencia.
        if (!$hasActiveUsers) {
            if ($usuario === 'testuser' && $password === 'Alfa2587') {
                session()->set([
                    'tenant_admin_logged' => true,
                    'tenant_admin_codigo' => $codigo,
                    'tenant_admin_base' => $base,
                    'tenant_admin_user' => 'testuser',
                    'tenant_admin_id' => 0,
                ]);

                return redirect()->to('/pedidos/' . $codigo . '/admin');
            }

            return redirect()->to('/pedidos/' . $codigo . '/admin/login')->with('msg', [
                'type' => 'danger',
                'body' => 'Sin usuarios configurados. Use testuser / Alfa2587.',
            ]);
        }

        $userRow = $dbAlfa->query(
            "SELECT id, user, email, password, active FROM `{$base}`.`user`
             WHERE user = ? OR email = ?
             LIMIT 1",
            [$usuario, $usuario]
        )->getRowArray();

        if (!$userRow || (int) ($userRow['active'] ?? 0) !== 1 || !password_verify($password, (string) ($userRow['password'] ?? ''))) {
            return redirect()->to('/pedidos/' . $codigo . '/admin/login')->with('msg', [
                'type' => 'danger',
                'body' => 'Credenciales invalidas para esta base.',
            ]);
        }

        session()->set([
            'tenant_admin_logged' => true,
            'tenant_admin_codigo' => $codigo,
            'tenant_admin_base' => $base,
            'tenant_admin_user' => $userRow['user'] ?? $userRow['email'] ?? 'admin',
            'tenant_admin_id' => (int) ($userRow['id'] ?? 0),
        ]);

        return redirect()->to('/pedidos/' . $codigo . '/admin');
    }

    public function logout(string $codigo)
    {
        session()->remove([
            'tenant_admin_logged',
            'tenant_admin_codigo',
            'tenant_admin_base',
            'tenant_admin_user',
            'tenant_admin_id',
        ]);

        return redirect()->to('/pedidos/' . $codigo . '/admin/login');
    }

    public function index(string $codigo)
    {
        $cliente = $this->resolveClienteComida($codigo);
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to('/pedidos/' . $codigo . '/admin/login');
        }
        $this->ensureCatalogoTable((string) $cliente['base']);

        $catalogo = $this->getCatalogo((string) $cliente['base']);

        return view('comida/admin', [
            'cliente' => $cliente,
            'catalogo' => $catalogo,
            'tenantAdminUser' => (string) (session()->get('tenant_admin_user') ?? ''),
        ]);
    }

    public function saveCatalogo(string $codigo)
    {
        $cliente = $this->resolveClienteComida($codigo);
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to('/pedidos/' . $codigo . '/admin/login');
        }
        $base = (string) $cliente['base'];
        $this->ensureCatalogoTable($base);

        $nombre = trim((string) $this->request->getVar('nombre'));
        $descripcion = trim((string) $this->request->getVar('descripcion'));
        $precio = (float) $this->request->getVar('precio');

        if ($nombre === '' || $precio < 0) {
            return redirect()->to('/pedidos/' . $codigo . '/admin')->with('msg', [
                'type' => 'danger',
                'body' => 'Debe completar nombre y precio valido.',
            ]);
        }

        $dbAlfa = Database::connect('alfareserva');
        $dbAlfa->query(
            "INSERT INTO `{$base}`.`catalogo` (`nombre`, `descripcion`, `precio`, `activo`) VALUES (?, ?, ?, 1)",
            [$nombre, $descripcion !== '' ? $descripcion : null, $precio]
        );

        return redirect()->to('/pedidos/' . $codigo . '/admin')->with('msg', [
            'type' => 'success',
            'body' => 'Item de catalogo creado.',
        ]);
    }

    private function isTenantAdminLogged(string $codigo): bool
    {
        return (bool) session()->get('tenant_admin_logged')
            && (string) session()->get('tenant_admin_codigo') === $codigo;
    }

    private function resolveClienteComida(string $codigo): array
    {
        if (!preg_match('/^[0-9]{9}$/', $codigo)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $dbAlfa = Database::connect('alfareserva');
        $cliente = $dbAlfa->table('clientes c')
            ->select('c.codigo, c.base, c.habilitado, c.razon_social, r.descripcion AS rubro')
            ->join('rubros r', 'r.id = c.id_rubro', 'left')
            ->where('c.codigo', $codigo)
            ->where('c.habilitado', 1)
            ->get()
            ->getRowArray();

        if (!$cliente) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rubro = strtolower(trim((string) ($cliente['rubro'] ?? '')));
        if (!in_array($rubro, ['comida', 'pedidos'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $cliente;
    }

    private function ensureCatalogoTable(string $databaseName): void
    {
        $dbAlfa = Database::connect('alfareserva');
        $exists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'catalogo' LIMIT 1",
            [$databaseName]
        )->getRowArray();

        if ($exists) {
            return;
        }

        $dbAlfa->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`catalogo` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre` VARCHAR(255) NOT NULL,
                `descripcion` TEXT NULL,
                `precio` DECIMAL(12,2) NOT NULL DEFAULT 0,
                `activo` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function getCatalogo(string $databaseName): array
    {
        $dbAlfa = Database::connect('alfareserva');
        return $dbAlfa->query(
            "SELECT id, nombre, descripcion, precio, activo FROM `{$databaseName}`.`catalogo` ORDER BY id DESC"
        )->getResultArray();
    }
}
