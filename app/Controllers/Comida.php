<?php

namespace App\Controllers;

use Config\Database;

class Comida extends BaseController
{
    private function formatAppDateTime(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return $value;
        }
        return date((string) APP_DATE_FORMAT . ' H:i', $ts);
    }

    private function estadoPedidoLabel(string $estado): string
    {
        $s = strtolower(trim($estado));
        return match ($s) {
            'pendiente' => 'Pendiente',
            'preparando envio', 'preparando_envio' => 'Preparando envio',
            'finalizado', 'completado' => 'Enviado / Finalizado',
            'recibido' => 'Recibido',
            'anulado' => 'Anulado',
            default => ucfirst($s !== '' ? $s : 'pendiente'),
        };
    }

    private function generateTrackingCode(): string
    {
        try {
            return strtoupper(bin2hex(random_bytes(5)));
        } catch (\Throwable $e) {
            return strtoupper(substr(md5(uniqid('', true)), 0, 10));
        }
    }

    private function tenantSlug(array $cliente): string
    {
        $linkSlug = \Config\Services::tenant()->extractSlugFromLink((string) ($cliente['link'] ?? ''));
        if ($linkSlug !== '') {
            return $linkSlug;
        }
        $base = strtolower(trim((string) ($cliente['base'] ?? '')));
        if ($base !== '' && preg_match('/^[a-z0-9_]+$/', $base) === 1) {
            return $base;
        }
        return (string) ($cliente['codigo'] ?? '');
    }

    private function publicBasePath(array $cliente): string
    {
        return '/' . ltrim($this->tenantSlug($cliente), '/');
    }

    private function adminBasePath(array $cliente): string
    {
        return $this->publicBasePath($cliente) . '/adminWeb';
    }

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

    public function index(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }

        \Config\Services::tenant()->activate($cliente);

        $branding = $this->getBranding((string) ($cliente['codigo'] ?? ''));
        $catalogo = $this->getCatalogo((string) $cliente['base']);

        return view('comida/index', [
            'cliente' => $cliente,
            'branding' => $branding,
            'catalogo' => $catalogo,
            'tenantNotice' => $cliente['tenant_access_notice'] ?? null,
            'tenantMode' => $cliente['tenant_access_mode'] ?? 'full',
            'publicBasePath' => $this->publicBasePath($cliente),
            'adminBasePath' => $this->adminBasePath($cliente),
        ]);
    }

    public function reservar(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $publicBasePath = $this->publicBasePath($cliente);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }

        $dbAlfa = Database::connect('alfareserva');
        $base = (string) $cliente['base'];
        $this->ensurePedidosTables($base);

        $nombre = trim((string) $this->request->getVar('nombre'));
        $telefono = trim((string) $this->request->getVar('telefono'));
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $direccion = trim((string) $this->request->getVar('direccion'));
        $entreCalles = trim((string) $this->request->getVar('entre_calles'));
        $ubicacionXRaw = trim((string) $this->request->getVar('ubicacion_x'));
        $ubicacionYRaw = trim((string) $this->request->getVar('ubicacion_y'));
        $catalogoId = (int) $this->request->getVar('catalogo_id');
        $cantidad = (int) $this->request->getVar('cantidad');
        $observaciones = trim((string) $this->request->getVar('observaciones'));
        $cartItemsRaw = trim((string) $this->request->getVar('cart_items'));

        if ($nombre === '') {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'Debe completar al menos el nombre del cliente.',
            ]);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'El email ingresado no es valido.',
            ]);
        }
        $ubicacionX = is_numeric($ubicacionXRaw) ? (float) $ubicacionXRaw : null;
        $ubicacionY = is_numeric($ubicacionYRaw) ? (float) $ubicacionYRaw : null;

        $catalogoExists = $dbAlfa->query(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'catalogo' LIMIT 1",
            [$base]
        )->getRowArray();

        if (!$catalogoExists) {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'No existe la tabla catalogo para este cliente.',
            ]);
        }

        $cartItems = [];
        if ($cartItemsRaw !== '') {
            $decoded = json_decode($cartItemsRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $it) {
                    $idArticulo = (int) ($it['id'] ?? 0);
                    $qty = (int) ($it['qty'] ?? 0);
                    if ($idArticulo > 0 && $qty > 0) {
                        $cartItems[] = ['idArticulo' => $idArticulo, 'cantidad' => $qty];
                    }
                }
            }
        }
        if (empty($cartItems) && $catalogoId > 0 && $cantidad > 0) {
            $cartItems[] = ['idArticulo' => $catalogoId, 'cantidad' => $cantidad];
        }
        if (empty($cartItems)) {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'Debe seleccionar al menos un articulo del catalogo.',
            ]);
        }

        $validItems = [];
        foreach ($cartItems as $line) {
            $idArticulo = (int) ($line['idArticulo'] ?? 0);
            $qty = (int) ($line['cantidad'] ?? 0);
            $row = $dbAlfa->query(
                "SELECT id, nombre, descripcion, precio, activo FROM `{$base}`.`catalogo` WHERE id = ? LIMIT 1",
                [$idArticulo]
            )->getRowArray();
            if (!$row || (int) ($row['activo'] ?? 0) !== 1 || $qty <= 0) {
                continue;
            }
            $validItems[] = [
                'idArticulo' => (int) ($row['id'] ?? 0),
                'nombre' => (string) ($row['nombre'] ?? ''),
                'descripcion' => (string) ($row['descripcion'] ?? ''),
                'precio' => (float) ($row['precio'] ?? 0),
                'cantidad' => $qty,
            ];
        }
        if (empty($validItems)) {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'No hay articulos validos para guardar el pedido.',
            ]);
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
                    "INSERT INTO `{$base}`.`clientes` (`nombre`, `telefono`, `email`, `activo`) VALUES (?, ?, ?, 1)",
                    [$nombre, $telefono !== '' ? $telefono : null, $email !== '' ? $email : null]
                );
                $inserted = $dbAlfa->query('SELECT LAST_INSERT_ID() AS id')->getRowArray();
                $clienteId = (int) ($inserted['id'] ?? 0);
            } else {
                $dbAlfa->query(
                    "UPDATE `{$base}`.`clientes` SET nombre = ?, email = COALESCE(?, email) WHERE id = ? LIMIT 1",
                    [$nombre, $email !== '' ? $email : null, $clienteId]
                );
            }
        }

        $trackingCode = $this->generateTrackingCode();
        $dbAlfa->query(
            "INSERT INTO `{$base}`.`Pedidos` (`id_cliente`, `nombre_cliente`, `direccion`, `entre_calles`, `ubicacion_x`, `ubicacion_y`, `telefono`, `email`, `fecha`, `observacion`, `estado`, `codigo_seguimiento`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'pendiente', ?)",
            [
                $clienteId,
                $nombre,
                $direccion !== '' ? $direccion : null,
                $entreCalles !== '' ? $entreCalles : null,
                $ubicacionX,
                $ubicacionY,
                $telefono !== '' ? $telefono : null,
                $email !== '' ? $email : null,
                $observaciones !== '' ? $observaciones : null,
                $trackingCode,
            ]
        );
        $pedidoInsert = $dbAlfa->query('SELECT LAST_INSERT_ID() AS id')->getRowArray();
        $pedidoId = (int) ($pedidoInsert['id'] ?? 0);
        foreach ($validItems as $line) {
            $dbAlfa->query(
                "INSERT INTO `{$base}`.`Pedios_Insumos` (`idpedido`, `idArticulo`, `Nombre`, `Descripcion`, `cantidad`, `precio`)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $pedidoId,
                    (int) $line['idArticulo'],
                    (string) $line['nombre'],
                    (string) $line['descripcion'],
                    (int) $line['cantidad'],
                    (float) $line['precio'],
                ]
            );
        }

        $trackingLink = rtrim(base_url(ltrim($publicBasePath, '/')), '/') . '/pedido/' . $trackingCode;
        session()->setFlashdata('tracking_link', $trackingLink);
        session()->setFlashdata('tracking_code', $trackingCode);
        session()->setFlashdata('tracking_id', $pedidoId);

        return redirect()->to($publicBasePath)->with('msg', [
            'type' => 'success',
            'body' => 'Pedido registrado correctamente. Guarda tu link de seguimiento.',
        ]);
    }

    public function seguimiento(string $identifier, string $trackingCode)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }

        $base = (string) ($cliente['base'] ?? '');
        $this->ensurePedidosTables($base);
        $db = Database::connect('alfareserva');
        $code = trim((string) $trackingCode);
        $pedido = $db->query(
            "SELECT id, nombre_cliente, direccion, entre_calles, ubicacion_x, ubicacion_y, telefono, email, fecha, observacion, estado, codigo_seguimiento, fecha_recibido
             FROM `{$base}`.`Pedidos`
             WHERE codigo_seguimiento IN (?, ?, ?)
             LIMIT 1",
            [$code, strtoupper($code), strtolower($code)]
        )->getRowArray();

        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $insumos = $db->query(
            "SELECT idArticulo, Nombre AS nombre, Descripcion AS descripcion, cantidad, precio
             FROM `{$base}`.`Pedios_Insumos`
             WHERE idpedido = ?
             ORDER BY id ASC",
            [(int) ($pedido['id'] ?? 0)]
        )->getResultArray();

        $pedido['fecha'] = $this->formatAppDateTime((string) ($pedido['fecha'] ?? ''));
        $pedido['fecha_recibido'] = $this->formatAppDateTime((string) ($pedido['fecha_recibido'] ?? ''));
        $pedido['estado_label'] = $this->estadoPedidoLabel((string) ($pedido['estado'] ?? 'pendiente'));

        return view('comida/pedido_tracking', [
            'cliente' => $cliente,
            'branding' => $this->getBranding((string) ($cliente['codigo'] ?? '')),
            'pedido' => $pedido,
            'insumos' => $insumos,
            'publicBasePath' => $this->publicBasePath($cliente),
        ]);
    }

    public function confirmarRecibido(string $identifier, string $trackingCode)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        $publicBasePath = $this->publicBasePath($cliente);
        $code = trim((string) $trackingCode);
        if ($code === '') {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'Codigo de seguimiento invalido.',
            ]);
        }

        $base = (string) ($cliente['base'] ?? '');
        $this->ensurePedidosTables($base);
        $db = Database::connect('alfareserva');
        $pedido = $db->query(
            "SELECT id, estado, codigo_seguimiento FROM `{$base}`.`Pedidos` WHERE codigo_seguimiento IN (?, ?, ?) LIMIT 1",
            [$code, strtoupper($code), strtolower($code)]
        )->getRowArray();
        if (!$pedido) {
            return redirect()->to($publicBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'No se encontro el pedido.',
            ]);
        }

        $estado = strtolower(trim((string) ($pedido['estado'] ?? '')));
        if (!in_array($estado, ['finalizado', 'completado', 'enviado', 'recibido'], true)) {
            return redirect()->to($publicBasePath . '/pedido/' . rawurlencode((string) ($pedido['codigo_seguimiento'] ?? $code)))->with('msg', [
                'type' => 'warning',
                'body' => 'Este pedido todavia no puede marcarse como recibido.',
            ]);
        }

        $db->query(
            "UPDATE `{$base}`.`Pedidos` SET estado = 'recibido', fecha_recibido = COALESCE(fecha_recibido, NOW()) WHERE id = ? LIMIT 1",
            [(int) ($pedido['id'] ?? 0)]
        );

        return redirect()->to($publicBasePath . '/pedido/' . rawurlencode((string) ($pedido['codigo_seguimiento'] ?? $code)))->with('msg', [
            'type' => 'success',
            'body' => 'Gracias. Confirmaste que el pedido fue recibido.',
        ]);
    }

    private function resolveClienteComida(string $identifier): array
    {
        $tenant = \Config\Services::tenant();
        $id = trim($identifier);
        $cliente = preg_match('/^[0-9]{9}$/', $id) === 1
            ? $tenant->resolveByCodigo($id)
            : $tenant->resolveBySlug($id);

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

        $columns = $dbAlfa->query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'catalogo'",
            [$databaseName]
        )->getResultArray();
        $columnNames = array_map(static fn(array $row): string => (string) ($row['COLUMN_NAME'] ?? ''), $columns);
        $optional = [];
        foreach (['imagen', 'foto', 'imagen_url', 'foto_url'] as $c) {
            if (in_array($c, $columnNames, true)) {
                $optional[] = $c;
            }
        }
        $select = 'id, nombre, descripcion, precio, activo';
        if (!empty($optional)) {
            $select .= ', ' . implode(', ', $optional);
        }

        return $dbAlfa->query(
            "SELECT {$select}
             FROM `{$databaseName}`.`catalogo`
             ORDER BY nombre ASC"
        )->getResultArray();
    }

    private function ensurePedidosTables(string $databaseName): void
    {
        $dbAlfa = Database::connect('alfareserva');
        $dbAlfa->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`Pedidos` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_cliente` INT(10) UNSIGNED NULL,
                `nombre_cliente` VARCHAR(255) NOT NULL,
                `direccion` VARCHAR(255) NULL,
                `entre_calles` VARCHAR(255) NULL,
                `ubicacion_x` DECIMAL(12,8) NULL,
                `ubicacion_y` DECIMAL(12,8) NULL,
                `telefono` VARCHAR(50) NULL,
                `email` VARCHAR(150) NULL,
                `fecha` DATETIME NOT NULL,
                `observacion` TEXT NULL,
                `estado` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
                `codigo_seguimiento` VARCHAR(40) NULL,
                `fecha_recibido` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_pedidos_cliente` (`id_cliente`),
                KEY `idx_pedidos_tracking` (`codigo_seguimiento`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $hasTracking = $dbAlfa->query(
            "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'Pedidos' AND COLUMN_NAME = 'codigo_seguimiento' LIMIT 1",
            [$databaseName]
        )->getRowArray();
        if (!$hasTracking) {
            $dbAlfa->query("ALTER TABLE `{$databaseName}`.`Pedidos` ADD COLUMN `codigo_seguimiento` VARCHAR(40) NULL AFTER `estado`");
            $dbAlfa->query("ALTER TABLE `{$databaseName}`.`Pedidos` ADD KEY `idx_pedidos_tracking` (`codigo_seguimiento`)");
        }
        $hasFechaRecibido = $dbAlfa->query(
            "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'Pedidos' AND COLUMN_NAME = 'fecha_recibido' LIMIT 1",
            [$databaseName]
        )->getRowArray();
        if (!$hasFechaRecibido) {
            $dbAlfa->query("ALTER TABLE `{$databaseName}`.`Pedidos` ADD COLUMN `fecha_recibido` DATETIME NULL AFTER `codigo_seguimiento`");
        }

        $dbAlfa->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`Pedios_Insumos` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `idpedido` INT(10) UNSIGNED NOT NULL,
                `idArticulo` INT(10) UNSIGNED NULL,
                `Nombre` VARCHAR(255) NULL,
                `Descripcion` TEXT NULL,
                `cantidad` INT(10) UNSIGNED NOT NULL DEFAULT 1,
                `precio` DECIMAL(12,2) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_pedios_insumos_pedido` (`idpedido`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function getBranding(string $codigo): array
    {
        $codigo = trim($codigo);
        $candidates = [
            [
                'dir' => rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $codigo . DIRECTORY_SEPARATOR,
                'url' => base_url(PUBLIC_FOLDER . $codigo . '/'),
            ],
            [
                'dir' => rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $codigo . DIRECTORY_SEPARATOR,
                'url' => base_url(PUBLIC_FOLDER . 'assets/tenants/' . $codigo . '/'),
            ],
        ];
        $logoCandidates = ['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp', 'LOGO.png', 'LOGO.jpg', 'LOGO.jpeg', 'LOGO.webp'];
        $backgroundCandidates = ['fondo.jpg', 'fondo.png', 'fondo.webp', 'background.jpg', 'background.png', 'background.webp'];

        $logoUrl = null;
        $backgroundUrl = null;
        foreach ($candidates as $candidate) {
            if (!is_dir($candidate['dir'])) {
                continue;
            }
            if ($logoUrl === null) {
                foreach ($logoCandidates as $file) {
                    $full = $candidate['dir'] . $file;
                    if (is_file($full)) {
                        $logoUrl = $candidate['url'] . $file . '?v=' . ((string) (@filemtime($full) ?: time()));
                        break;
                    }
                }
            }
            if ($backgroundUrl === null) {
                foreach ($backgroundCandidates as $file) {
                    $full = $candidate['dir'] . $file;
                    if (is_file($full)) {
                        $backgroundUrl = $candidate['url'] . $file . '?v=' . ((string) (@filemtime($full) ?: time()));
                        break;
                    }
                }
            }
        }

        return [
            'logo' => $logoUrl,
            'background' => $backgroundUrl,
        ];
    }
}
