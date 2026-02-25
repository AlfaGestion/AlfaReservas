<?php

namespace App\Libraries;

use Config\Database;

class Tenant
{
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

        $dbAlfa = Database::connect('alfareserva');

        return $dbAlfa->table('clientes c')
            ->select('c.codigo, c.base, c.habilitado, c.razon_social, c.link, r.descripcion AS rubro')
            ->join('rubros r', 'r.id = c.id_rubro', 'left')
            ->where('c.codigo', $codigo)
            ->where('c.habilitado', 1)
            ->get()
            ->getRowArray();
    }

    public function resolveBySlug(string $slug): ?array
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || preg_match('/^[a-z0-9_]+$/', $slug) !== 1) {
            return null;
        }

        $dbAlfa = Database::connect('alfareserva');
        $cliente = $dbAlfa->table('clientes c')
            ->select('c.codigo, c.base, c.habilitado, c.razon_social, c.link, r.descripcion AS rubro')
            ->join('rubros r', 'r.id = c.id_rubro', 'left')
            ->where('c.base', $slug)
            ->where('c.habilitado', 1)
            ->get()
            ->getRowArray();

        if ($cliente) {
            return $cliente;
        }

        $candidatos = $dbAlfa->table('clientes c')
            ->select('c.codigo, c.base, c.habilitado, c.razon_social, c.link, r.descripcion AS rubro')
            ->join('rubros r', 'r.id = c.id_rubro', 'left')
            ->where('c.habilitado', 1)
            ->where('c.link IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($candidatos as $candidato) {
            if ($this->extractSlugFromLink((string) ($candidato['link'] ?? '')) === $slug) {
                return $candidato;
            }
        }

        return null;
    }

    public function activate(array $cliente): void
    {
        session()->set([
            'tenant_codigo' => (string) ($cliente['codigo'] ?? ''),
            'tenant_base' => (string) ($cliente['base'] ?? ''),
            'tenant_rubro' => (string) ($cliente['rubro'] ?? ''),
            'tenant_active' => 1,
        ]);
    }

    public function clear(): void
    {
        session()->remove([
            'tenant_codigo',
            'tenant_base',
            'tenant_rubro',
            'tenant_active',
        ]);
    }
}
