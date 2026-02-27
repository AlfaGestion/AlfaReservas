<?php

namespace App\Controllers;

use Config\Database;

class ComidaAdmin extends BaseController
{
    private function formatAppDate(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return $value;
        }
        return date((string) APP_DATE_FORMAT, $ts);
    }

    private function getWebConfig(string $base): array
    {
        $defaults = [
            'dias_abiertos' => ['1', '2', '3', '4', '5', '6'],
            'turno1_desde' => '09:00',
            'turno1_hasta' => '13:00',
            'usar_segundo_turno' => '0',
            'turno2_desde' => '17:00',
            'turno2_hasta' => '21:00',
            'mp_public_key' => '',
            'mp_access_token' => '',
            'color_primary' => '#1f4467',
            'color_secondary' => '#f39323',
            'offer_enabled' => '0',
            'offer_title' => '',
            'offer_percent' => '0',
        ];

        $db = Database::connect('alfareserva');
        $tableExists = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'ta_configuracion' LIMIT 1",
            [$base]
        )->getRowArray();
        if (!$tableExists) {
            return $defaults;
        }

        $rows = $db->query(
            "SELECT clave, valor FROM `{$base}`.`ta_configuracion` WHERE clave IN ('dias_abiertos','turno1_desde','turno1_hasta','usar_segundo_turno','turno2_desde','turno2_hasta')"
        )->getResultArray();

        $cfg = $defaults;
        foreach ($rows as $r) {
            $k = (string) ($r['clave'] ?? '');
            $v = (string) ($r['valor'] ?? '');
            if (!array_key_exists($k, $cfg)) {
                continue;
            }
            if ($k === 'dias_abiertos') {
                $arr = json_decode($v, true);
                if (is_array($arr)) {
                    $cfg[$k] = array_values(array_filter(array_map(static fn($d) => (string) $d, $arr), static fn($d) => preg_match('/^[0-6]$/', $d) === 1));
                }
                continue;
            }
            $cfg[$k] = $v !== '' ? $v : $cfg[$k];
        }

        return $cfg;
    }

    private function getTenantUsers(string $base): array
    {
        $db = Database::connect('alfareserva');
        $tableExists = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'user' LIMIT 1",
            [$base]
        )->getRowArray();
        if (!$tableExists) {
            return [];
        }

        return $db->query(
            "SELECT id, `user`, email, name, active FROM `{$base}`.`user` ORDER BY id DESC"
        )->getResultArray();
    }

    private function getTenantClientes(string $base): array
    {
        $db = Database::connect('alfareserva');
        $tableExists = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'clientes' LIMIT 1",
            [$base]
        )->getRowArray();
        if (!$tableExists) {
            return [];
        }

        return $db->query(
            "SELECT id, nombre, telefono, email, activo FROM `{$base}`.`clientes` ORDER BY id DESC"
        )->getResultArray();
    }

    private function upsertWebConfig(string $base, string $key, string $value): void
    {
        $db = Database::connect('alfareserva');
        $row = $db->query(
            "SELECT id FROM `{$base}`.`ta_configuracion` WHERE clave = ? LIMIT 1",
            [$key]
        )->getRowArray();

        if ($row) {
            $db->query(
                "UPDATE `{$base}`.`ta_configuracion` SET valor = ? WHERE id = ?",
                [$value, (int) $row['id']]
            );
            return;
        }

        $db->query(
            "INSERT INTO `{$base}`.`ta_configuracion` (clave, valor) VALUES (?, ?)",
            [$key, $value]
        );
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

    private function adminBasePath(array $cliente): string
    {
        return '/' . ltrim($this->tenantSlug($cliente), '/') . '/adminWeb';
    }

    private function publicBasePath(array $cliente): string
    {
        return '/' . ltrim($this->tenantSlug($cliente), '/');
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

    public function login(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        $branding = $this->getBranding((string) ($cliente['codigo'] ?? ''));
        return view('comida/admin_login', [
            'cliente' => $cliente,
            'branding' => $branding,
            'tenantNotice' => $cliente['tenant_access_notice'] ?? null,
            'adminBasePath' => $this->adminBasePath($cliente),
            'publicBasePath' => $this->publicBasePath($cliente),
        ]);
    }

    public function doLogin(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        $base = (string) $cliente['base'];
        $usuario = trim((string) $this->request->getVar('usuario'));
        $password = (string) $this->request->getVar('password');

        if ($usuario === '' || $password === '') {
            return redirect()->to($adminBasePath . '/login')->with('msg', [
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

                return redirect()->to($adminBasePath);
            }

            return redirect()->to($adminBasePath . '/login')->with('msg', [
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
            return redirect()->to($adminBasePath . '/login')->with('msg', [
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

        return redirect()->to($adminBasePath);
    }

    public function logout(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        session()->remove([
            'tenant_admin_logged',
            'tenant_admin_codigo',
            'tenant_admin_base',
            'tenant_admin_user',
            'tenant_admin_id',
        ]);

        return redirect()->to($this->adminBasePath($cliente) . '/login');
    }

    public function index(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        $codigo = (string) ($cliente['codigo'] ?? '');
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($this->adminBasePath($cliente) . '/login');
        }
        $this->ensureCatalogoTable((string) $cliente['base']);

        $fechaFiltro = trim((string) $this->request->getGet('fecha'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFiltro)) {
            $fechaFiltro = date('Y-m-d');
        }

        $catalogo = $this->getCatalogo((string) $cliente['base']);
        $pedidos = $this->getPedidos((string) $cliente['base'], $fechaFiltro);
        $webConfig = $this->getWebConfig((string) $cliente['base']);
        $tenantUsers = $this->getTenantUsers((string) $cliente['base']);
        $tenantClientes = $this->getTenantClientes((string) $cliente['base']);
        $branding = $this->getBranding((string) ($cliente['codigo'] ?? ''));

        return view('comida/admin', [
            'cliente' => $cliente,
            'catalogo' => $catalogo,
            'pedidos' => $pedidos,
            'webConfig' => $webConfig,
            'tenantUsers' => $tenantUsers,
            'tenantClientes' => $tenantClientes,
            'branding' => $branding,
            'tenantAdminUser' => (string) (session()->get('tenant_admin_user') ?? ''),
            'tenantNotice' => $cliente['tenant_access_notice'] ?? null,
            'tenantMode' => $cliente['tenant_access_mode'] ?? 'full',
            'pedidoFechaFiltro' => $fechaFiltro,
            'adminBasePath' => $this->adminBasePath($cliente),
            'publicBasePath' => $this->publicBasePath($cliente),
        ]);
    }

    public function savePedidoEstado(string $identifier, int $pedidoId)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($adminBasePath . '/login');
        }

        $estado = strtolower(trim((string) $this->request->getVar('estado')));
        $permitidos = [
            'pendiente' => 'pendiente',
            'preparando_envio' => 'preparando envio',
            'finalizado' => 'finalizado',
            'recibido' => 'recibido',
            'anulado' => 'anulado',
        ];
        if (!array_key_exists($estado, $permitidos)) {
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'Estado de pedido invalido.',
            ]);
        }

        $base = (string) ($cliente['base'] ?? '');
        $db = Database::connect('alfareserva');
        if ($estado === 'recibido') {
            $db->query(
                "UPDATE `{$base}`.`Pedidos` SET estado = 'recibido', fecha_recibido = COALESCE(fecha_recibido, NOW()) WHERE id = ? LIMIT 1",
                [$pedidoId]
            );
        } else {
            $db->query(
                "UPDATE `{$base}`.`Pedidos` SET estado = ?, fecha_recibido = CASE WHEN ? IN ('finalizado','completado','pendiente','preparando envio','anulado') THEN NULL ELSE fecha_recibido END WHERE id = ? LIMIT 1",
                [$permitidos[$estado], $permitidos[$estado], $pedidoId]
            );
        }

        $fechaFiltro = trim((string) $this->request->getVar('fecha_filtro'));
        $qs = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFiltro) ? ('?fecha=' . $fechaFiltro) : '';
        return redirect()->to($adminBasePath . $qs)->with('msg', [
            'type' => 'success',
            'body' => 'Estado del pedido actualizado.',
        ]);
    }

    public function savePedidoEdicion(string $identifier, int $pedidoId)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($adminBasePath . '/login');
        }

        $detalle = trim((string) $this->request->getVar('observaciones'));
        $estado = strtolower(trim((string) $this->request->getVar('estado')));
        $permitidos = ['pendiente', 'preparando envio', 'finalizado', 'anulado', 'completado', 'recibido'];
        if ($estado !== '' && !in_array($estado, $permitidos, true)) {
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'Estado invalido para editar.',
            ]);
        }

        $base = (string) ($cliente['base'] ?? '');
        $db = Database::connect('alfareserva');
        if ($estado !== '') {
            if ($estado === 'recibido') {
                $db->query(
                    "UPDATE `{$base}`.`Pedidos` SET observacion = ?, estado = 'recibido', fecha_recibido = COALESCE(fecha_recibido, NOW()) WHERE id = ? LIMIT 1",
                    [$detalle, $pedidoId]
                );
            } else {
                $db->query(
                    "UPDATE `{$base}`.`Pedidos` SET observacion = ?, estado = ?, fecha_recibido = NULL WHERE id = ? LIMIT 1",
                    [$detalle, $estado, $pedidoId]
                );
            }
        } else {
            $db->query(
                "UPDATE `{$base}`.`Pedidos` SET observacion = ? WHERE id = ? LIMIT 1",
                [$detalle, $pedidoId]
            );
        }

        $fechaFiltro = trim((string) $this->request->getVar('fecha_filtro'));
        $qs = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFiltro) ? ('?fecha=' . $fechaFiltro) : '';
        return redirect()->to($adminBasePath . $qs)->with('msg', [
            'type' => 'success',
            'body' => 'Pedido actualizado.',
        ]);
    }

    public function saveCatalogo(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        if (!(bool) ($cliente['tenant_access_allowed'] ?? true)) {
            return $this->renderTenantBlocked($cliente);
        }
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        $isAjax = $this->request->isAJAX();
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            if ($isAjax) {
                return $this->response->setStatusCode(403)->setJSON([
                    'error' => true,
                    'message' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
                ]);
            }
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            if ($isAjax) {
                return $this->response->setStatusCode(401)->setJSON([
                    'error' => true,
                    'message' => 'Sesion no valida.',
                ]);
            }
            return redirect()->to($adminBasePath . '/login');
        }
        $base = (string) $cliente['base'];
        $this->ensureCatalogoTable($base);

        $itemId = (int) $this->request->getVar('id');
        $nombre = trim((string) $this->request->getVar('nombre'));
        $descripcion = trim((string) $this->request->getVar('descripcion'));
        $precio = (float) $this->request->getVar('precio');
        $imagenFile = $this->request->getFile('imagen');
        $imagenUrlInput = trim((string) $this->request->getVar('imagen_url'));

        if ($nombre === '' || $precio < 0) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON([
                    'error' => true,
                    'message' => 'Debe completar nombre y precio valido.',
                ]);
            }
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'danger',
                'body' => 'Debe completar nombre y precio valido.',
            ]);
        }

        $uploadProvided = $imagenFile && (int) $imagenFile->getError() !== UPLOAD_ERR_NO_FILE;
        $urlProvided = $imagenUrlInput !== '';
        $imageExt = '';
        if ($uploadProvided) {
            if (!$imagenFile->isValid()) {
                if ($isAjax) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'error' => true,
                        'message' => 'La imagen seleccionada no es valida.',
                    ]);
                }
                return redirect()->to($adminBasePath)->with('msg', [
                    'type' => 'danger',
                    'body' => 'La imagen seleccionada no es valida.',
                ]);
            }
            $imageExt = strtolower((string) $imagenFile->getExtension());
            if (!in_array($imageExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'error' => true,
                        'message' => 'Formato de imagen no permitido. Use png, jpg, jpeg o webp.',
                    ]);
                }
                return redirect()->to($adminBasePath)->with('msg', [
                    'type' => 'danger',
                    'body' => 'Formato de imagen no permitido. Use png, jpg, jpeg o webp.',
                ]);
            }
        }
        if (!$uploadProvided && $urlProvided) {
            if (!filter_var($imagenUrlInput, FILTER_VALIDATE_URL) || preg_match('#^https?://#i', $imagenUrlInput) !== 1) {
                if ($isAjax) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'error' => true,
                        'message' => 'URL de imagen invalida.',
                    ]);
                }
                return redirect()->to($adminBasePath)->with('msg', [
                    'type' => 'danger',
                    'body' => 'URL de imagen invalida.',
                ]);
            }
        }

        $dbAlfa = Database::connect('alfareserva');
        $isEdit = $itemId > 0;
        if ($isEdit) {
            $dbAlfa->query(
                "UPDATE `{$base}`.`catalogo` SET `nombre` = ?, `descripcion` = ?, `precio` = ? WHERE id = ? LIMIT 1",
                [$nombre, $descripcion !== '' ? $descripcion : null, $precio, $itemId]
            );
        } else {
            $dbAlfa->query(
                "INSERT INTO `{$base}`.`catalogo` (`nombre`, `descripcion`, `precio`, `activo`) VALUES (?, ?, ?, 1)",
                [$nombre, $descripcion !== '' ? $descripcion : null, $precio]
            );
            $inserted = $dbAlfa->query('SELECT LAST_INSERT_ID() AS id')->getRowArray();
            $itemId = (int) ($inserted['id'] ?? 0);
        }

        if (($uploadProvided || $urlProvided) && $itemId > 0) {
            $codigoFolder = preg_replace('/[^0-9A-Za-z_]/', '', (string) $codigo);
            $folderName = $codigoFolder . 'Catalogo';
            $targetDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $folderName;
            if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'error' => true,
                        'message' => 'Item guardado, pero no se pudo crear carpeta para imagen.',
                    ]);
                }
                return redirect()->to($adminBasePath)->with('msg', [
                    'type' => 'warning',
                    'body' => 'Item creado, pero no se pudo crear carpeta para imagen.',
                ]);
            }

            foreach (['png', 'jpg', 'jpeg', 'webp'] as $oldExt) {
                $oldFile = $targetDir . DIRECTORY_SEPARATOR . $itemId . '.' . $oldExt;
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $savedRelativePath = null;
            if ($uploadProvided) {
                $targetName = $itemId . '.' . $imageExt;
                if ($imagenFile->move($targetDir, $targetName, true)) {
                    $savedRelativePath = $folderName . '/' . $targetName;
                }
            } elseif ($urlProvided) {
                try {
                    $ctx = stream_context_create([
                        'http' => ['timeout' => 12, 'follow_location' => 1],
                        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                    ]);
                    $raw = @file_get_contents($imagenUrlInput, false, $ctx);
                    if ($raw !== false && strlen((string) $raw) > 0) {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mime = strtolower((string) $finfo->buffer($raw));
                        $mimeToExt = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                        ];
                        if (isset($mimeToExt[$mime])) {
                            $targetName = $itemId . '.' . $mimeToExt[$mime];
                            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $targetName;
                            if (@file_put_contents($targetPath, $raw) !== false) {
                                $savedRelativePath = $folderName . '/' . $targetName;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                }
            }

            if ($savedRelativePath !== null) {
                $dbAlfa->query(
                    "UPDATE `{$base}`.`catalogo` SET `imagen` = ? WHERE id = ? LIMIT 1",
                    [$savedRelativePath, $itemId]
                );
            }
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'error' => false,
                'message' => $isEdit ? 'Item actualizado correctamente.' : 'Item creado correctamente.',
                'data' => [
                    'catalogo' => $this->getCatalogo($base),
                    'saved_id' => $itemId,
                ],
            ]);
        }

        return redirect()->to($adminBasePath)->with('msg', [
            'type' => 'success',
            'body' => 'Item de catalogo guardado.',
        ]);
    }

    public function saveGeneralConfig(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($adminBasePath)->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($adminBasePath . '/login');
        }

        $base = (string) $cliente['base'];
        $dias = (array) ($this->request->getVar('dias_abiertos') ?? []);
        $dias = array_values(array_filter(array_map(static fn($d) => (string) $d, $dias), static fn($d) => preg_match('/^[0-6]$/', $d) === 1));

        $turno1Desde = trim((string) $this->request->getVar('turno1_desde'));
        $turno1Hasta = trim((string) $this->request->getVar('turno1_hasta'));
        $usarSegundo = $this->request->getVar('usar_segundo_turno') ? '1' : '0';
        $turno2Desde = trim((string) $this->request->getVar('turno2_desde'));
        $turno2Hasta = trim((string) $this->request->getVar('turno2_hasta'));

        $isHour = static fn(string $v): bool => preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $v) === 1;
        if (empty($dias) || !$isHour($turno1Desde) || !$isHour($turno1Hasta)) {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'danger',
                'body' => 'Complete dias y horario del turno 1 correctamente.',
            ]);
        }
        if ($usarSegundo === '1' && (!$isHour($turno2Desde) || !$isHour($turno2Hasta))) {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'danger',
                'body' => 'Complete horario del turno 2 correctamente.',
            ]);
        }

        $this->upsertWebConfig($base, 'dias_abiertos', json_encode($dias));
        $this->upsertWebConfig($base, 'turno1_desde', $turno1Desde);
        $this->upsertWebConfig($base, 'turno1_hasta', $turno1Hasta);
        $this->upsertWebConfig($base, 'usar_segundo_turno', $usarSegundo);
        $this->upsertWebConfig($base, 'turno2_desde', $turno2Desde);
        $this->upsertWebConfig($base, 'turno2_hasta', $turno2Hasta);

        return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
            'type' => 'success',
            'body' => 'Configuracion general guardada.',
        ]);
    }

    public function saveAdminUser(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($adminBasePath . '/login');
        }

        $base = (string) $cliente['base'];
        $user = trim((string) $this->request->getVar('user'));
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $password = (string) $this->request->getVar('password');

        if ($user === '' || $password === '') {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'danger',
                'body' => 'Debe completar usuario y contrasena.',
            ]);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'danger',
                'body' => 'Email invalido.',
            ]);
        }

        $db = Database::connect('alfareserva');
        if ($email !== '') {
            $dup = $db->query(
                "SELECT id FROM `{$base}`.`user` WHERE `user` = ? OR email = ? LIMIT 1",
                [$user, $email]
            )->getRowArray();
        } else {
            $dup = $db->query(
                "SELECT id FROM `{$base}`.`user` WHERE `user` = ? LIMIT 1",
                [$user]
            )->getRowArray();
        }
        if ($dup) {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'danger',
                'body' => 'El usuario ya existe en esta web.',
            ]);
        }

        $db->query(
            "INSERT INTO `{$base}`.`user` (`user`, email, password, name, active) VALUES (?, ?, ?, ?, 1)",
            [$user, $email !== '' ? $email : null, password_hash($password, PASSWORD_DEFAULT), $user]
        );

        return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
            'type' => 'success',
            'body' => 'Usuario creado correctamente.',
        ]);
    }

    public function saveWebSettings(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($adminBasePath . '/login');
        }

        $base = (string) $cliente['base'];
        $mpPublic = trim((string) $this->request->getVar('mp_public_key'));
        $mpAccess = trim((string) $this->request->getVar('mp_access_token'));
        $colorPrimary = trim((string) $this->request->getVar('color_primary'));
        $colorSecondary = trim((string) $this->request->getVar('color_secondary'));

        $this->upsertWebConfig($base, 'mp_public_key', $mpPublic);
        $this->upsertWebConfig($base, 'mp_access_token', $mpAccess);
        $this->upsertWebConfig($base, 'color_primary', $colorPrimary);
        $this->upsertWebConfig($base, 'color_secondary', $colorSecondary);

        return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
            'type' => 'success',
            'body' => 'Configuracion web guardada.',
        ]);
    }

    public function saveOfferSettings(string $identifier)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        $adminBasePath = $this->adminBasePath($cliente);
        if ((string) ($cliente['tenant_access_mode'] ?? 'full') === 'read_only') {
            return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
                'type' => 'warning',
                'body' => (string) ($cliente['tenant_access_notice'] ?? 'Modo solo lectura activo.'),
            ]);
        }
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($adminBasePath . '/login');
        }

        $base = (string) $cliente['base'];
        $enabled = $this->request->getVar('offer_enabled') ? '1' : '0';
        $title = trim((string) $this->request->getVar('offer_title'));
        $percent = (string) max(0, min(100, (int) $this->request->getVar('offer_percent')));

        $this->upsertWebConfig($base, 'offer_enabled', $enabled);
        $this->upsertWebConfig($base, 'offer_title', $title);
        $this->upsertWebConfig($base, 'offer_percent', $percent);

        return redirect()->to($adminBasePath . '#settings-pane')->with('msg', [
            'type' => 'success',
            'body' => 'Ofertas guardadas.',
        ]);
    }

    public function pedidoDetalle(string $identifier, int $pedidoId)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        if (!$this->isTenantAdminLogged($codigo)) {
            return redirect()->to($this->adminBasePath($cliente) . '/login');
        }

        $base = (string) ($cliente['base'] ?? '');
        $db = Database::connect('alfareserva');
        $row = $db->query(
            "SELECT p.id, p.fecha, p.estado, p.observacion AS observaciones,
                    p.nombre_cliente AS cliente_nombre, p.telefono AS cliente_telefono,
                    p.email, p.direccion, p.entre_calles, p.ubicacion_x, p.ubicacion_y
             FROM `{$base}`.`Pedidos` p
             WHERE p.id = ?
             LIMIT 1",
            [$pedidoId]
        )->getRowArray();

        if (!$row) {
            return redirect()->to($this->adminBasePath($cliente))->with('msg', [
                'type' => 'danger',
                'body' => 'Pedido no encontrado.',
            ]);
        }

        $row['fecha'] = $this->formatAppDate((string) ($row['fecha'] ?? ''));

        return view('comida/pedido_detalle', [
            'cliente' => $cliente,
            'pedido' => $row,
            'adminBasePath' => $this->adminBasePath($cliente),
            'branding' => $this->getBranding((string) ($cliente['codigo'] ?? '')),
        ]);
    }

    public function pedidoDetalleJson(string $identifier, int $pedidoId)
    {
        $cliente = $this->resolveClienteComida($identifier);
        $codigo = (string) ($cliente['codigo'] ?? '');
        if (!$this->isTenantAdminLogged($codigo)) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Sesion no valida.',
            ]);
        }

        $base = (string) ($cliente['base'] ?? '');
        $db = Database::connect('alfareserva');
        $pedido = $db->query(
            "SELECT p.id, p.fecha, p.estado, p.observacion AS observaciones,
                    p.nombre_cliente AS cliente_nombre, p.telefono AS cliente_telefono,
                    p.email, p.direccion, p.entre_calles, p.ubicacion_x, p.ubicacion_y
             FROM `{$base}`.`Pedidos` p
             WHERE p.id = ?
             LIMIT 1",
            [$pedidoId]
        )->getRowArray();

        if (!$pedido) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => true,
                'message' => 'Pedido no encontrado.',
            ]);
        }

        $insumos = [];
        $insumosTable = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'Pedios_Insumos' LIMIT 1",
            [$base]
        )->getRowArray();
        if ($insumosTable) {
            $insumos = $db->query(
                "SELECT id, idArticulo, Nombre AS nombre, Descripcion AS descripcion, cantidad, precio
                 FROM `{$base}`.`Pedios_Insumos`
                 WHERE idpedido = ?
                 ORDER BY id ASC",
                [$pedidoId]
            )->getResultArray();
        }

        $pedido['fecha'] = $this->formatAppDate((string) ($pedido['fecha'] ?? ''));
        return $this->response->setJSON([
            'error' => false,
            'data' => [
                'pedido' => $pedido,
                'insumos' => $insumos,
            ],
        ]);
    }

    private function isTenantAdminLogged(string $codigo): bool
    {
        return (bool) session()->get('tenant_admin_logged')
            && (string) session()->get('tenant_admin_codigo') === $codigo;
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
            $hasImagen = $dbAlfa->query(
                "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'catalogo' AND COLUMN_NAME = 'imagen' LIMIT 1",
                [$databaseName]
            )->getRowArray();
            if (!$hasImagen) {
                $dbAlfa->query(
                    "ALTER TABLE `{$databaseName}`.`catalogo` ADD COLUMN `imagen` VARCHAR(255) NULL AFTER `precio`"
                );
            }
            return;
        }

        $dbAlfa->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`catalogo` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre` VARCHAR(255) NOT NULL,
                `descripcion` TEXT NULL,
                `precio` DECIMAL(12,2) NOT NULL DEFAULT 0,
                `imagen` VARCHAR(255) NULL,
                `activo` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function getCatalogo(string $databaseName): array
    {
        $dbAlfa = Database::connect('alfareserva');
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
            "SELECT {$select} FROM `{$databaseName}`.`catalogo` ORDER BY id DESC"
        )->getResultArray();
    }

    private function getPedidos(string $databaseName, string $fechaFiltro): array
    {
        $dbAlfa = Database::connect('alfareserva');
        $pedidosExists = $dbAlfa->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'Pedidos' LIMIT 1",
            [$databaseName]
        )->getRowArray();
        if (!$pedidosExists) {
            return [];
        }

        $rows = $dbAlfa->query(
            "SELECT p.id, p.fecha, p.estado, p.observacion AS observaciones,
                    p.nombre_cliente AS cliente_nombre, p.telefono AS cliente_telefono
             FROM `{$databaseName}`.`Pedidos` p
             WHERE DATE(p.fecha) = ?
             ORDER BY
                CASE
                    WHEN LOWER(TRIM(p.estado)) = 'pendiente' THEN 0
                    WHEN LOWER(TRIM(p.estado)) IN ('preparando envio', 'preparando_envio') THEN 1
                    WHEN LOWER(TRIM(p.estado)) IN ('finalizado', 'completado') THEN 2
                    WHEN LOWER(TRIM(p.estado)) = 'recibido' THEN 3
                    WHEN LOWER(TRIM(p.estado)) = 'anulado' THEN 4
                    ELSE 5
                END,
                p.id DESC",
            [$fechaFiltro]
        )->getResultArray();
        foreach ($rows as &$row) {
            $row['fecha'] = $this->formatAppDate((string) ($row['fecha'] ?? ''));
        }
        unset($row);
        return $rows;
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

        $logoUrl = null;
        $backgroundUrl = null;
        foreach ($candidates as $candidate) {
            if (!is_dir($candidate['dir'])) {
                continue;
            }
            if ($logoUrl === null) {
                $logoMatches = glob($candidate['dir'] . '{logo,LOGO}.{png,jpg,jpeg,webp}', GLOB_BRACE) ?: [];
                if (!empty($logoMatches) && is_file($logoMatches[0])) {
                    $logoFile = basename($logoMatches[0]);
                    $logoUrl = $candidate['url'] . $logoFile . '?v=' . ((string) (@filemtime($logoMatches[0]) ?: time()));
                }
            }
            if ($backgroundUrl === null) {
                $bgMatches = glob($candidate['dir'] . '{fondo,background}.{png,jpg,jpeg,webp}', GLOB_BRACE) ?: [];
                if (!empty($bgMatches) && is_file($bgMatches[0])) {
                    $bgFile = basename($bgMatches[0]);
                    $backgroundUrl = $candidate['url'] . $bgFile . '?v=' . ((string) (@filemtime($bgMatches[0]) ?: time()));
                }
            }
        }

        return [
            'logo' => $logoUrl,
            'background' => $backgroundUrl,
        ];
    }
}
