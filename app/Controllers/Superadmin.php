<?php

namespace App\Controllers;

use App\Models\BookingsModel;
use App\Models\ClientesModel;
use App\Models\CustomersModel;
use App\Models\FieldsModel;
use App\Models\CancelReservationsModel;
use App\Models\ConfigModel;
use App\Models\LocalitiesModel;
use App\Models\BookingSlotsModel;
use App\Models\MercadoPagoKeysModel;
use App\Models\MercadoPagoModel;
use App\Models\OffersModel;
use App\Models\PaymentsModel;
use App\Models\RateModel;
use App\Models\RubrosModel;
use App\Models\TimeModel;
use App\Models\UsersModel;
use Config\Database;

class Superadmin extends BaseController
{
    private function cleanupExpiredPendingBookings(): void
    {
        $bookingsModel = new BookingsModel();
        $bookingSlotsModel = new BookingSlotsModel();
        $mercadoPagoModel = new MercadoPagoModel();
        $paymentsModel = new PaymentsModel();

        $now = date('Y-m-d H:i:s');
        $threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        // 1) Tomar los slots pending vencidos para identificar reservas candidatas.
        $expiredPendingSlots = $bookingSlotsModel
            ->select('booking_id')
            ->where('active', 1)
            ->where('status', 'pending')
            ->where('expires_at <', $now)
            ->findAll();

        // 2) Expirar esos slots.
        $bookingSlotsModel->where('active', 1)
            ->where('status', 'pending')
            ->where('expires_at <', $now)
            ->set(['active' => 0, 'status' => 'expired'])
            ->update();

        if (empty($expiredPendingSlots)) {
            return;
        }

        $candidateIds = [];
        foreach ($expiredPendingSlots as $slot) {
            $bookingId = (int)($slot['booking_id'] ?? 0);
            if ($bookingId > 0) {
                $candidateIds[$bookingId] = true;
            }
        }
        $candidateIds = array_keys($candidateIds);

        // Fallback: reservas provisionales vencidas por booking_time (aunque no tengan slot enlazado).
        $staleBookings = $bookingsModel
            ->select('id')
            ->where('annulled', 0)
            ->where('mp', 0)
            ->where('payment <=', 0)
            ->where('booking_time <', $threshold)
            ->groupStart()
            ->where('approved', 0)
            ->orWhere('approved', null)
            ->groupEnd()
            ->findAll();

        foreach ($staleBookings as $row) {
            $bookingId = (int)($row['id'] ?? 0);
            if ($bookingId > 0) {
                $candidateIds[$bookingId] = true;
            }
        }

        $candidateIds = array_keys($candidateIds);

        if (empty($candidateIds)) {
            return;
        }
        // 3) Solo reservas provisionales sin pago confirmado.
        $candidates = $bookingsModel->whereIn('id', $candidateIds)
            ->where('annulled', 0)
            ->where('mp', 0)
            ->where('payment <=', 0)
            ->groupStart()
            ->where('approved', 0)
            ->orWhere('approved', null)
            ->groupEnd()
            ->findAll();

        if (empty($candidates)) {
            return;
        }

        $idsToDelete = [];
        foreach ($candidates as $booking) {
            $bookingId = (int)$booking['id'];

            $hasApprovedMp = $mercadoPagoModel->where('id_booking', $bookingId)
                ->where('status', 'approved')
                ->first();

            $hasPayment = $paymentsModel->where('id_booking', $bookingId)->first();

            if (!$hasApprovedMp && !$hasPayment) {
                $idsToDelete[] = $bookingId;
            }
        }

        if (empty($idsToDelete)) {
            return;
        }

        // 4) Limpiar registros relacionados y luego la reserva.
        $bookingSlotsModel->whereIn('booking_id', $idsToDelete)
            ->where('active', 1)
            ->set(['active' => 0, 'status' => 'expired'])
            ->update();

        $paymentsModel->whereIn('id_booking', $idsToDelete)->delete();
        $mercadoPagoModel->whereIn('id_booking', $idsToDelete)->delete();
        $bookingsModel->delete($idsToDelete);
    }

    private function getNextClienteCodigo(): string
    {
        $prefix = '11201';
        $db = Database::connect('alfareserva');

        if (!$db->tableExists('clientes')) {
            return $prefix . '0001';
        }

        $row = $db->query(
            "SELECT MAX(CAST(SUBSTRING(codigo, 6) AS UNSIGNED)) AS max_suffix
             FROM clientes
             WHERE codigo LIKE ?",
            [$prefix . '%']
        )->getRowArray();

        $maxSuffix = (int) ($row['max_suffix'] ?? 0);
        if ($maxSuffix <= 0) {
            return $prefix . '0001';
        }

        $nextNumber = $maxSuffix + 1;
        $candidate = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        // Defensa extra ante colisiones (ej. alta simultanea)
        $tries = 0;
        while ($tries < 200) {
            $exists = $db->table('clientes')->select('id')->where('codigo', $candidate)->get()->getRowArray();
            if (!$exists) {
                return $candidate;
            }
            $nextNumber++;
            $candidate = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $tries++;
        }

        throw new \RuntimeException('No se pudo generar un codigo de cliente unico.');
    }

    private function databaseExists(string $databaseName): bool
    {
        $db = Database::connect('alfareserva');
        $row = $db->query(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1',
            [$databaseName]
        )->getRowArray();

        return !empty($row);
    }

    private function createDatabase(string $databaseName): void
    {
        $db = Database::connect('alfareserva');
        $db->query('CREATE DATABASE `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }

    private function provisionClienteDatabase(string $databaseName, string $rubroDescripcion): void
    {
        $db = Database::connect('alfareserva');

        $db->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`user` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user` VARCHAR(100) NOT NULL,
                `email` VARCHAR(150) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `name` VARCHAR(255) NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_user_email` (`email`),
                UNIQUE KEY `uq_user_user` (`user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $db->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`clientes` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre` VARCHAR(255) NOT NULL,
                `telefono` VARCHAR(50) NULL,
                `email` VARCHAR(150) NULL,
                `activo` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $db->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`reservas` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_cliente` INT(10) UNSIGNED NULL,
                `fecha` DATE NOT NULL,
                `hora_desde` VARCHAR(10) NULL,
                `hora_hasta` VARCHAR(10) NULL,
                `estado` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
                `observaciones` TEXT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_reservas_cliente` (`id_cliente`),
                CONSTRAINT `fk_reservas_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $db->query(
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

        $db->query(
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

        $db->query(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`ta_configuracion` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `clave` VARCHAR(100) NOT NULL,
                `valor` TEXT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_ta_configuracion_clave` (`clave`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $rubroNormalizado = strtolower(trim($rubroDescripcion));
        if (in_array($rubroNormalizado, ['comida', 'pedidos'], true)) {
            $db->query(
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
    }

    private function normalizeTenantKey(string $value): string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return '';
        }

        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($normalized) && $normalized !== '') {
            $value = $normalized;
        }

        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        if ($value === '') {
            return '';
        }

        return substr($value, 0, 90);
    }

    private function buildClienteLink(string $slug): string
    {
        return '/' . ltrim($slug, '/');
    }

    private function clienteBaseExists(string $base, ?int $excludeId = null): bool
    {
        $base = trim($base);
        if ($base === '') {
            return false;
        }

        $db = Database::connect('alfareserva');
        $builder = $db->table('clientes')->select('id')->where('base', $base);
        if (($excludeId ?? 0) > 0) {
            $builder->where('id <>', (int) $excludeId);
        }
        return (bool) $builder->get()->getRowArray();
    }

    private function clienteLinkExists(string $link, ?int $excludeId = null): bool
    {
        $link = trim($link);
        if ($link === '') {
            return false;
        }

        $db = Database::connect('alfareserva');
        $builder = $db->table('clientes')->select('id')->where('link', $link);
        if (($excludeId ?? 0) > 0) {
            $builder->where('id <>', (int) $excludeId);
        }
        return (bool) $builder->get()->getRowArray();
    }

    private function nextAvailableBase(string $seed, ?int $excludeId = null): string
    {
        $base = $this->normalizeTenantKey($seed);
        if ($base === '') {
            return '';
        }

        $candidate = substr($base, 0, 90);
        $counter = 1;
        while ($this->databaseExists($candidate) || $this->clienteBaseExists($candidate, $excludeId)) {
            $suffix = '_' . $counter;
            $candidate = substr($base, 0, max(1, 90 - strlen($suffix))) . $suffix;
            $counter++;
            if ($counter > 9999) {
                throw new \RuntimeException('No se pudo generar una base unica para el cliente.');
            }
        }

        return $candidate;
    }

    private function nextAvailableLinkSlug(string $seed, ?int $excludeId = null): string
    {
        $slug = $this->normalizeTenantKey($seed);
        if ($slug === '') {
            return '';
        }

        $candidate = substr($slug, 0, 90);
        $counter = 1;
        while ($this->clienteLinkExists($this->buildClienteLink($candidate), $excludeId)) {
            $suffix = '_' . $counter;
            $candidate = substr($slug, 0, max(1, 90 - strlen($suffix))) . $suffix;
            $counter++;
            if ($counter > 9999) {
                throw new \RuntimeException('No se pudo generar un link unico para el cliente.');
            }
        }

        return $candidate;
    }

    private function resolveScopedPrimaryCliente(array $allClientes, string $sessionCuenta, string $sessionEmail): ?array
    {
        foreach ($allClientes as $row) {
            $code = trim((string) ($row['codigo'] ?? ''));
            $base = trim((string) ($row['base'] ?? ''));
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if (
                ($sessionCuenta !== '' && ($code === $sessionCuenta || $base === $sessionCuenta))
                || ($sessionEmail !== '' && $email === $sessionEmail)
            ) {
                return $row;
            }
        }
        return null;
    }

    private function scopedCurrentClienteOrNull(): ?array
    {
        $sessionEmail = strtolower(trim((string) session()->get('email')));
        $sessionCuenta = trim((string) session()->get('cuenta'));
        $allClientes = $this->getClientesRows();
        return $this->resolveScopedPrimaryCliente($allClientes, $sessionCuenta, $sessionEmail);
    }

    private function getBookingsSummaryByBase(string $base): array
    {
        $summary = [
            'total' => 0,
            'active' => 0,
            'annulled' => 0,
            'upcoming' => 0,
        ];

        $base = trim($base);
        if ($base === '' || preg_match('/^[A-Za-z0-9_-]+$/', $base) !== 1) {
            return $summary;
        }
        if (!$this->databaseExists($base)) {
            return $summary;
        }

        $db = Database::connect('alfareserva');
        $tableExists = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'bookings' LIMIT 1",
            [$base]
        )->getRowArray();
        if (!$tableExists) {
            return $summary;
        }

        try {
            $row = $db->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN annulled = 0 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN annulled = 1 THEN 1 ELSE 0 END) AS annulled,
                    SUM(CASE WHEN annulled = 0 AND date >= CURDATE() THEN 1 ELSE 0 END) AS upcoming
                FROM `{$base}`.`bookings`"
            )->getRowArray();

            if ($row) {
                $summary['total'] = (int) ($row['total'] ?? 0);
                $summary['active'] = (int) ($row['active'] ?? 0);
                $summary['annulled'] = (int) ($row['annulled'] ?? 0);
                $summary['upcoming'] = (int) ($row['upcoming'] ?? 0);
            }
        } catch (\Throwable $e) {
            return $summary;
        }

        return $summary;
    }

    private function getTenantUsers(string $base): array
    {
        $base = trim($base);
        if ($base === '' || preg_match('/^[A-Za-z0-9_-]+$/', $base) !== 1) {
            return [];
        }
        if (!$this->databaseExists($base)) {
            return [];
        }

        $db = Database::connect('alfareserva');
        try {
            $table = null;
            $hasUser = $db->query(
                "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'user' LIMIT 1",
                [$base]
            )->getRowArray();
            if ($hasUser) {
                $table = 'user';
            } else {
                $hasUsers = $db->query(
                    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' LIMIT 1",
                    [$base]
                )->getRowArray();
                if ($hasUsers) {
                    $table = 'users';
                }
            }

            if ($table === null) {
                return [];
            }

            $cols = $db->query(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [$base, $table]
            )->getResultArray();
            $colNames = array_map(static fn(array $r): string => (string) ($r['COLUMN_NAME'] ?? ''), $cols);

            $emailExpr = in_array('email', $colNames, true) ? 'email' : 'NULL';
            $activeExpr = in_array('active', $colNames, true) ? 'active' : '1';
            $nameExpr = in_array('name', $colNames, true) ? 'name' : '`user`';

            return $db->query(
                "SELECT id, `user`, {$emailExpr} AS email, {$nameExpr} AS name, {$activeExpr} AS active
                 FROM `{$base}`.`{$table}`
                 ORDER BY id DESC"
            )->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getClientUsersQuota(array $cliente, ?string $baseOverride = null): array
    {
        $base = trim((string) ($baseOverride ?? ($cliente['base'] ?? '')));
        $totalAllowed = max(0, (int) ($cliente['included_users'] ?? 0));
        $usedActive = 0;

        if ($base !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $base) === 1 && $this->databaseExists($base)) {
            try {
                $db = Database::connect('alfareserva');
                $row = $db->query(
                    "SELECT COUNT(*) AS total FROM `{$base}`.`user` WHERE active = 1"
                )->getRowArray();
                $usedActive = (int) ($row['total'] ?? 0);
            } catch (\Throwable $e) {
                $usedActive = 0;
            }
        }

        return [
            'total' => $totalAllowed,
            'used' => $usedActive,
            'remaining' => max(0, $totalAllowed - $usedActive),
        ];
    }

    private function scopedTenantBaseForAdminOps(): ?string
    {
        $sessionEmail = strtolower(trim((string) session()->get('email')));
        $isMasterAdmin = ((int) session()->get('superadmin') === 1)
            && $sessionEmail !== ''
            && $sessionEmail === $this->masterAdminEmail();
        if ($isMasterAdmin) {
            return null;
        }

        $cliente = $this->scopedCurrentClienteOrNull();
        if (!is_array($cliente)) {
            return null;
        }

        $base = trim((string) ($cliente['base'] ?? ''));
        if ($base === '' || preg_match('/^[A-Za-z0-9_-]+$/', $base) !== 1) {
            return null;
        }
        if (!$this->databaseExists($base)) {
            return null;
        }

        return $base;
    }

    private function getBookingsByRangeFromTenantBase(string $base, string $from, string $to, int $annulled): array
    {
        $db = Database::connect('alfareserva');

        $hasBookings = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'bookings' LIMIT 1",
            [$base]
        )->getRowArray();
        if (!$hasBookings) {
            return [];
        }

        $rows = $db->query(
            "SELECT *
             FROM `{$base}`.`bookings`
             WHERE `date` >= ? AND `date` <= ? AND `annulled` = ?
             ORDER BY `time_from` ASC",
            [$from, $to, $annulled]
        )->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $bookingIds = array_map(static fn(array $r): int => (int) ($r['id'] ?? 0), $rows);
        $bookingIds = array_values(array_filter($bookingIds, static fn(int $v): bool => $v > 0));

        $paidByBooking = [];
        $hasPayments = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'payments' LIMIT 1",
            [$base]
        )->getRowArray();
        if ($hasPayments && !empty($bookingIds)) {
            $idsSql = implode(',', array_map('intval', $bookingIds));
            $paymentsRows = $db->query(
                "SELECT id_booking, SUM(amount) AS paid_total
                 FROM `{$base}`.`payments`
                 WHERE id_booking IN ({$idsSql})
                 GROUP BY id_booking"
            )->getResultArray();
            foreach ($paymentsRows as $pr) {
                $paidByBooking[(int) ($pr['id_booking'] ?? 0)] = (float) ($pr['paid_total'] ?? 0);
            }
        }

        $fieldsById = [];
        $hasFields = $db->query(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'fields' LIMIT 1",
            [$base]
        )->getRowArray();
        if ($hasFields) {
            $frows = $db->query("SELECT id, name FROM `{$base}`.`fields`")->getResultArray();
            foreach ($frows as $f) {
                $fieldsById[(int) ($f['id'] ?? 0)] = (string) ($f['name'] ?? 'N/D');
            }
        }

        $bookings = [];
        foreach ($rows as $booking) {
            $bookingId = (int) ($booking['id'] ?? 0);
            $paymentsSum = (float) ($paidByBooking[$bookingId] ?? 0);
            $bookingPaid = (float) ($booking['payment'] ?? 0);
            $paid = max($paymentsSum, $bookingPaid);
            $total = (float) ($booking['total'] ?? 0);
            $difference = max(0, $total - $paid);

            $bookings[] = [
                'id' => $booking['id'],
                'cancha' => $fieldsById[(int) ($booking['id_field'] ?? 0)] ?? 'N/D',
                'fecha' => date('d/m/Y', strtotime((string) ($booking['date'] ?? ''))),
                'horario' => (string) ($booking['time_from'] ?? '') . ' a ' . (string) ($booking['time_until'] ?? ''),
                'nombre' => $booking['name'] ?? '',
                'telefono' => $booking['phone'] ?? '',
                'creado_por' => $booking['created_by_name'] ?? $booking['created_by_type'] ?? 'N/D',
                'editado_por' => $booking['edited_by_name'] ?? null,
                'editado_en' => $booking['edited_at'] ?? null,
                'pago_total' => $paid >= $total ? 'Si' : 'No',
                'total_reserva' => $booking['total'] ?? 0,
                'diferencia' => $difference,
                'monto_reserva' => $paid,
                'descripcion' => $booking['description'] ?? '',
                'metodo_pago' => $booking['payment_method'] ?? '',
                'anulada' => $booking['annulled'] ?? 0,
                'mp' => $booking['mp'] ?? 1,
            ];
        }

        return $bookings;
    }

    private function resolveClientLogoUrl(string $clientCodigo): string
    {
        $clientCodigo = trim($clientCodigo);
        if ($clientCodigo === '') {
            return '';
        }

        $basePath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR;
        $logoCandidates = [
            [
                'dir' => $basePath . $clientCodigo . DIRECTORY_SEPARATOR,
                'url' => base_url(PUBLIC_FOLDER . $clientCodigo . '/'),
            ],
            [
                'dir' => $basePath . 'assets' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $clientCodigo . DIRECTORY_SEPARATOR,
                'url' => base_url(PUBLIC_FOLDER . 'assets/tenants/' . $clientCodigo . '/'),
            ],
        ];

        foreach ($logoCandidates as $candidate) {
            if (!is_dir($candidate['dir'])) {
                continue;
            }

            $files = glob($candidate['dir'] . '{logo,LOGO}.*', GLOB_BRACE) ?: [];
            foreach ($files as $filePath) {
                if (!is_file($filePath)) {
                    continue;
                }
                $ext = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
                if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                    continue;
                }
                $fileName = basename($filePath);
                $version = @filemtime($filePath) ?: time();
                return $candidate['url'] . $fileName . '?v=' . $version;
            }
        }

        return '';
    }

    private function getClientesRows(): array
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('clientes')) {
            return [];
        }

        $builder = $db->table('clientes c')
            ->select(
                'c.id, c.codigo, c.NombreApellido, c.razon_social, c.base, c.id_rubro, c.email, c.telefono, c.dni, c.localidad, c.habilitado, c.estado, c.created_at, c.trial_start, c.trial_end, c.paid_through, c.grace_end, c.link, r.descripcion AS rubro_descripcion'
            )
            ->join('rubros r', 'r.id = c.id_rubro', 'left');

        if ($db->tableExists('cliente_contratos')) {
            $builder
                ->select('cc.id AS contrato_id, cc.plan_id, cc.periodo AS contrato_periodo, cc.estado AS contrato_estado, cc.included_users, cc.included_resources, cc.extra_users, cc.extra_resources, cc.precio_total, cc.start_at AS contrato_start_at, cc.end_at AS contrato_end_at')
                ->join(
                    'cliente_contratos cc',
                    "cc.id = (
                        SELECT cc2.id
                        FROM cliente_contratos cc2
                        WHERE cc2.cliente_id = c.id
                        ORDER BY (cc2.estado = 'ACTIVE') DESC, cc2.start_at DESC, cc2.id DESC
                        LIMIT 1
                    )",
                    'left',
                    false
                );

            if ($db->tableExists('planes')) {
                $builder->select('p.codigo AS plan_codigo, p.nombre AS plan_nombre')
                    ->join('planes p', 'p.id = cc.plan_id', 'left');
            }
        }

        return $builder
            ->orderBy('c.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function index()
    {
        $tenantBaseForModels = $this->scopedTenantBaseForAdminOps();
        if (is_string($tenantBaseForModels) && $tenantBaseForModels !== '') {
            session()->set([
                'tenant_base' => $tenantBaseForModels,
                'tenant_active' => 1,
            ]);
        } else {
            \Config\Services::tenant()->clear();
        }

        $bookingsModel = new BookingsModel();
        $fieldsModel = new FieldsModel();
        $rateModel = new RateModel();
        $customersModel = new CustomersModel();
        $clientesModel = new ClientesModel();
        $rubrosModel = new RubrosModel();
        $timeModel = new TimeModel();
        $usersModel = new UsersModel();
        $offerModel = new OffersModel();
        $localitiesModel = new LocalitiesModel();
        $configModel = new ConfigModel();

        $users = $usersModel->where('active', 1)
        ->where('user !=', 'testuser')
        ->findAll();

        $bookings = [];

        foreach ($bookingsModel->getBookings() as $booking) {
            $reserva = [
                'id' => $booking['id'],
                'cancha' => $fieldsModel->getField($booking['id_field'])['name'],
                'fecha' => date("d/m/Y", strtotime($booking['date'])),
                'horario' => $booking['time_from'] . ' a ' . $booking['time_until'],
                'nombre' => $booking['name'],
                'telefono' => $booking['phone'],
                'creado_por' => $booking['created_by_name'] ?? $booking['created_by_type'] ?? 'N/D',
                'editado_por' => $booking['edited_by_name'] ?? null,
                'editado_en' => $booking['edited_at'] ?? null,
                'pago_total' => $booking['total_payment'] == 1 ? 'Si' : 'No',
                'total_reserva' => $booking['total'],
                'diferencia' => $booking['diference'],
                'monto_reserva' => $booking['payment'],
                'metodo_pago' => $booking['payment_method']
            ];

            array_push($bookings, $reserva);
        }

        $getTime = $timeModel->findAll();
        if ($getTime) {
            $time = $getTime[0];
        } else {
            $time = [
                'from' => 0,
                'until' => 0,
                'from_cut' => 0,
                'until_cut' => 0,
                'nocturnal_time' => 0
            ];
        }

        $openingTime = $timeModel->getOpeningTime();

        $getRate = $rateModel->findAll();
        if ($getRate) {
            $rate = $getRate[0];
        } else {
            $rate = 0;
        }

        $getOfferRate = $offerModel->findAll();
        if ($getOfferRate) {
            $offerRate = $getOfferRate[0];
        } else {
            $offerRate = 0;
        }

        $fields = $fieldsModel->findAll();

        $customers = $customersModel->findAll();
        $clientes = [];
        $rubros = [];
        $planes = [];
        $rubroParametros = [];
        $db = Database::connect('alfareserva');
        if ($db->tableExists('rubros')) {
            $rubros = $rubrosModel->orderBy('descripcion', 'ASC')->findAll();
        }
        if ($db->tableExists('planes')) {
            $planes = $db->table('planes')
                ->orderBy('activo', 'DESC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
        }
        if ($db->tableExists('rubro_parametros')) {
            $rubroParametros = $db->table('rubro_parametros rp')
                ->select('rp.*, r.descripcion AS rubro_descripcion')
                ->join('rubros r', 'r.id = rp.rubro_id', 'left')
                ->orderBy('r.descripcion', 'ASC')
                ->orderBy('rp.key', 'ASC')
                ->get()
                ->getResultArray();
        }
        $allClientes = $this->getClientesRows();
        $sessionEmail = strtolower(trim((string) session()->get('email')));
        $sessionCuenta = trim((string) session()->get('cuenta'));
        $isMasterAdmin = ((int) session()->get('superadmin') === 1)
            && $sessionEmail !== ''
            && $sessionEmail === $this->masterAdminEmail();
        $isClientScoped = !$isMasterAdmin;

        $empresaNombre = '';
        $empresaPrincipal = null;
        $clientes = $allClientes;
        $sucursalesResumen = [];
        $bookingsSummary = ['total' => 0, 'active' => 0, 'annulled' => 0, 'upcoming' => 0];

        if ($isClientScoped) {
            $empresaPrincipal = $this->resolveScopedPrimaryCliente($allClientes, $sessionCuenta, $sessionEmail);

            if (is_array($empresaPrincipal)) {
                $empresaNombre = trim((string) ($empresaPrincipal['razon_social'] ?? ''));
            }

            $clientes = array_values(array_filter($allClientes, static function (array $row) use ($sessionCuenta, $sessionEmail, $empresaNombre): bool {
                $code = trim((string) ($row['codigo'] ?? ''));
                $base = trim((string) ($row['base'] ?? ''));
                $email = strtolower(trim((string) ($row['email'] ?? '')));
                $razon = strtolower(trim((string) ($row['razon_social'] ?? '')));
                $empresaKey = strtolower(trim($empresaNombre));

                if ($sessionCuenta !== '' && ($code === $sessionCuenta || $base === $sessionCuenta)) {
                    return true;
                }
                if ($sessionEmail !== '' && $email === $sessionEmail) {
                    return true;
                }
                if ($empresaKey !== '' && $razon === $empresaKey) {
                    return true;
                }
                return false;
            }));

            if (empty($clientes) && is_array($empresaPrincipal)) {
                $clientes = [$empresaPrincipal];
            }

            $clienteAdminPath = '';
            if (is_array($empresaPrincipal)) {
                $link = trim((string) ($empresaPrincipal['link'] ?? ''));
                if ($link !== '') {
                    $clienteAdminPath = '/' . ltrim($link, '/') . '/admin';
                }
            } elseif (!empty($clientes)) {
                $link = trim((string) ($clientes[0]['link'] ?? ''));
                if ($link !== '') {
                    $clienteAdminPath = '/' . ltrim($link, '/') . '/admin';
                }
            }

            if ($clienteAdminPath !== '') {
                session()->set('admin_panel_path', $clienteAdminPath);
                $currentPath = '/' . ltrim((string) $this->request->getUri()->getPath(), '/');
                $normalizedCurrent = preg_replace('#^/index\.php#i', '', $currentPath);
                $normalizedCurrent = $normalizedCurrent === '' ? '/' : $normalizedCurrent;
                if (stripos($normalizedCurrent, '/abmAdmin') === 0 || stripos($normalizedCurrent, '/abmRubros') === 0) {
                    return redirect()->to($clienteAdminPath);
                }
            }

            $allowedRubros = [];
            foreach ($clientes as $c) {
                $rid = (int) ($c['id_rubro'] ?? 0);
                if ($rid > 0) {
                    $allowedRubros[$rid] = true;
                }
            }
            if (!empty($allowedRubros)) {
                $rubros = array_values(array_filter($rubros, static function (array $r) use ($allowedRubros): bool {
                    return isset($allowedRubros[(int) ($r['id'] ?? 0)]);
                }));
            } else {
                $rubros = [];
            }

            $rubroParametros = [];
            $users = [];
            $nextClienteCodigo = '';

            foreach ($clientes as $clienteRow) {
                $base = trim((string) ($clienteRow['base'] ?? ''));
                $summaryBase = $this->getBookingsSummaryByBase($base);
                $bookingsSummary['total'] += (int) ($summaryBase['total'] ?? 0);
                $bookingsSummary['active'] += (int) ($summaryBase['active'] ?? 0);
                $bookingsSummary['annulled'] += (int) ($summaryBase['annulled'] ?? 0);
                $bookingsSummary['upcoming'] += (int) ($summaryBase['upcoming'] ?? 0);

                $sucursalesResumen[] = [
                    'codigo' => (string) ($clienteRow['codigo'] ?? ''),
                    'razon_social' => (string) ($clienteRow['razon_social'] ?? ''),
                    'base' => $base,
                    'link' => (string) ($clienteRow['link'] ?? ''),
                    'rubro_descripcion' => (string) ($clienteRow['rubro_descripcion'] ?? ''),
                    'bookings_total' => (int) ($summaryBase['total'] ?? 0),
                    'bookings_upcoming' => (int) ($summaryBase['upcoming'] ?? 0),
                ];
            }
        } else {
            session()->set('admin_panel_path', '/abmRubros');
            $nextClienteCodigo = $this->getNextClienteCodigo();
        }

        $clientesHabilitados = array_reduce($clientes, static function (int $carry, array $cliente): int {
            return $carry + (((int) ($cliente['habilitado'] ?? 0) === 1) ? 1 : 0);
        }, 0);
        $clientesDeshabilitados = max(count($clientes) - $clientesHabilitados, 0);
        $superadminStats = [
            'clientes_total' => count($clientes),
            'clientes_habilitados' => $clientesHabilitados,
            'clientes_deshabilitados' => $clientesDeshabilitados,
            'rubros_total' => count($rubros),
            'bookings_total' => (int) ($bookingsSummary['total'] ?? 0),
            'bookings_active' => (int) ($bookingsSummary['active'] ?? 0),
            'bookings_annulled' => (int) ($bookingsSummary['annulled'] ?? 0),
            'bookings_upcoming' => (int) ($bookingsSummary['upcoming'] ?? 0),
            'empresa_nombre' => $empresaNombre,
            'empresa_cuenta' => $sessionCuenta,
            'sucursales' => $sucursalesResumen,
        ];

        $clientProfile = null;
        $clientUsers = [];
        $currentPlan = null;
        $clientAccessUser = null;
        $usersFromTenant = false;
        if ($isClientScoped && is_array($empresaPrincipal)) {
            $clientProfile = $empresaPrincipal;
            $clientCodigo = trim((string) ($empresaPrincipal['codigo'] ?? ''));
            $resolvedLogoUrl = $this->resolveClientLogoUrl($clientCodigo);
            if ($resolvedLogoUrl !== '') {
                $clientProfile['logo_url'] = $resolvedLogoUrl;
                session()->set('tenant_logo_url', $resolvedLogoUrl);
            } else {
                session()->remove('tenant_logo_url');
            }

            $clientUsers = $this->getTenantUsers((string) ($empresaPrincipal['base'] ?? ''));
            $users = $clientUsers;
            $usersFromTenant = true;
            $usersQuota = $this->getClientUsersQuota($empresaPrincipal, (string) ($empresaPrincipal['base'] ?? ''));

            $periodo = strtoupper(trim((string) ($empresaPrincipal['contrato_periodo'] ?? '')));
            $estadoCliente = strtoupper(trim((string) ($empresaPrincipal['estado'] ?? '')));
            $trialEnd = trim((string) ($empresaPrincipal['trial_end'] ?? ''));
            $trialDaysLeft = null;
            if ($estadoCliente === 'TRIAL' && $trialEnd !== '') {
                try {
                    $today = new \DateTimeImmutable(date('Y-m-d'));
                    $end = new \DateTimeImmutable(date('Y-m-d', strtotime($trialEnd)));
                    $diffDays = (int) $today->diff($end)->format('%r%a');
                    $trialDaysLeft = max(0, $diffDays);
                } catch (\Throwable $e) {
                    $trialDaysLeft = null;
                }
            }
            $currentPlan = [
                'plan_id' => (string) ($empresaPrincipal['plan_id'] ?? ''),
                'nombre' => (string) ($empresaPrincipal['plan_nombre'] ?? '-'),
                'periodo' => $periodo,
                'periodo_human' => $periodo === 'YEAR' ? 'Anual' : ($periodo === 'MONTH' ? 'Mensual' : '-'),
                'included_users' => (string) ($empresaPrincipal['included_users'] ?? '-'),
                'included_resources' => (string) ($empresaPrincipal['included_resources'] ?? '-'),
                'estado_cliente' => $estadoCliente,
                'trial_end' => $trialEnd,
                'trial_days_left' => $trialDaysLeft,
                'users_quota_total' => (int) ($usersQuota['total'] ?? 0),
                'users_quota_used' => (int) ($usersQuota['used'] ?? 0),
                'users_quota_remaining' => (int) ($usersQuota['remaining'] ?? 0),
            ];

            $sessionUserId = (int) (session()->get('id_user') ?? 0);
            if ($sessionUserId > 0) {
                $sessionAccessUser = $usersModel->find($sessionUserId);
                if (is_array($sessionAccessUser)) {
                    $clientAccessUser = [
                        'user' => (string) ($sessionAccessUser['user'] ?? ''),
                        'email' => (string) ($sessionAccessUser['email'] ?? ''),
                    ];
                }
            }
        }
        if (!$isClientScoped) {
            session()->remove('tenant_logo_url');
        }
        $localities = $localitiesModel->orderBy('name', 'ASC')->findAll();
        $closureText = $configModel->getValue('texto_cierre');
        if (!is_string($closureText) || trim($closureText) === '') {
            $closureText = "Aviso importante\n\n"
                . "Queremos informarles que el dÃ­a <fecha> las canchas permanecerÃ¡n cerradas.\n"
                . "Pedimos disculpas por las molestias que esto pueda ocasionar.\n\n"
                . "De todas formas, ya pueden reservar normalmente las horas para fechas posteriores.\n"
                . "Muchas gracias por la comprensiÃ³n y por seguir eligiÃ©ndonos.";
        }
        $bookingEmail = $configModel->getValue('email_reservas');

        $useLegacyReservasAdmin = false;
        if ($isClientScoped && is_array($empresaPrincipal)) {
            $rubroPrincipal = strtolower(trim((string) ($empresaPrincipal['rubro_descripcion'] ?? '')));
            $useLegacyReservasAdmin = !in_array($rubroPrincipal, ['comida', 'pedidos'], true);
        }

        $viewName = $useLegacyReservasAdmin ? 'superadmin/index_reservas' : 'superadmin/index';

        return view($viewName, [
            'bookings' => $bookings,
            'rate' => $rate,
            'customers' => $customers,
            'clientes' => $clientes,
            'rubros' => $rubros,
            'planes' => $planes,
            'rubroParametros' => $rubroParametros,
            'superadminStats' => $superadminStats,
            'nextClienteCodigo' => $nextClienteCodigo,
            'time' => $time,
            'openingTime' => $openingTime,
            'fields' => $fields,
            'users' => $users,
            'usersFromTenant' => $usersFromTenant,
            'offerRate' => $offerRate,
            'localities' => $localities,
            'closureText' => $closureText,
            'bookingEmail' => $bookingEmail,
            'isClientScoped' => $isClientScoped,
            'clientProfile' => $clientProfile,
            'clientUsers' => $clientUsers,
            'currentPlan' => $currentPlan,
            'clientPlanOptions' => $planes,
            'clientAccessUser' => $clientAccessUser,
        ]);
    }

    public function saveField()
    {
        $fieldsModel = new FieldsModel();

        $this->request->getVar('iluminacion') ? $iluminacion = true : $iluminacion = false;
        $this->request->getVar('tipoTecho') ? $techada = true : $techada = false;

        $nombre = $this->request->getVar('nombre');
        $medidas = $this->request->getVar('medidas');
        $tipoPiso = $this->request->getVar('tipoPiso');
        $tipoCancha = $this->request->getVar('tipoCancha');
        $valor = $this->request->getVar('valor');
        $valorIluminacion = $this->request->getVar('valorIluminacion');


        $query = [
            'name' => $nombre,
            'sizes' => $medidas,
            'floor_type' => $tipoPiso,
            'field_type' => $tipoCancha,
            'ilumination' => $iluminacion,
            'roofed' => $techada,
            'value' => $valor,
            'ilumination_value' => $valorIluminacion,
            'disabled' => 0,
        ];

        if ($nombre == '' || $medidas == '' || $tipoPiso == '' || $tipoCancha == '' || $valor == '' || $valorIluminacion == '') {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe ingresar todos los datos']);
        }


        try {
            $fieldsModel->insert($query);
        } catch (\Exception $e) {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Error al insertar datos: ' . $e->getMessage()]);
        }

        return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Cancha creada correctamente']);
    }

    public function editField($id)
    {
        $fieldsModel = new FieldsModel();

        $this->request->getVar('iluminacion') ? $iluminacion = true : $iluminacion = false;
        $this->request->getVar('tipoTecho') ? $techada = true : $techada = false;

        $nombre = $this->request->getVar('nombre');
        $medidas = $this->request->getVar('medidas');
        $tipoPiso = $this->request->getVar('tipoPiso');
        $tipoCancha = $this->request->getVar('tipoCancha');
        $valor = $this->request->getVar('valor');
        $valorIluminacion = $this->request->getVar('valorIluminacion');
        $disabled = $this->request->getVar('disabled');


        $query = [
            'name' => $nombre,
            'sizes' => $medidas,
            'floor_type' => $tipoPiso,
            'field_type' => $tipoCancha,
            'ilumination' => $iluminacion,
            'roofed' => $techada,
            'value' => $valor,
            'ilumination_value' => $valorIluminacion,
            'disabled' => $disabled,
        ];

        if ($nombre == '' || $medidas == '' || $tipoPiso == '' || $tipoCancha == '' || $valor == '' || $valorIluminacion == '') {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe ingresar todos los datos']);
        }


        try {
            $fieldsModel->update($id, $query);
        } catch (\Exception $e) {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Error al insertar datos: ' . $e->getMessage()]);
        }

        return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Cancha editada correctamente']);
    }

    public function getActiveBookings()
    {
        try {
            $this->cleanupExpiredPendingBookings();
        } catch (\Throwable $e) {
            // El cleanup no debe bloquear el listado de reservas.
        }

        $data = $this->request->getJSON();
        $from = (string) ($data->fechaDesde ?? '');
        $to = (string) ($data->fechaHasta ?? '');
        $tenantBase = $this->scopedTenantBaseForAdminOps();
        if ($tenantBase !== null) {
            $bookings = $this->getBookingsByRangeFromTenantBase($tenantBase, $from, $to, 0);
            return $this->response->setJSON($this->setResponse(null, null, $bookings, 'Respuesta exitosa'));
        }

        $fieldsModel = new FieldsModel();
        $bookingsModel = new BookingsModel();
        $paymentsModel = new PaymentsModel();

        $getBookings = $bookingsModel->where('date >=', $data->fechaDesde)
            ->where('date <=', $data->fechaHasta)
            ->where('annulled', 0)
            ->orderBy('time_from', 'ASC')
            ->findAll();

        $bookings = [];
        $bookingIds = array_column($getBookings, 'id');
        $paidByBooking = [];

        if (!empty($bookingIds)) {
            $paymentsRows = $paymentsModel
                ->select('id_booking, SUM(amount) as paid_total')
                ->whereIn('id_booking', $bookingIds)
                ->groupBy('id_booking')
                ->findAll();

            foreach ($paymentsRows as $pr) {
                $paidByBooking[(int)$pr['id_booking']] = (float)($pr['paid_total'] ?? 0);
            }
        }

        foreach ($getBookings as $booking) {
            $bookingId = (int)$booking['id'];
            $paymentsSum = $paidByBooking[$bookingId] ?? 0.0;
            $bookingPaid = (float)($booking['payment'] ?? 0);
            $paid = max($paymentsSum, $bookingPaid);
            $total = (float)($booking['total'] ?? 0);
            $difference = $total - $paid;
            if ($difference < 0) {
                $difference = 0;
            }

            $reserva = [
                'id' => $booking['id'],
                'cancha' => $fieldsModel->getField($booking['id_field'])['name'],
                'fecha' => date("d/m/Y", strtotime($booking['date'])),
                'horario' => $booking['time_from'] . ' a ' . $booking['time_until'],
                'nombre' => $booking['name'],
                'telefono' => $booking['phone'],
                'creado_por' => $booking['created_by_name'] ?? $booking['created_by_type'] ?? 'N/D',
                'editado_por' => $booking['edited_by_name'] ?? null,
                'editado_en' => $booking['edited_at'] ?? null,
                'pago_total' => $paid >= $total ? 'Si' : 'No',
                'total_reserva' => $booking['total'],
                'diferencia' => $difference,
                'monto_reserva' => $paid,
                'descripcion' => $booking['description'],
                'metodo_pago' => $booking['payment_method'],
                'anulada'     => $booking['annulled'],
                'mp'        => $booking['mp'],
            ];

            array_push($bookings, $reserva);
        }

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $bookings, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function getAnnulledBookings()
    {
        $data = $this->request->getJSON();
        $from = (string) ($data->fechaDesde ?? '');
        $to = (string) ($data->fechaHasta ?? '');
        $tenantBase = $this->scopedTenantBaseForAdminOps();
        if ($tenantBase !== null) {
            $bookings = $this->getBookingsByRangeFromTenantBase($tenantBase, $from, $to, 1);
            return $this->response->setJSON($this->setResponse(null, null, $bookings, 'Respuesta exitosa'));
        }

        $fieldsModel = new FieldsModel();
        $bookingsModel = new BookingsModel();
        $paymentsModel = new PaymentsModel();

        $getBookings = $bookingsModel->where('date >=', $data->fechaDesde)
            ->where('date <=', $data->fechaHasta)
            ->where('annulled', 1)
            ->orderBy('time_from', 'ASC')
            ->findAll();

        $bookings = [];
        $bookingIds = array_column($getBookings, 'id');
        $paidByBooking = [];

        if (!empty($bookingIds)) {
            $paymentsRows = $paymentsModel
                ->select('id_booking, SUM(amount) as paid_total')
                ->whereIn('id_booking', $bookingIds)
                ->groupBy('id_booking')
                ->findAll();

            foreach ($paymentsRows as $pr) {
                $paidByBooking[(int)$pr['id_booking']] = (float)($pr['paid_total'] ?? 0);
            }
        }

        foreach ($getBookings as $booking) {
            $bookingId = (int)$booking['id'];
            $paymentsSum = $paidByBooking[$bookingId] ?? 0.0;
            $bookingPaid = (float)($booking['payment'] ?? 0);
            $paid = max($paymentsSum, $bookingPaid);
            $total = (float)($booking['total'] ?? 0);
            $difference = $total - $paid;
            if ($difference < 0) {
                $difference = 0;
            }

            $reserva = [
                'id' => $booking['id'],
                'cancha' => $fieldsModel->getField($booking['id_field'])['name'],
                'fecha' => date("d/m/Y", strtotime($booking['date'])),
                'horario' => $booking['time_from'] . ' a ' . $booking['time_until'],
                'nombre' => $booking['name'],
                'telefono' => $booking['phone'],
                'creado_por' => $booking['created_by_name'] ?? $booking['created_by_type'] ?? 'N/D',
                'editado_por' => $booking['edited_by_name'] ?? null,
                'editado_en' => $booking['edited_at'] ?? null,
                'pago_total' => $paid >= $total ? 'Si' : 'No',
                'total_reserva' => $booking['total'],
                'diferencia' => $difference,
                'monto_reserva' => $paid,
                'descripcion' => $booking['description'],
                'metodo_pago' => $booking['payment_method'],
                'anulada'     => $booking['annulled'],
            ];

            array_push($bookings, $reserva);
        }

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $bookings, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function checkCancelReservations()
    {
        $data = $this->request->getJSON();
        $date = $data->fecha ?? null;
        $field = $data->cancha ?? 'all';

        if (!$date) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe ingresar una fecha.'));
        }

        $bookingsModel = new BookingsModel();
        $fieldsModel = new FieldsModel();

        $query = $bookingsModel->where('date', $date)->where('annulled', 0);
        if ($field !== 'all') {
            $query->where('id_field', $field);
        }

        $bookings = $query->findAll();

        $result = [];
        foreach ($bookings as $booking) {
            $fieldName = $fieldsModel->getField($booking['id_field'])['name'] ?? 'N/D';
            $result[] = [
                'nombre' => $booking['name'],
                'telefono' => $booking['phone'],
                'cancha' => $fieldName,
                'horario' => $booking['time_from'] . ' a ' . $booking['time_until'],
            ];
        }

        $fieldLabel = 'Todas';
        if ($field !== 'all') {
            $fieldLabel = $fieldsModel->getField($field)['name'] ?? 'N/D';
        }

        $payload = [
            'fecha' => $date,
            'canchaLabel' => $fieldLabel,
            'bookings' => $result,
        ];

        return $this->response->setJSON($this->setResponse(null, null, $payload, 'Respuesta exitosa'));
    }

    public function saveCancelReservations()
    {
        $data = $this->request->getJSON();
        $date = $data->fecha ?? null;
        $field = $data->cancha ?? 'all';

        if (!$date) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe ingresar una fecha.'));
        }

        $today = date('Y-m-d');
        if ($date < $today) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'No se pueden informar cierres con fecha anterior a hoy.'));
        }

        $fieldsModel = new FieldsModel();
        $cancelModel = new CancelReservationsModel();

        $sameDateRows = $cancelModel->where('cancel_date', $date)->findAll();
        $hasAllClosure = false;
        $hasSameFieldClosure = false;
        foreach ($sameDateRows as $row) {
            if (empty($row['field_id'])) {
                $hasAllClosure = true;
            }
            if ($field !== 'all' && (int)($row['field_id'] ?? 0) === (int)$field) {
                $hasSameFieldClosure = true;
            }
        }

        if ($field === 'all' && !empty($sameDateRows)) {
            return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existen cierres para esa fecha. Solo el primer registro puede editarse a "Todas".'));
        }
        if ($field !== 'all' && $hasAllClosure) {
            return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existe un cierre para Todas las canchas en esa fecha.'));
        }
        if ($field !== 'all' && $hasSameFieldClosure) {
            return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existe un cierre para esa cancha en esa fecha.'));
        }

        $fieldLabel = 'Todas';
        $fieldId = null;
        if ($field !== 'all') {
            $fieldLabel = $fieldsModel->getField($field)['name'] ?? 'N/D';
            $fieldId = $field;
        }

        $userName = session()->get('name') ?? session()->get('user') ?? 'N/D';

        $payload = [
            'cancel_date' => $date,
            'field_id' => $fieldId,
            'field_label' => $fieldLabel,
            'user_name' => $userName,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $cancelModel->insert($payload);
            return $this->response->setJSON($this->setResponse(null, null, null, 'CancelaciÃ³n registrada.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function getCancelReservations()
    {
        $data = $this->request->getJSON();
        $date = $data->fecha ?? null;
        $dateFrom = $data->fechaDesde ?? null;
        $dateTo = $data->fechaHasta ?? null;
        $field = $data->cancha ?? 'all';

        $cancelModel = new CancelReservationsModel();
        $query = $cancelModel;
        if ($date) {
            $query = $query->where('cancel_date', $date);
        } elseif ($dateFrom || $dateTo) {
            if ($dateFrom) {
                $query = $query->where('cancel_date >=', $dateFrom);
            }
            if ($dateTo) {
                $query = $query->where('cancel_date <=', $dateTo);
            }
        } else {
            $query = $query->where('cancel_date >=', date('Y-m-d'));
        }
        if ($field !== 'all') {
            $query = $query->where('field_id', (int)$field);
        }
        $rows = $query
            ->orderBy('cancel_date', 'DESC')
            ->orderBy('field_label', 'ASC')
            ->findAll();

        return $this->response->setJSON($this->setResponse(null, null, $rows, 'Respuesta exitosa'));
    }

    public function updateCancelReservation()
    {
        $data = $this->request->getJSON();
        $id = $data->id ?? null;
        $date = $data->fecha ?? null;
        $field = $data->cancha ?? 'all';

        if (!$id) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'ID invÃ¡lido.'));
        }
        if (!$date) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe ingresar una fecha.'));
        }

        $today = date('Y-m-d');
        if ($date < $today) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'No se pueden editar cierres con fecha anterior a hoy.'));
        }

        $fieldsModel = new FieldsModel();
        $cancelModel = new CancelReservationsModel();
        $row = $cancelModel->find($id);
        if (!$row) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'Cierre no encontrado.'));
        }

        $sameDateRows = $cancelModel->where('cancel_date', $date)->findAll();
        $firstId = null;
        $hasAllOther = false;
        $hasSameFieldOther = false;
        foreach ($sameDateRows as $r) {
            $rowId = (int)$r['id'];
            if ($firstId === null || $rowId < $firstId) {
                $firstId = $rowId;
            }
            if ($rowId === (int)$id) {
                continue;
            }
            if (empty($r['field_id'])) {
                $hasAllOther = true;
            }
            if ($field !== 'all' && (int)($r['field_id'] ?? 0) === (int)$field) {
                $hasSameFieldOther = true;
            }
        }

        if ($field === 'all') {
            if ($firstId !== null && (int)$id !== (int)$firstId) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'Solo el primer cierre de la fecha puede cambiarse a "Todas".'));
            }
        } else {
            if ($hasAllOther) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existe un cierre para Todas las canchas en esa fecha.'));
            }
            if ($hasSameFieldOther) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existe un cierre para esa cancha en esa fecha.'));
            }
        }

        $fieldLabel = 'Todas';
        $fieldId = null;
        if ($field !== 'all') {
            $fieldLabel = $fieldsModel->getField($field)['name'] ?? 'N/D';
            $fieldId = $field;
        }

        $userName = session()->get('name') ?? session()->get('user') ?? 'N/D';
        $payload = [
            'cancel_date' => $date,
            'field_id' => $fieldId,
            'field_label' => $fieldLabel,
            'user_name' => $userName,
        ];

        try {
            $cancelModel->update($id, $payload);
            if ($field === 'all') {
                $cancelModel->where('cancel_date', $date)
                    ->where('id !=', $id)
                    ->delete();
            }
            return $this->response->setJSON($this->setResponse(null, null, null, 'Cierre actualizado.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function deleteCancelReservation()
    {
        $data = $this->request->getJSON();
        $id = $data->id ?? null;

        if (!$id) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'ID invÃ¡lido.'));
        }

        $cancelModel = new CancelReservationsModel();
        try {
            $row = $cancelModel->find($id);
            if (!$row) {
                return $this->response->setJSON($this->setResponse(404, true, null, 'Cierre no encontrado.'));
            }

            $today = date('Y-m-d');
            if (!empty($row['cancel_date']) && $row['cancel_date'] < $today) {
                return $this->response->setJSON($this->setResponse(403, true, null, 'No se pueden editar o eliminar cierres con fecha anterior a hoy.'));
            }

            $cancelModel->delete($id);
            return $this->response->setJSON($this->setResponse(null, null, null, 'Cierre eliminado.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    private function ensureClienteEstadoConfigTable(): void
    {
        $db = Database::connect('alfareserva');
        $db->query(
            "CREATE TABLE IF NOT EXISTS `cliente_configuracion` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cliente_id` INT(11) UNSIGNED NOT NULL,
                `clave` VARCHAR(100) NOT NULL,
                `valor` TEXT NULL,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_cliente_configuracion_cliente_clave` (`cliente_id`, `clave`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function getClienteEstadoConfigDefaults(): array
    {
        return [
            'trial_days' => 15,
            'grace_days' => 5,
            'read_only_days' => 10,
            'msg_trial' => 'Periodo de prueba activo. Te quedan <dias_restantes> dia(s). Vence el <fecha_fin>.',
            'msg_grace' => 'Estas en periodo de gracia. Te quedan <dias_restantes> dia(s) para regularizar el plan.',
            'msg_read_only' => 'Modo solo lectura activo. Te quedan <dias_restantes> dia(s) antes de la suspension.',
            'msg_suspended' => 'Tu cuenta esta suspendida por falta de pago. Contacta al administrador para reactivarla.',
        ];
    }

    public function getClienteEstadoConfigAjax()
    {
        $data = $this->request->getJSON();
        $clienteCodigo = trim((string) ($data->clienteCodigo ?? ''));
        if ($clienteCodigo === '' || preg_match('/^[0-9]{9}$/', $clienteCodigo) !== 1) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Codigo de cliente invalido.'));
        }

        $db = Database::connect('alfareserva');
        if (!$db->tableExists('clientes')) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'La tabla clientes no existe.'));
        }
        $cliente = $db->table('clientes')->select('id, codigo')->where('codigo', $clienteCodigo)->get()->getRowArray();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'Cliente no encontrado.'));
        }
        $clienteId = (int) ($cliente['id'] ?? 0);

        $defaults = $this->getClienteEstadoConfigDefaults();
        try {
            $this->ensureClienteEstadoConfigTable();
            $rows = $db->table('cliente_configuracion')
                ->select('clave, valor')
                ->where('cliente_id', $clienteId)
                ->whereIn('clave', array_keys($defaults))
                ->get()
                ->getResultArray();

            $config = $defaults;
            foreach ($rows as $row) {
                $k = (string) ($row['clave'] ?? '');
                if ($k === '' || !array_key_exists($k, $config)) {
                    continue;
                }
                $config[$k] = in_array($k, ['trial_days', 'grace_days', 'read_only_days'], true)
                    ? (int) ($row['valor'] ?? $defaults[$k])
                    : (string) ($row['valor'] ?? $defaults[$k]);
            }

            return $this->response->setJSON($this->setResponse(null, false, [
                'clienteCodigo' => (string) ($cliente['codigo'] ?? $clienteCodigo),
                'config' => $config,
            ], 'Configuracion obtenida.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function saveClienteEstadoConfigAjax()
    {
        $data = $this->request->getJSON();
        $clienteCodigo = trim((string) ($data->clienteCodigo ?? ''));
        if ($clienteCodigo === '' || preg_match('/^[0-9]{9}$/', $clienteCodigo) !== 1) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Codigo de cliente invalido.'));
        }

        $db = Database::connect('alfareserva');
        if (!$db->tableExists('clientes')) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'La tabla clientes no existe.'));
        }
        $cliente = $db->table('clientes')->select('id, codigo')->where('codigo', $clienteCodigo)->get()->getRowArray();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'Cliente no encontrado.'));
        }
        $clienteId = (int) ($cliente['id'] ?? 0);

        $defaults = $this->getClienteEstadoConfigDefaults();
        $trialDays = max(1, min(365, (int) ($data->trial_days ?? $defaults['trial_days'])));
        $graceDays = max(0, min(60, (int) ($data->grace_days ?? $defaults['grace_days'])));
        $readOnlyDays = max(0, min(60, (int) ($data->read_only_days ?? $defaults['read_only_days'])));
        $msgTrial = substr(trim((string) ($data->msg_trial ?? $defaults['msg_trial'])), 0, 1500);
        $msgGrace = substr(trim((string) ($data->msg_grace ?? $defaults['msg_grace'])), 0, 1500);
        $msgReadOnly = substr(trim((string) ($data->msg_read_only ?? $defaults['msg_read_only'])), 0, 1500);
        $msgSuspended = substr(trim((string) ($data->msg_suspended ?? $defaults['msg_suspended'])), 0, 1500);

        $payload = [
            'trial_days' => (string) $trialDays,
            'grace_days' => (string) $graceDays,
            'read_only_days' => (string) $readOnlyDays,
            'msg_trial' => $msgTrial !== '' ? $msgTrial : $defaults['msg_trial'],
            'msg_grace' => $msgGrace !== '' ? $msgGrace : $defaults['msg_grace'],
            'msg_read_only' => $msgReadOnly !== '' ? $msgReadOnly : $defaults['msg_read_only'],
            'msg_suspended' => $msgSuspended !== '' ? $msgSuspended : $defaults['msg_suspended'],
        ];

        try {
            $this->ensureClienteEstadoConfigTable();
            foreach ($payload as $clave => $valor) {
                $existing = $db->table('cliente_configuracion')
                    ->select('id')
                    ->where('cliente_id', $clienteId)
                    ->where('clave', $clave)
                    ->get()
                    ->getRowArray();

                if ($existing) {
                    $db->table('cliente_configuracion')
                        ->where('id', (int) $existing['id'])
                        ->update(['valor' => $valor]);
                } else {
                    $db->table('cliente_configuracion')->insert([
                        'cliente_id' => $clienteId,
                        'clave' => $clave,
                        'valor' => $valor,
                    ]);
                }
            }

            return $this->response->setJSON($this->setResponse(null, false, [
                'clienteCodigo' => (string) ($cliente['codigo'] ?? $clienteCodigo),
                'config' => $payload,
            ], 'Configuracion guardada.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function saveConfigGeneral()
    {
        $data = $this->request->getJSON();

        $configModel = new ConfigModel();

        try {
            if ($data && property_exists($data, 'textoCierre')) {
                $textoCierre = (string) ($data->textoCierre ?? '');
                $existingText = $configModel->where('clave', 'texto_cierre')->first();
                if ($existingText) {
                    $configModel->update($existingText['id'], ['valor' => $textoCierre]);
                } else {
                    $configModel->insert(['clave' => 'texto_cierre', 'valor' => $textoCierre]);
                }
            }

            if ($data && property_exists($data, 'emailReservas')) {
                $emailReservas = (string) ($data->emailReservas ?? '');
                $existingEmail = $configModel->where('clave', 'email_reservas')->first();
                if ($existingEmail) {
                    $configModel->update($existingEmail['id'], ['valor' => $emailReservas]);
                } else {
                    $configModel->insert(['clave' => 'email_reservas', 'valor' => $emailReservas]);
                }
            }

            return $this->response->setJSON($this->setResponse(null, null, null, 'ConfiguraciÃ³n guardada.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function configMpView()
    {
        return view('mercadoPago/config', ['errors' => []]);
    }

    public function configMp()
    {
        $mpKeysModel = new MercadoPagoKeysModel();

        $publicKey = $this->request->getVar('publicKeyMp');
        $accessToken = $this->request->getVar('accesTokenMp');

        $query = [
            'public_key'   => $publicKey,
            'access_token' => $accessToken,
        ];

        try {
            $existing = $mpKeysModel->first();
            if ($existing) {
                $mpKeysModel->update($existing['id'], $query);
            } else {
                $mpKeysModel->insert($query);
            }
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Datos insertados con Ã©xito: ']);
        } catch (\Exception $e) {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Error al insertar datos: ' . $e->getMessage()]);
        }
    }

    private function masterAdminEmail(): string
    {
        return strtolower(trim((string) env('MASTER_ADMIN_EMAIL', 'marcoslromero23@gmail.com')));
    }

    private function canManageUsers(): bool
    {
        return (bool) session()->get('logueado');
    }

    private function isValidPasswordComplexity(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password) === 1;
    }

    private function resolveCuentaForNewUser(): string
    {
        if ((int) session()->get('superadmin') === 1) {
            return 'Alfa';
        }

        $sessionCuenta = trim((string) session()->get('cuenta'));
        if ($sessionCuenta !== '') {
            return $sessionCuenta;
        }

        $sessionUser = trim((string) session()->get('user'));
        return $sessionUser !== '' ? $sessionUser : 'Alfa';
    }

    private function getActiveUsersList(): array
    {
        $usersModel = new UsersModel();
        $query = $usersModel
            ->where('active', 1)
            ->where('user !=', 'testuser')
            ->orderBy('id', 'DESC');

        $sessionEmail = strtolower(trim((string) session()->get('email')));
        $isMaster = ((int) session()->get('superadmin') === 1) && $sessionEmail === $this->masterAdminEmail();
        if (!$isMaster) {
            $sessionCuenta = trim((string) session()->get('cuenta'));
            if ($sessionCuenta !== '') {
                $query->where('cuenta', $sessionCuenta);
            }
        }

        return $query->findAll();
    }

    public function saveUserAjax()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'Debe iniciar sesion para crear usuarios.'));
        }

        $usersModel = new UsersModel();
        $usuario = trim((string) $this->request->getVar('user'));
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $cuenta = $this->resolveCuentaForNewUser();
        $password = (string) $this->request->getVar('password');
        $repeatPassword = (string) $this->request->getVar('repeat_password');

        if ($password !== $repeatPassword) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Las contrasenas no coinciden.'));
        }

        if ($usuario === '' || $email === '' || $password === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe completar usuario, email y contrasena.'));
        }
        if (!$this->isValidPasswordComplexity($password)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'El email ingresado no es valido.'));
        }

        $exists = $usersModel
            ->groupStart()
            ->where('email', $email)
            ->orWhere('user', $usuario)
            ->groupEnd()
            ->first();

        if ($exists) {
            return $this->response->setJSON($this->setResponse(409, true, null, 'El usuario o email ya existe.'));
        }

        try {
            $usersModel->insert([
                'user' => $usuario,
                'email' => $email,
                'cuenta' => $cuenta,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'superadmin' => $email === $this->masterAdminEmail() ? 1 : 0,
                'name' => $usuario,
                'active' => 1,
            ]);

            return $this->response->setJSON(
                $this->setResponse(null, false, ['users' => $this->getActiveUsersList()], 'Usuario creado correctamente.')
            );
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function deleteUser($id)
    {
        $usersModel = new UsersModel();
        try {
            $usersModel->update($id, ['active' => 0]);
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Usuario eliminado con Ã©xito: ']);
        } catch (\Exception $e) {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Error al eliminar usuario: ' . $e->getMessage()]);
        }
    }
    private function resolveClienteCuenta(string $codigo, string $base): string
    {
        $codigo = trim($codigo);
        if ($codigo !== '') {
            return $codigo;
        }

        $base = trim($base);
        if ($base !== '') {
            return $base;
        }

        return 'Alfa';
    }

    private function nextAvailableUsername(UsersModel $usersModel, string $seed): string
    {
        $base = $this->normalizeTenantKey($seed);
        if ($base === '') {
            $base = 'usuario';
        }
        $base = substr($base, 0, 40);

        $candidate = $base;
        $counter = 1;
        while ($usersModel->where('user', $candidate)->first()) {
            $suffix = (string) $counter;
            $candidate = substr($base, 0, max(1, 40 - strlen($suffix))) . $suffix;
            $counter++;
            if ($counter > 9999) {
                throw new \RuntimeException('No se pudo generar un nombre de usuario unico.');
            }
        }

        return $candidate;
    }

    private function upsertClienteAccessUser(
        string $email,
        ?string $plainPassword,
        string $displayName,
        string $cuenta,
        ?string $previousEmail = null
    ): array {
        $usersModel = new UsersModel();
        $existing = $usersModel->where('email', $email)->first();
        if (!$existing && $previousEmail && strtolower(trim($previousEmail)) !== strtolower(trim($email))) {
            $existing = $usersModel->where('email', strtolower(trim($previousEmail)))->first();
        }

        $plainPassword = (string) ($plainPassword ?? '');
        $shouldUpdatePassword = trim($plainPassword) !== '';
        $hash = $shouldUpdatePassword ? password_hash($plainPassword, PASSWORD_DEFAULT) : null;
        $name = trim($displayName) !== '' ? trim($displayName) : '';

        if ($existing) {
            $payload = [
                'email' => $email,
                'cuenta' => $cuenta,
                'active' => 1,
                'superadmin' => 0,
            ];
            if ($shouldUpdatePassword && $hash !== null) {
                $payload['password'] = $hash;
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }

            $usersModel->update((int) $existing['id'], $payload);
            $updated = $usersModel->find((int) $existing['id']);
            return is_array($updated) ? $updated : array_merge($existing, $payload);
        }

        $localPart = explode('@', $email)[0] ?? 'usuario';
        $username = $this->nextAvailableUsername($usersModel, $localPart);
        if (!$shouldUpdatePassword || $hash === null) {
            throw new \RuntimeException('Debe indicar una contrasena para crear el usuario del cliente.');
        }
        $payload = [
            'user' => $username,
            'email' => $email,
            'password' => $hash,
            'cuenta' => $cuenta,
            'superadmin' => 0,
            'name' => $name !== '' ? $name : $username,
            'active' => 1,
        ];

        $usersModel->insert($payload);
        $id = (int) $usersModel->getInsertID();
        $inserted = $usersModel->find($id);
        return is_array($inserted) ? $inserted : $payload;
    }

    private function upsertTenantUser(string $base, array $sourceUser, string $fallbackEmail = ''): void
    {
        $email = trim((string) ($sourceUser['email'] ?? $fallbackEmail));
        $user = trim((string) ($sourceUser['user'] ?? ''));
        $password = (string) ($sourceUser['password'] ?? '');
        $name = trim((string) ($sourceUser['name'] ?? $user));

        if ($email === '' || $user === '' || $password === '') {
            throw new \RuntimeException('No se pudo sincronizar el usuario del cliente.');
        }

        $db = Database::connect('alfareserva');
        $table = "`{$base}`.`user`";
        $existing = $db->query(
            "SELECT id FROM {$table} WHERE email = ? OR user = ? LIMIT 1",
            [$email, $user]
        )->getRowArray();

        if ($existing) {
            $db->query(
                "UPDATE {$table} SET `user` = ?, `email` = ?, `password` = ?, `name` = ?, `active` = 1 WHERE id = ?",
                [$user, $email, $password, $name, (int) $existing['id']]
            );
            return;
        }

        $db->query(
            "INSERT INTO {$table} (`user`, `email`, `password`, `name`, `active`) VALUES (?, ?, ?, ?, 1)",
            [$user, $email, $password, $name]
        );
    }

    private function upsertClienteContrato(int $clienteId, int $planId, string $periodo, int $includedUsers, int $includedResources): void
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('cliente_contratos') || !$db->tableExists('planes')) {
            return;
        }
        if ($clienteId <= 0 || $planId <= 0) {
            return;
        }

        $periodo = strtoupper(trim($periodo)) === 'YEAR' ? 'YEAR' : 'MONTH';
        $plan = $db->table('planes')->where('id', $planId)->get()->getRowArray();
        if (!$plan) {
            throw new \RuntimeException('El plan seleccionado no existe.');
        }

        $price = $periodo === 'YEAR'
            ? (float) ($plan['price_year'] ?? 0)
            : (float) ($plan['price_month'] ?? 0);

        $payload = [
            'cliente_id' => $clienteId,
            'plan_id' => $planId,
            'periodo' => $periodo,
            'estado' => 'ACTIVE',
            'included_users' => max(0, $includedUsers),
            'included_resources' => max(0, $includedResources),
            'extra_users' => 0,
            'extra_resources' => 0,
            'precio_total' => $price,
            'start_at' => date('Y-m-d'),
            'end_at' => $periodo === 'YEAR' ? date('Y-m-d', strtotime('+1 year')) : date('Y-m-d', strtotime('+1 month')),
        ];

        $existing = $db->table('cliente_contratos')
            ->where('cliente_id', $clienteId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('cliente_contratos')->where('id', (int) $existing['id'])->update($payload);
            return;
        }

        $db->table('cliente_contratos')->insert($payload);
    }

    public function saveCliente()
    {
        $clientesModel = new ClientesModel();
        $rubrosModel = new RubrosModel();

        $codigo = $this->getNextClienteCodigo();
        $razonSocial = trim((string) $this->request->getVar('razon_social'));
        $base = $this->normalizeTenantKey($razonSocial);
        $linkPathInput = trim((string) $this->request->getVar('link_path'));
        $idRubro = (int) $this->request->getVar('id_rubro');
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $planId = (int) ($this->request->getVar('plan_id') ?? 0);
        $contratoPeriodo = strtoupper(trim((string) ($this->request->getVar('contrato_periodo') ?? 'MONTH')));
        $includedUsers = (int) ($this->request->getVar('included_users') ?? 1);
        $includedResources = (int) ($this->request->getVar('included_resources') ?? 2);
        $userPassword = (string) $this->request->getVar('user_password');
        $userPasswordConfirm = (string) $this->request->getVar('user_password_confirm');
        $nombreApellido = trim((string) $this->request->getVar('nombre_apellido'));
        $telefono = trim((string) $this->request->getVar('telefono'));
        $dni = trim((string) $this->request->getVar('dni'));
        $localidad = trim((string) $this->request->getVar('localidad'));
        $estado = strtoupper(trim((string) $this->request->getVar('estado')));
        $estadosValidos = ['TRIAL', 'ACTIVE', 'GRACE', 'READ_ONLY', 'SUSPENDED'];
        if (!in_array($estado, $estadosValidos, true)) {
            $estado = 'TRIAL';
        }

        if ($razonSocial === '' || $base === '' || $idRubro <= 0 || $email === '') {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe completar todos los datos del cliente.']);
        }
        if ($this->databaseExists($base) || $this->clienteBaseExists($base)) {
            return redirect()->to('/abmAdmin')->with('msg', [
                'type' => 'danger',
                'body' => 'Ya existe una base con ese nombre. Ingrese otra.'
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'El email ingresado no es valido.']);
        }
        $usersModel = new UsersModel();
        if ($usersModel->where('email', $email)->first()) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'El email ya existe en AlfaReserva. Ingrese otro.']);
        }
        if ($userPassword === '' || $userPasswordConfirm === '') {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe ingresar y repetir la contrasena del cliente.']);
        }
        if ($userPassword !== $userPasswordConfirm) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Las contrasenas no coinciden.']);
        }
        if (!$this->isValidPasswordComplexity($userPassword)) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.']);
        }

        $rubro = $rubrosModel->find($idRubro);
        if (!$rubro) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'El rubro seleccionado no existe.']);
        }

        $cuentaCliente = $this->resolveClienteCuenta($codigo, $base);
        $displayName = $nombreApellido !== '' ? $nombreApellido : $razonSocial;
        $sourceUser = $this->upsertClienteAccessUser($email, $userPassword, $displayName, $cuentaCliente);

        $databaseCreated = false;
        try {
            $this->createDatabase($base);
            $databaseCreated = true;
            $this->provisionClienteDatabase($base, (string) ($rubro['descripcion'] ?? ''));
            $this->upsertTenantUser($base, $sourceUser, $email);

            $linkSeed = $linkPathInput !== '' ? ltrim($linkPathInput, '/') : $base;
            $linkSlug = $this->normalizeTenantKey($linkSeed);
            if ($linkSlug === '') {
                throw new \RuntimeException('No se pudo generar un link valido para el cliente.');
            }
            $link = $this->buildClienteLink($linkSlug);
            if ($this->clienteLinkExists($link)) {
                throw new \RuntimeException('El link ingresado ya existe.');
            }

            $clientesModel->insert([
                'codigo' => $codigo,
                'NombreApellido' => $nombreApellido !== '' ? $nombreApellido : null,
                'razon_social' => $razonSocial,
                'base' => $base,
                'id_rubro' => $idRubro,
                'email' => $email,
                'telefono' => $telefono !== '' ? $telefono : null,
                'dni' => $dni !== '' ? $dni : null,
                'localidad' => $localidad !== '' ? $localidad : null,
                'habilitado' => 1,
                'estado' => $estado,
                'link' => $link,
            ]);
            $clienteId = (int) $clientesModel->getInsertID();
            $this->upsertClienteContrato($clienteId, $planId, $contratoPeriodo, $includedUsers, $includedResources);
        } catch (\Exception $e) {
            if ($databaseCreated) {
                try {
                    $db = Database::connect('alfareserva');
                    $db->query('DROP DATABASE `' . $base . '`');
                } catch (\Throwable $dropError) {
                    // Si falla el rollback de DB, devolvemos el error original.
                }
            }
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Error al crear cliente: ' . $e->getMessage()]);
        }

        return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Cliente creado correctamente.']);
    }

    public function saveClienteAjax()
    {
        $clientesModel = new ClientesModel();
        $rubrosModel = new RubrosModel();

        $id = (int) ($this->request->getVar('id') ?? 0);
        $razonSocial = trim((string) $this->request->getVar('razon_social'));
        $idRubro = (int) $this->request->getVar('id_rubro');
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $planId = (int) ($this->request->getVar('plan_id') ?? 0);
        $contratoPeriodo = strtoupper(trim((string) ($this->request->getVar('contrato_periodo') ?? 'MONTH')));
        $includedUsers = (int) ($this->request->getVar('included_users') ?? 1);
        $includedResources = (int) ($this->request->getVar('included_resources') ?? 2);
        $userPassword = (string) $this->request->getVar('user_password');
        $userPasswordConfirm = (string) $this->request->getVar('user_password_confirm');
        $linkPathInput = trim((string) $this->request->getVar('link_path'));
        $nombreApellido = trim((string) $this->request->getVar('nombre_apellido'));
        $telefono = trim((string) $this->request->getVar('telefono'));
        $dni = trim((string) $this->request->getVar('dni'));
        $localidad = trim((string) $this->request->getVar('localidad'));
        $estado = strtoupper(trim((string) $this->request->getVar('estado')));
        $estadosValidos = ['TRIAL', 'ACTIVE', 'GRACE', 'READ_ONLY', 'SUSPENDED'];
        if (!in_array($estado, $estadosValidos, true)) {
            $estado = 'TRIAL';
        }

        if ($razonSocial === '' || $idRubro <= 0 || $email === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe completar todos los datos del cliente.'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'El email ingresado no es valido.'));
        }
        if ($id <= 0) {
            if ($userPassword === '' || $userPasswordConfirm === '') {
                return $this->response->setJSON($this->setResponse(400, true, null, 'Debe ingresar y repetir la contrasena del cliente.'));
            }
            if ($userPassword !== $userPasswordConfirm) {
                return $this->response->setJSON($this->setResponse(400, true, null, 'Las contrasenas no coinciden.'));
            }
            if (!$this->isValidPasswordComplexity($userPassword)) {
                return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.'));
            }
        } else {
            $hasOnePassword = ($userPassword !== '' || $userPasswordConfirm !== '');
            if ($hasOnePassword && $userPassword !== $userPasswordConfirm) {
                return $this->response->setJSON($this->setResponse(400, true, null, 'Las contrasenas no coinciden.'));
            }
            if ($hasOnePassword && !$this->isValidPasswordComplexity($userPassword)) {
                return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.'));
            }
        }
        $rubro = $rubrosModel->find($idRubro);
        if (!$rubro) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'El rubro seleccionado no existe.'));
        }

        try {
            if ($id > 0) {
                $cliente = $clientesModel->find($id);
                if (!$cliente) {
                    return $this->response->setJSON($this->setResponse(404, true, null, 'Cliente no encontrado.'));
                }
                $usersModel = new UsersModel();
                $previousEmail = strtolower(trim((string) ($cliente['email'] ?? '')));
                if ($previousEmail !== $email && $usersModel->where('email', $email)->first()) {
                    return $this->response->setJSON($this->setResponse(409, true, null, 'El email ya existe en AlfaReserva. Ingrese otro.'));
                }
                $cuentaCliente = $this->resolveClienteCuenta((string) ($cliente['codigo'] ?? ''), (string) ($cliente['base'] ?? ''));
                $displayName = $nombreApellido !== '' ? $nombreApellido : $razonSocial;
                $sourceUser = $this->upsertClienteAccessUser(
                    $email,
                    $userPassword,
                    $displayName,
                    $cuentaCliente,
                    $previousEmail
                );

                $linkSlug = $this->normalizeTenantKey($linkPathInput !== '' ? ltrim($linkPathInput, '/') : (string) ($cliente['base'] ?? ''));
                if ($linkSlug === '') {
                    return $this->response->setJSON($this->setResponse(400, true, null, 'No se pudo generar un link valido para el cliente.'));
                }
                $link = $this->buildClienteLink($linkSlug);

                $db = Database::connect('alfareserva');
                $dupLink = $db->table('clientes')
                    ->select('id')
                    ->where('link', $link)
                    ->where('id <>', $id)
                    ->get()
                    ->getRowArray();
                if ($dupLink) {
                    return $this->response->setJSON($this->setResponse(409, true, null, 'El link ingresado ya existe.'));
                }

                $clientesModel->update($id, [
                    'NombreApellido' => $nombreApellido !== '' ? $nombreApellido : null,
                    'razon_social' => $razonSocial,
                    'id_rubro' => $idRubro,
                    'email' => $email,
                    'telefono' => $telefono !== '' ? $telefono : null,
                    'dni' => $dni !== '' ? $dni : null,
                    'localidad' => $localidad !== '' ? $localidad : null,
                    'estado' => $estado,
                    'link' => $link,
                ]);

                $baseCliente = trim((string) ($cliente['base'] ?? ''));
                if ($baseCliente !== '') {
                    $this->upsertTenantUser($baseCliente, $sourceUser, (string) ($cliente['email'] ?? ''));
                }
                $this->upsertClienteContrato($id, $planId, $contratoPeriodo, $includedUsers, $includedResources);
            } else {
                $codigo = $this->getNextClienteCodigo();
                $base = $this->normalizeTenantKey($razonSocial);
                if ($base === '') {
                    return $this->response->setJSON($this->setResponse(400, true, null, 'No se pudo generar la base del cliente.'));
                }
                if ($this->databaseExists($base) || $this->clienteBaseExists($base)) {
                    return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existe una base con ese nombre. Ingrese otra.'));
                }
                $usersModel = new UsersModel();
                if ($usersModel->where('email', $email)->first()) {
                    return $this->response->setJSON($this->setResponse(409, true, null, 'El email ya existe en AlfaReserva. Ingrese otro.'));
                }

                $databaseCreated = false;
                try {
                    $this->createDatabase($base);
                    $databaseCreated = true;
                    $this->provisionClienteDatabase($base, (string) ($rubro['descripcion'] ?? ''));
                    $cuentaCliente = $this->resolveClienteCuenta($codigo, $base);
                    $displayName = $nombreApellido !== '' ? $nombreApellido : $razonSocial;
                    $sourceUser = $this->upsertClienteAccessUser($email, $userPassword, $displayName, $cuentaCliente);

                    $this->upsertTenantUser($base, $sourceUser, $email);

                    $linkSeed = $linkPathInput !== '' ? ltrim($linkPathInput, '/') : $base;
                    $linkSlug = $this->normalizeTenantKey($linkSeed);
                    if ($linkSlug === '') {
                        throw new \RuntimeException('No se pudo generar un link valido para el cliente.');
                    }
                    $link = $this->buildClienteLink($linkSlug);
                    if ($this->clienteLinkExists($link)) {
                        throw new \RuntimeException('El link ingresado ya existe.');
                    }

                    $clientesModel->insert([
                        'codigo' => $codigo,
                        'NombreApellido' => $nombreApellido !== '' ? $nombreApellido : null,
                        'razon_social' => $razonSocial,
                        'base' => $base,
                        'id_rubro' => $idRubro,
                        'email' => $email,
                        'telefono' => $telefono !== '' ? $telefono : null,
                        'dni' => $dni !== '' ? $dni : null,
                        'localidad' => $localidad !== '' ? $localidad : null,
                        'habilitado' => 1,
                        'estado' => $estado,
                        'link' => $link,
                    ]);
                    $clienteId = (int) $clientesModel->getInsertID();
                    $this->upsertClienteContrato($clienteId, $planId, $contratoPeriodo, $includedUsers, $includedResources);
                } catch (\Exception $e) {
                    if ($databaseCreated) {
                        try {
                            $db = Database::connect('alfareserva');
                            $db->query('DROP DATABASE `' . $base . '`');
                        } catch (\Throwable $dropError) {
                        }
                    }
                    return $this->response->setJSON($this->setResponse(500, true, null, 'Error al crear cliente: ' . $e->getMessage()));
                }
            }

            $clientes = $this->getClientesRows();
            $nextClienteCodigo = $this->getNextClienteCodigo();
            return $this->response->setJSON($this->setResponse(null, false, [
                'clientes' => $clientes,
                'nextClienteCodigo' => $nextClienteCodigo,
            ], 'Cliente guardado correctamente.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function toggleClienteStatus(int $id)
    {
        $clientesModel = new ClientesModel();
        $cliente = $clientesModel->find($id);

        if (!$cliente) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Cliente no encontrado.']);
        }

        $nuevoEstado = ((int) ($cliente['habilitado'] ?? 0) === 1) ? 0 : 1;
        $clientesModel->update($id, ['habilitado' => $nuevoEstado]);

        $estadoTexto = $nuevoEstado === 1 ? 'habilitado' : 'deshabilitado';
        return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Cliente ' . $estadoTexto . ' correctamente.']);
    }

    public function toggleClienteStatusAjax()
    {
        $id = (int) ($this->request->getVar('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'ID de cliente invalido.'));
        }

        $clientesModel = new ClientesModel();
        $cliente = $clientesModel->find($id);
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'Cliente no encontrado.'));
        }

        $nuevoEstado = ((int) ($cliente['habilitado'] ?? 0) === 1) ? 0 : 1;
        $clientesModel->update($id, ['habilitado' => $nuevoEstado]);

        $clientes = $this->getClientesRows();
        $estadoTexto = $nuevoEstado === 1 ? 'habilitado' : 'deshabilitado';
        return $this->response->setJSON($this->setResponse(null, false, [
            'clientes' => $clientes,
        ], 'Cliente ' . $estadoTexto . ' correctamente.'));
    }

    public function saveClientProfileAjax()
    {
        $cliente = $this->scopedCurrentClienteOrNull();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $id = (int) ($this->request->getVar('id') ?? 0);
        if ($id <= 0 || $id !== (int) ($cliente['id'] ?? 0)) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $razonSocial = trim((string) $this->request->getVar('razon_social'));
        $nombreApellido = trim((string) $this->request->getVar('nombre_apellido'));
        if ($razonSocial === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'La razon social es obligatoria.'));
        }

        $clientesModel = new ClientesModel();
        $clientesModel->update($id, [
            'razon_social' => $razonSocial,
            'NombreApellido' => $nombreApellido !== '' ? $nombreApellido : null,
        ]);

        return $this->response->setJSON($this->setResponse(null, false, null, 'Perfil actualizado correctamente.'));
    }

    public function saveClientLogoAjax()
    {
        $cliente = $this->scopedCurrentClienteOrNull();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $id = (int) ($this->request->getVar('id') ?? 0);
        if ($id <= 0 || $id !== (int) ($cliente['id'] ?? 0)) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $file = $this->request->getFile('logo');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe seleccionar un archivo válido.'));
        }

        $allowed = ['png', 'jpg', 'jpeg', 'webp'];
        $ext = strtolower((string) $file->getExtension());
        if (!in_array($ext, $allowed, true)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Formato de logo no permitido.'));
        }

        $codigo = trim((string) ($cliente['codigo'] ?? ''));
        if ($codigo === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'No se encontró código de cliente.'));
        }

        $targetDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $codigo;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'No se pudo crear carpeta de logo.'));
        }

        foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp'] as $oldLogo) {
            $oldPath = $targetDir . DIRECTORY_SEPARATOR . $oldLogo;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $targetName = 'logo.' . $ext;
        if (!$file->move($targetDir, $targetName, true)) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'No se pudo guardar el logo.'));
        }

        return $this->response->setJSON($this->setResponse(null, false, null, 'Logo actualizado correctamente.'));
    }

    public function saveOwnClientPasswordAjax()
    {
        $cliente = $this->scopedCurrentClienteOrNull();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $currentPassword = (string) ($this->request->getVar('current_password') ?? '');
        $newPassword = (string) ($this->request->getVar('new_password') ?? '');
        $repeatPassword = (string) ($this->request->getVar('repeat_password') ?? '');
        if ($currentPassword === '' || $newPassword === '' || $repeatPassword === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe completar todos los campos de contrasena.'));
        }
        if ($newPassword !== $repeatPassword) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Las contrasenas no coinciden.'));
        }
        if (!$this->isValidPasswordComplexity($newPassword)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.'));
        }

        $userId = (int) (session()->get('id_user') ?? 0);
        if ($userId <= 0) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'Sesion invalida.'));
        }

        $usersModel = new UsersModel();
        $sessionUser = $usersModel->find($userId);
        if (!$sessionUser || (int) ($sessionUser['active'] ?? 0) !== 1) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'Usuario no encontrado.'));
        }
        if (!password_verify($currentPassword, (string) ($sessionUser['password'] ?? ''))) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena actual no es correcta.'));
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $usersModel->update($userId, ['password' => $newHash]);

        $base = trim((string) ($cliente['base'] ?? ''));
        $email = strtolower(trim((string) ($sessionUser['email'] ?? '')));
        $userName = trim((string) ($sessionUser['user'] ?? ''));
        if ($base !== '' && $this->databaseExists($base)) {
            try {
                $db = Database::connect('alfareserva');
                $table = "`{$base}`.`user`";
                $tenantRow = $db->query(
                    "SELECT id FROM {$table} WHERE email = ? OR `user` = ? LIMIT 1",
                    [$email, $userName]
                )->getRowArray();
                if ($tenantRow) {
                    $db->query("UPDATE {$table} SET password = ? WHERE id = ?", [$newHash, (int) $tenantRow['id']]);
                }
            } catch (\Throwable $e) {
            }
        }

        return $this->response->setJSON($this->setResponse(null, false, null, 'Contrasena actualizada correctamente.'));
    }

    public function addClientBaseUserAjax()
    {
        $cliente = $this->scopedCurrentClienteOrNull();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $base = trim((string) ($cliente['base'] ?? ''));
        if ($base === '' || !$this->databaseExists($base)) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'No se encontro la base del cliente.'));
        }

        $user = trim((string) ($this->request->getVar('user') ?? ''));
        $email = strtolower(trim((string) ($this->request->getVar('email') ?? '')));
        $password = (string) ($this->request->getVar('password') ?? '');
        $repeatPassword = (string) ($this->request->getVar('repeat_password') ?? '');

        if ($user === '' || $password === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe completar usuario y contrasena.'));
        }
        if ($password !== $repeatPassword) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Las contrasenas no coinciden.'));
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Email invalido.'));
        }
        if (!$this->isValidPasswordComplexity($password)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.'));
        }

        $db = Database::connect('alfareserva');
        $table = "`{$base}`.`user`";
        try {
            $quota = $this->getClientUsersQuota($cliente, $base);
            if ((int) ($quota['total'] ?? 0) <= 0) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'Tu plan actual no tiene cupo de usuarios. Actualiza el plan para agregar usuarios.'));
            }
            if ((int) ($quota['remaining'] ?? 0) <= 0) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'No te quedan usuarios disponibles en tu plan.'));
            }

            if ($email !== '') {
                $dup = $db->query(
                    "SELECT id FROM {$table} WHERE email = ? OR `user` = ? LIMIT 1",
                    [$email, $user]
                )->getRowArray();
            } else {
                $dup = $db->query(
                    "SELECT id FROM {$table} WHERE `user` = ? LIMIT 1",
                    [$user]
                )->getRowArray();
            }
            if ($dup) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'El usuario o email ya existe en esta base.'));
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->query(
                "INSERT INTO {$table} (`user`, email, password, name, active) VALUES (?, ?, ?, ?, 1)",
                [$user, $email, $hash, $user]
            );

            $tenantUsers = $this->getTenantUsers($base);
            $quotaAfter = $this->getClientUsersQuota($cliente, $base);
            return $this->response->setJSON($this->setResponse(null, false, [
                'users' => $tenantUsers,
                'quota' => $quotaAfter,
            ], 'Usuario agregado en la base del cliente.'));
        } catch (\Throwable $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function saveClientPlanAjax()
    {
        $cliente = $this->scopedCurrentClienteOrNull();
        if (!$cliente) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $id = (int) ($this->request->getVar('id') ?? 0);
        if ($id <= 0 || $id !== (int) ($cliente['id'] ?? 0)) {
            return $this->response->setJSON($this->setResponse(403, true, null, 'No autorizado.'));
        }

        $planId = (int) ($this->request->getVar('plan_id') ?? 0);
        $periodo = strtoupper(trim((string) ($this->request->getVar('periodo') ?? 'MONTH')));
        if ($planId <= 0) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe seleccionar un plan.'));
        }

        $db = Database::connect('alfareserva');
        $plan = $db->table('planes')->where('id', $planId)->where('activo', 1)->get()->getRowArray();
        if (!$plan) {
            return $this->response->setJSON($this->setResponse(404, true, null, 'El plan seleccionado no existe o esta inactivo.'));
        }

        $includedUsers = (int) ($this->request->getVar('included_users') ?? ($plan['included_users'] ?? 1));
        $includedResources = (int) ($this->request->getVar('included_resources') ?? ($plan['included_resources'] ?? 2));
        $this->upsertClienteContrato($id, $planId, $periodo, $includedUsers, $includedResources);

        // Si el cliente estaba en prueba, al asignar plan pasa a activo.
        $clientesModel = new ClientesModel();
        $clienteActual = $clientesModel->find($id);
        if (is_array($clienteActual)) {
            $estadoActual = strtoupper(trim((string) ($clienteActual['estado'] ?? '')));
            if ($estadoActual === '' || $estadoActual === 'TRIAL') {
                $clientesModel->update($id, ['estado' => 'ACTIVE']);
            }
        }

        return $this->response->setJSON($this->setResponse(null, false, [
            'planNombre' => (string) ($plan['nombre'] ?? ''),
            'periodo' => $periodo,
            'includedUsers' => $includedUsers,
            'includedResources' => $includedResources,
        ], 'Plan actualizado correctamente.'));
    }

    public function saveRubro()
    {
        $db = Database::connect('alfareserva');
        $descripcion = trim((string) $this->request->getVar('descripcion'));

        if ($descripcion === '') {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe ingresar una descripcion de rubro.']);
        }

        $existing = $db->query(
            'SELECT id FROM rubros WHERE LOWER(TRIM(descripcion)) = ? LIMIT 1',
            [strtolower($descripcion)]
        )->getRowArray();

        if ($existing) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'warning', 'body' => 'Ese rubro ya existe.']);
        }

        $db->table('rubros')->insert(['descripcion' => $descripcion]);

        return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Rubro creado correctamente.']);
    }

    public function savePlan()
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('planes')) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'La tabla planes no existe.']);
        }

        $codigo = strtoupper(trim((string) $this->request->getVar('codigo')));
        $nombre = trim((string) $this->request->getVar('nombre'));
        $priceMonth = (float) $this->request->getVar('price_month');
        $priceYear = (float) $this->request->getVar('price_year');
        $includedUsers = (int) ($this->request->getVar('included_users') ?? 1);
        $includedResources = (int) ($this->request->getVar('included_resources') ?? 2);
        $maxUsers = (int) ($this->request->getVar('max_users') ?? 50);
        $maxResources = (int) ($this->request->getVar('max_resources') ?? 100);
        $soporteHoras = (int) ($this->request->getVar('soporte_horas') ?? 0);
        $emailPorReserva = (int) ($this->request->getVar('email_por_reserva') ?? 0);

        if ($codigo === '' || $nombre === '') {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe indicar codigo y nombre del plan.']);
        }

        $payload = [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'price_month' => $priceMonth,
            'price_year' => $priceYear,
            'included_users' => max(0, $includedUsers),
            'included_resources' => max(0, $includedResources),
            'max_users' => max(0, $maxUsers),
            'max_resources' => max(0, $maxResources),
            'soporte_horas' => max(0, $soporteHoras),
            'email_por_reserva' => $emailPorReserva === 1 ? 1 : 0,
            'activo' => 1,
        ];

        $existing = $db->table('planes')->where('codigo', $codigo)->get()->getRowArray();
        if ($existing) {
            $db->table('planes')->where('id', $existing['id'])->update($payload);
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Plan actualizado correctamente.']);
        }

        $db->table('planes')->insert($payload);
        return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Plan creado correctamente.']);
    }

    public function savePlanAjax()
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('planes')) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'La tabla planes no existe.'));
        }

        $id = (int) ($this->request->getVar('id') ?? 0);
        $codigo = strtoupper(trim((string) $this->request->getVar('codigo')));
        $nombre = trim((string) $this->request->getVar('nombre'));
        $priceMonth = (float) $this->request->getVar('price_month');
        $priceYear = (float) $this->request->getVar('price_year');
        $includedUsers = (int) ($this->request->getVar('included_users') ?? 1);
        $includedResources = (int) ($this->request->getVar('included_resources') ?? 2);
        $maxUsers = (int) ($this->request->getVar('max_users') ?? 50);
        $maxResources = (int) ($this->request->getVar('max_resources') ?? 100);
        $soporteHoras = (int) ($this->request->getVar('soporte_horas') ?? 0);
        $emailPorReserva = (int) ($this->request->getVar('email_por_reserva') ?? 0);

        if ($codigo === '' || $nombre === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe indicar codigo y nombre del plan.'));
        }

        $payload = [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'price_month' => $priceMonth,
            'price_year' => $priceYear,
            'included_users' => max(0, $includedUsers),
            'included_resources' => max(0, $includedResources),
            'max_users' => max(0, $maxUsers),
            'max_resources' => max(0, $maxResources),
            'soporte_horas' => max(0, $soporteHoras),
            'email_por_reserva' => $emailPorReserva === 1 ? 1 : 0,
            'activo' => 1,
        ];

        try {
            if ($id > 0) {
                $db->table('planes')->where('id', $id)->update($payload);
            } else {
                $existing = $db->table('planes')->where('codigo', $codigo)->get()->getRowArray();
                if ($existing) {
                    $db->table('planes')->where('id', $existing['id'])->update($payload);
                } else {
                    $db->table('planes')->insert($payload);
                }
            }

            $planes = $db->table('planes')
                ->orderBy('activo', 'DESC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON($this->setResponse(null, false, ['planes' => $planes], 'Plan guardado correctamente.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function saveRubroAjax()
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('rubros')) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'La tabla rubros no existe.'));
        }

        $id = (int) ($this->request->getVar('id') ?? 0);
        $descripcion = trim((string) $this->request->getVar('descripcion'));
        if ($descripcion === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe ingresar una descripcion de rubro.'));
        }

        try {
            if ($id > 0) {
                $exists = $db->query(
                    'SELECT id FROM rubros WHERE LOWER(TRIM(descripcion)) = ? AND id <> ? LIMIT 1',
                    [strtolower($descripcion), $id]
                )->getRowArray();
            } else {
                $exists = $db->query(
                    'SELECT id FROM rubros WHERE LOWER(TRIM(descripcion)) = ? LIMIT 1',
                    [strtolower($descripcion)]
                )->getRowArray();
            }
            if ($exists) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'Ese rubro ya existe.'));
            }

            if ($id > 0) {
                $db->table('rubros')->where('id', $id)->update(['descripcion' => $descripcion]);
            } else {
                $db->table('rubros')->insert(['descripcion' => $descripcion]);
            }

            $rubros = $db->table('rubros')->orderBy('descripcion', 'ASC')->get()->getResultArray();
            return $this->response->setJSON($this->setResponse(null, false, ['rubros' => $rubros], 'Rubro guardado correctamente.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function saveRubroParametroAjax()
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('rubro_parametros')) {
            return $this->response->setJSON($this->setResponse(500, true, null, 'La tabla rubro_parametros no existe.'));
        }

        $id = (int) ($this->request->getVar('id') ?? 0);
        $rubroId = (int) $this->request->getVar('rubro_id');
        $key = strtolower(trim((string) $this->request->getVar('key')));
        $label = trim((string) $this->request->getVar('label'));
        $minValue = (int) ($this->request->getVar('min_value') ?? 1);
        $maxValue = (int) ($this->request->getVar('max_value') ?? 999);
        $precioUnidad = (float) ($this->request->getVar('precio_por_unidad') ?? 0);

        if ($rubroId <= 0 || $key === '' || $label === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe completar rubro, key y label del parametro.'));
        }

        try {
            $rubroExists = $db->table('rubros')->where('id', $rubroId)->get()->getRowArray();
            if (!$rubroExists) {
                return $this->response->setJSON($this->setResponse(404, true, null, 'El rubro seleccionado no existe.'));
            }

            $dupQuery = $db->table('rubro_parametros')
                ->select('id')
                ->where('rubro_id', $rubroId)
                ->where('key', $key);
            if ($id > 0) {
                $dupQuery->where('id <>', $id);
            }
            $duplicate = $dupQuery->get()->getRowArray();
            if ($duplicate) {
                return $this->response->setJSON($this->setResponse(409, true, null, 'Ya existe un parametro con esa key para el rubro.'));
            }

            $payload = [
                'rubro_id' => $rubroId,
                'key' => $key,
                'label' => $label,
                'min_value' => $minValue,
                'max_value' => $maxValue,
                'precio_por_unidad' => $precioUnidad,
                'activo' => 1,
            ];

            if ($id > 0) {
                $db->table('rubro_parametros')->where('id', $id)->update($payload);
            } else {
                $db->table('rubro_parametros')->insert($payload);
            }

            $rubroParametros = $db->table('rubro_parametros rp')
                ->select('rp.*, r.descripcion AS rubro_descripcion')
                ->join('rubros r', 'r.id = rp.rubro_id', 'left')
                ->orderBy('r.descripcion', 'ASC')
                ->orderBy('rp.key', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON($this->setResponse(null, false, ['rubroParametros' => $rubroParametros], 'Parametro guardado correctamente.'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function saveRubroParametro()
    {
        $db = Database::connect('alfareserva');
        if (!$db->tableExists('rubro_parametros')) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'La tabla rubro_parametros no existe.']);
        }

        $rubroId = (int) $this->request->getVar('rubro_id');
        $key = strtolower(trim((string) $this->request->getVar('key')));
        $label = trim((string) $this->request->getVar('label'));
        $minValue = (int) ($this->request->getVar('min_value') ?? 1);
        $maxValue = (int) ($this->request->getVar('max_value') ?? 999);
        $precioUnidad = (float) ($this->request->getVar('precio_por_unidad') ?? 0);

        if ($rubroId <= 0 || $key === '' || $label === '') {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe completar rubro, key y label del parametro.']);
        }

        $rubroExists = $db->table('rubros')->where('id', $rubroId)->get()->getRowArray();
        if (!$rubroExists) {
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'El rubro seleccionado no existe.']);
        }

        $payload = [
            'rubro_id' => $rubroId,
            'key' => $key,
            'label' => $label,
            'min_value' => $minValue,
            'max_value' => $maxValue,
            'precio_por_unidad' => $precioUnidad,
            'activo' => 1,
        ];

        $existing = $db->table('rubro_parametros')
            ->where('rubro_id', $rubroId)
            ->where('key', $key)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('rubro_parametros')->where('id', $existing['id'])->update($payload);
            return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Parametro actualizado correctamente.']);
        }

        $db->table('rubro_parametros')->insert($payload);
        return redirect()->to('/abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Parametro creado correctamente.']);
    }

    public function setResponse($code = 200, $error = false, $data = null, $message = '')
    {
        $response = [
            'error' => $error,
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ];

        return $response;
    }
}

