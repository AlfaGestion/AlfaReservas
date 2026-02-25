<?php

namespace App\Controllers;

use Config\Database;

class Comida extends BaseController
{
    private function renderTenantBlocked(array $cliente)
    {
        return response()
            ->setStatusCode(403)
            ->setBody(view('tenant_access_blocked', [
                'title' => 'Acceso no disponible',
                'message' => (string) ($cliente['tenant_access_message'] ?? 'No podes ingresar en este momento.'),
                'cliente' => $cliente,
            ]));
    }

    public function index(string $codigo)
    {
        $cliente = $this->resolveClienteComida($codigo);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }

        \Config\Services::tenant()->activate($cliente);

        $branding = $this->getBranding($codigo);
        $catalogo = $this->getCatalogo((string) $cliente['base']);

        return view('comida/index', [
            'cliente' => $cliente,
            'branding' => $branding,
            'catalogo' => $catalogo,
            'tenantNotice' => $cliente['tenant_access_notice'] ?? null,
            'tenantMode' => $cliente['tenant_access_mode'] ?? 'full',
        ]);
    }

    public function reservar(string $codigo)
    {
        $cliente = $this->resolveClienteComida($codigo);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to('/pedidos/' . $codigo)->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }

        $dbAlfa = Database::connect('alfareserva');
        $base = (string) $cliente['base'];

        $nombre = trim((string) $this->request->getVar('nombre'));
        $telefono = trim((string) $this->request->getVar('telefono'));
        $catalogoId = (int) $this->request->getVar('catalogo_id');
        $cantidad = (int) $this->request->getVar('cantidad');
        $observaciones = trim((string) $this->request->getVar('observaciones'));

        if ($nombre === '' || $catalogoId <= 0 || $cantidad <= 0) {
            return redirect()->to('/pedidos/' . $codigo)->with('msg', [
                'type' => 'danger',
                'body' => 'Debe completar nombre, item del catalogo y cantidad.',
            ]);
        }

        $catalogoExists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'catalogo' LIMIT 1",
            [$base]
        )->getRowArray();

        if (!$catalogoExists) {
            return redirect()->to('/pedidos/' . $codigo)->with('msg', [
                'type' => 'danger',
                'body' => 'No existe la tabla catalogo para este cliente.',
            ]);
        }

        $item = $dbAlfa->query(
            "SELECT id, nombre, precio, activo FROM `{$base}`.`catalogo` WHERE id = ? LIMIT 1",
            [$catalogoId]
        )->getRowArray();

        if (!$item || (int) ($item['activo'] ?? 0) !== 1) {
            return redirect()->to('/pedidos/' . $codigo)->with('msg', [
                'type' => 'danger',
                'body' => 'El item seleccionado no esta disponible.',
            ]);
        }

        $precio = (float) ($item['precio'] ?? 0);
        $total = $precio * $cantidad;
        $detalle = 'Item: ' . ($item['nombre'] ?? '') . ' | Cantidad: ' . $cantidad . ' | Total: $' . number_format($total, 2, '.', '');
        if ($observaciones !== '') {
            $detalle .= ' | Obs: ' . $observaciones;
        }

        $clienteId = null;
        $clientesExists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'clientes' LIMIT 1",
            [$base]
        )->getRowArray();
        if ($clientesExists) {
            if ($telefono !== '') {
                $existingCliente = $dbAlfa->query(
                    "SELECT id FROM `{$base}`.`clientes` WHERE telefono = ? LIMIT 1",
                    [$telefono]
                )->getRowArray();
                if ($existingCliente) {
                    $clienteId = (int) $existingCliente['id'];
                }
            }

            if ($clienteId === null) {
                $dbAlfa->query(
                    "INSERT INTO `{$base}`.`clientes` (`nombre`, `telefono`, `email`, `activo`) VALUES (?, ?, NULL, 1)",
                    [$nombre, $telefono !== '' ? $telefono : null]
                );
                $inserted = $dbAlfa->query('SELECT LAST_INSERT_ID() AS id')->getRowArray();
                $clienteId = (int) ($inserted['id'] ?? 0);
            }
        }

        $reservasExists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'reservas' LIMIT 1",
            [$base]
        )->getRowArray();

        if (!$reservasExists) {
            return redirect()->to('/pedidos/' . $codigo)->with('msg', [
                'type' => 'danger',
                'body' => 'No existe la tabla reservas para este cliente.',
            ]);
        }

        $dbAlfa->query(
            "INSERT INTO `{$base}`.`reservas` (`id_cliente`, `fecha`, `hora_desde`, `hora_hasta`, `estado`, `observaciones`)
             VALUES (?, CURDATE(), NULL, NULL, 'pendiente', ?)",
            [$clienteId, $detalle]
        );

        return redirect()->to('/pedidos/' . $codigo)->with('msg', [
            'type' => 'success',
            'body' => 'Pedido registrado correctamente.',
        ]);
    }

    private function resolveClienteComida(string $codigo): array
    {
        if (!preg_match('/^[0-9]{9}$/', $codigo)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $tenant = \Config\Services::tenant();
        $cliente = $tenant->resolveByCodigo($codigo);

        if (!$cliente) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $dbAlfa = Database::connect('alfareserva');
        $rubro = strtolower(trim((string) ($cliente['rubro'] ?? '')));
        if (!in_array($rubro, ['comida', 'pedidos'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $base = (string) ($cliente['base'] ?? '');
        if ($base === '') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $baseExists = $dbAlfa->query(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1',
            [$base]
        )->getRowArray();

        if (!$baseExists) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $cliente;
    }

    private function getCatalogo(string $databaseName): array
    {
        $dbAlfa = Database::connect('alfareserva');
        $exists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'catalogo' LIMIT 1",
            [$databaseName]
        )->getRowArray();

        if (!$exists) {
            return [];
        }

        return $dbAlfa->query(
            "SELECT id, nombre, descripcion, precio, activo
             FROM `{$databaseName}`.`catalogo`
             ORDER BY nombre ASC"
        )->getResultArray();
    }

    private function getBranding(string $codigo): array
    {
        $tenantDir = FCPATH . 'assets/tenants/' . $codigo . '/';
        $logoCandidates = ['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp'];
        $backgroundCandidates = ['fondo.jpg', 'fondo.png', 'fondo.webp', 'background.jpg', 'background.png', 'background.webp'];

        $logoUrl = null;
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
        ];
    }
}
