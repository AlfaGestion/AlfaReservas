<?php

namespace App\Libraries;

use Config\Database;
use DateTimeImmutable;

class Tenant
{
    private function parseDate(?string $value): ?DateTimeImmutable
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatDate(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    private function daysRemaining(DateTimeImmutable $now, DateTimeImmutable $end): int
    {
        if ($now > $end) {
            return 0;
        }

        $seconds = $end->getTimestamp() - $now->getTimestamp();
        return (int) ceil($seconds / 86400);
    }

    private function getEstadoConfigDefaults(): array
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

    private function getClienteEstadoConfig(int $clienteId): array
    {
        $defaults = $this->getEstadoConfigDefaults();
        if ($clienteId <= 0) {
            return $defaults;
        }

        $db = Database::connect('alfareserva');
        if (!$db->tableExists('cliente_configuracion')) {
            return $defaults;
        }

        $rows = $db->table('cliente_configuracion')
            ->select('clave, valor')
            ->where('cliente_id', $clienteId)
            ->whereIn('clave', array_keys($defaults))
            ->get()
            ->getResultArray();

        $config = $defaults;
        foreach ($rows as $row) {
            $key = (string) ($row['clave'] ?? '');
            if (!array_key_exists($key, $config)) {
                continue;
            }

            if (in_array($key, ['trial_days', 'grace_days', 'read_only_days'], true)) {
                $config[$key] = (int) ($row['valor'] ?? $defaults[$key]);
            } else {
                $value = trim((string) ($row['valor'] ?? ''));
                $config[$key] = $value !== '' ? $value : $defaults[$key];
            }
        }

        $config['trial_days'] = max(1, min(365, (int) ($config['trial_days'] ?? 15)));
        $config['grace_days'] = max(0, min(60, (int) ($config['grace_days'] ?? 5)));
        $config['read_only_days'] = max(0, min(60, (int) ($config['read_only_days'] ?? 10)));

        return $config;
    }

    private function estadoLabel(string $estado): string
    {
        return match (strtoupper(trim($estado))) {
            'TRIAL' => 'En prueba',
            'ACTIVE' => 'Activo',
            'GRACE' => 'Periodo de gracia',
            'READ_ONLY' => 'Solo lectura',
            'SUSPENDED' => 'Suspendido',
            default => strtoupper(trim($estado)),
        };
    }

    private function periodoLabel(?string $periodo): string
    {
        return match (strtoupper(trim((string) $periodo))) {
            'MONTH' => 'Mensual',
            'YEAR' => 'Anual',
            default => '-',
        };
    }

    private function renderTemplate(string $template, array $vars): string
    {
        return preg_replace_callback('/<([a-z_]+)>/i', static function ($m) use ($vars) {
            $key = strtolower((string) ($m[1] ?? ''));
            return isset($vars[$key]) ? (string) $vars[$key] : '';
        }, $template) ?? $template;
    }

    private function applyEstadoRules(array $cliente): array
    {
        $db = Database::connect('alfareserva');
        $now = new DateTimeImmutable('now');
        $config = $this->getClienteEstadoConfig((int) ($cliente['id'] ?? 0));

        $estado = strtoupper(trim((string) ($cliente['estado'] ?? 'TRIAL')));
        if ($estado === '') {
            $estado = 'TRIAL';
        }

        $updates = [];
        $notice = null;
        $daysRemaining = null;
        $mode = 'full';
        $blockedMessage = null;

        $trialStart = $this->parseDate($cliente['trial_start'] ?? null);
        $createdAt = $this->parseDate($cliente['created_at'] ?? null);
        $trialEnd = $this->parseDate($cliente['trial_end'] ?? null);
        $graceEnd = $this->parseDate($cliente['grace_end'] ?? null);

        if (!$trialStart) {
            $trialStart = $createdAt ?: $now;
            $updates['trial_start'] = $this->formatDate($trialStart);
        }
        if (!$trialEnd) {
            $trialEnd = $trialStart->modify('+' . (int) $config['trial_days'] . ' days');
            $updates['trial_end'] = $this->formatDate($trialEnd);
        }

        if ($estado === 'TRIAL') {
            if ($now > $trialEnd) {
                $estado = 'GRACE';
                $graceEnd = $now->modify('+' . (int) $config['grace_days'] . ' days');
                $updates['estado'] = 'GRACE';
                $updates['grace_end'] = $this->formatDate($graceEnd);
            } else {
                $daysRemaining = $this->daysRemaining($now, $trialEnd);
                $notice = $this->renderTemplate((string) $config['msg_trial'], [
                    'cliente' => (string) ($cliente['razon_social'] ?? ''),
                    'codigo' => (string) ($cliente['codigo'] ?? ''),
                    'estado' => $this->estadoLabel($estado),
                    'plan' => (string) ($cliente['plan_nombre'] ?? '-'),
                    'periodo' => $this->periodoLabel((string) ($cliente['contrato_periodo'] ?? '')),
                    'dias_restantes' => (string) $daysRemaining,
                    'fecha_fin' => $trialEnd->format('Y-m-d'),
                    'fecha_hoy' => $now->format('Y-m-d'),
                ]);
            }
        }

        if ($estado === 'ACTIVE') {
            $contractEstado = strtoupper(trim((string) ($cliente['contrato_estado'] ?? '')));
            $contractEnd = $this->parseDate($cliente['contrato_end_at'] ?? null);
            $contractOk = $contractEstado === 'ACTIVE' && $contractEnd && $now <= $contractEnd;

            if (!$contractOk) {
                $estado = 'GRACE';
                $graceEnd = $now->modify('+' . (int) $config['grace_days'] . ' days');
                $updates['estado'] = 'GRACE';
                $updates['grace_end'] = $this->formatDate($graceEnd);
            }
        }

        if ($estado === 'GRACE') {
            if (!$graceEnd) {
                $graceEnd = $now->modify('+' . (int) $config['grace_days'] . ' days');
                $updates['grace_end'] = $this->formatDate($graceEnd);
            }

            if ($now > $graceEnd) {
                $estado = 'READ_ONLY';
                $updates['estado'] = 'READ_ONLY';
            } else {
                $daysRemaining = $this->daysRemaining($now, $graceEnd);
                $notice = $this->renderTemplate((string) $config['msg_grace'], [
                    'cliente' => (string) ($cliente['razon_social'] ?? ''),
                    'codigo' => (string) ($cliente['codigo'] ?? ''),
                    'estado' => $this->estadoLabel($estado),
                    'plan' => (string) ($cliente['plan_nombre'] ?? '-'),
                    'periodo' => $this->periodoLabel((string) ($cliente['contrato_periodo'] ?? '')),
                    'dias_restantes' => (string) $daysRemaining,
                    'fecha_fin' => $graceEnd->format('Y-m-d'),
                    'fecha_hoy' => $now->format('Y-m-d'),
                ]);
            }
        }

        if ($estado === 'READ_ONLY') {
            if (!$graceEnd) {
                $graceEnd = $now;
                $updates['grace_end'] = $this->formatDate($graceEnd);
            }

            $readOnlyEnd = $graceEnd->modify('+' . (int) $config['read_only_days'] . ' days');
            if ($now > $readOnlyEnd) {
                $estado = 'SUSPENDED';
                $updates['estado'] = 'SUSPENDED';
            } else {
                $mode = 'read_only';
                $daysRemaining = $this->daysRemaining($now, $readOnlyEnd);
                $notice = $this->renderTemplate((string) $config['msg_read_only'], [
                    'cliente' => (string) ($cliente['razon_social'] ?? ''),
                    'codigo' => (string) ($cliente['codigo'] ?? ''),
                    'estado' => $this->estadoLabel($estado),
                    'plan' => (string) ($cliente['plan_nombre'] ?? '-'),
                    'periodo' => $this->periodoLabel((string) ($cliente['contrato_periodo'] ?? '')),
                    'dias_restantes' => (string) $daysRemaining,
                    'fecha_fin' => $readOnlyEnd->format('Y-m-d'),
                    'fecha_hoy' => $now->format('Y-m-d'),
                ]);
            }
        }

        if ($estado === 'SUSPENDED') {
            $mode = 'blocked';
            $blockedMessage = $this->renderTemplate((string) $config['msg_suspended'], [
                'cliente' => (string) ($cliente['razon_social'] ?? ''),
                'codigo' => (string) ($cliente['codigo'] ?? ''),
                'estado' => $this->estadoLabel($estado),
                'plan' => (string) ($cliente['plan_nombre'] ?? '-'),
                'periodo' => $this->periodoLabel((string) ($cliente['contrato_periodo'] ?? '')),
                'dias_restantes' => '0',
                'fecha_fin' => $now->format('Y-m-d'),
                'fecha_hoy' => $now->format('Y-m-d'),
            ]);
        }

        if (!empty($updates) && !empty($cliente['id'])) {
            $db->table('clientes')->where('id', (int) $cliente['id'])->update($updates);
            foreach ($updates as $k => $v) {
                $cliente[$k] = $v;
            }
        }

        $cliente['estado'] = $estado;
        $cliente['tenant_access_mode'] = $mode;
        $cliente['tenant_access_allowed'] = $mode !== 'blocked';
        $cliente['tenant_access_notice'] = $notice;
        $cliente['tenant_days_remaining'] = $daysRemaining;
        $cliente['tenant_access_message'] = $blockedMessage;

        return $cliente;
    }

    private function baseClienteBuilder()
    {
        $dbAlfa = Database::connect('alfareserva');
        $builder = $dbAlfa->table('clientes c')
            ->select('c.id, c.codigo, c.base, c.habilitado, c.razon_social, c.link, c.estado, c.created_at, c.trial_start, c.trial_end, c.paid_through, c.grace_end, r.descripcion AS rubro')
            ->join('rubros r', 'r.id = c.id_rubro', 'left')
            ->where('c.habilitado', 1);

        if ($dbAlfa->tableExists('cliente_contratos')) {
            $builder
                ->select('cc.id AS contrato_id, cc.plan_id, cc.periodo AS contrato_periodo, cc.estado AS contrato_estado, cc.start_at AS contrato_start_at, cc.end_at AS contrato_end_at, cc.precio_total')
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

            if ($dbAlfa->tableExists('planes')) {
                $builder->select('p.nombre AS plan_nombre')
                    ->join('planes p', 'p.id = cc.plan_id', 'left');
            }
        }

        return $builder;
    }

    public function normalizeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return '';
        }

        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($normalized) && $normalized !== '') {
            $value = $normalized;
        }

        $value = preg_replace('/[^a-z0-9_]+/', '_', $value);
        $value = trim((string) $value, '_');

        return $value;
    }

    public function extractSlugFromLink(?string $link): string
    {
        $link = trim((string) $link);
        if ($link === '') {
            return '';
        }

        $path = $link;
        if (preg_match('#^https?://#i', $link) === 1) {
            $parsedPath = parse_url($link, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : '';
        }

        $path = trim($path, '/');
        if ($path === '') {
            return '';
        }

        $segments = explode('/', $path);
        $last = (string) end($segments);

        return $this->normalizeSlug($last);
    }

    public function resolveByCodigo(string $codigo): ?array
    {
        if (preg_match('/^[0-9]{9}$/', $codigo) !== 1) {
            return null;
        }

        $cliente = $this->baseClienteBuilder()
            ->where('c.codigo', $codigo)
            ->get()
            ->getRowArray();

        if (!$cliente) {
            return null;
        }

        return $this->applyEstadoRules($cliente);
    }

    public function resolveBySlug(string $slug): ?array
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || preg_match('/^[a-z0-9_]+$/', $slug) !== 1) {
            return null;
        }

        $dbAlfa = Database::connect('alfareserva');
        $cliente = $this->baseClienteBuilder()
            ->where('c.base', $slug)
            ->get()
            ->getRowArray();

        if ($cliente) {
            return $this->applyEstadoRules($cliente);
        }

        $candidatos = $this->baseClienteBuilder()
            ->where('c.link IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($candidatos as $candidato) {
            if ($this->extractSlugFromLink((string) ($candidato['link'] ?? '')) === $slug) {
                return $this->applyEstadoRules($candidato);
            }
        }

        return null;
    }

    public function activate(array $cliente): void
    {
        session()->set([
            'tenant_id' => (int) ($cliente['id'] ?? 0),
            'tenant_codigo' => (string) ($cliente['codigo'] ?? ''),
            'tenant_base' => (string) ($cliente['base'] ?? ''),
            'tenant_rubro' => (string) ($cliente['rubro'] ?? ''),
            'tenant_estado' => (string) ($cliente['estado'] ?? ''),
            'tenant_access_mode' => (string) ($cliente['tenant_access_mode'] ?? 'full'),
            'tenant_access_notice' => (string) ($cliente['tenant_access_notice'] ?? ''),
            'tenant_access_message' => (string) ($cliente['tenant_access_message'] ?? ''),
            'tenant_days_remaining' => $cliente['tenant_days_remaining'] ?? null,
            'tenant_active' => 1,
        ]);
    }

    public function clear(): void
    {
        session()->remove([
            'tenant_id',
            'tenant_codigo',
            'tenant_base',
            'tenant_rubro',
            'tenant_estado',
            'tenant_access_mode',
            'tenant_access_notice',
            'tenant_access_message',
            'tenant_days_remaining',
            'tenant_active',
        ]);
    }
}
