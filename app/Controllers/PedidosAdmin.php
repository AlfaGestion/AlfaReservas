<?php

namespace App\Controllers;

class PedidosAdmin extends ComidaAdmin
{
    private function resolveClienteFallback(string $identifier): ?array
    {
        $tenant = \Config\Services::tenant();
        $id = trim($identifier);
        if ($id === '') {
            return null;
        }

        return preg_match('/^[0-9]{9}$/', $id) === 1
            ? $tenant->resolveByCodigo($id)
            : $tenant->resolveBySlug($id);
    }

    private function tenantAdminPath(array $cliente): string
    {
        $tenant = \Config\Services::tenant();
        $slug = $tenant->extractSlugFromLink((string) ($cliente['link'] ?? ''));
        if ($slug === '') {
            $slug = strtolower(trim((string) ($cliente['base'] ?? '')));
        }
        if ($slug === '') {
            $slug = trim((string) ($cliente['codigo'] ?? ''));
        }
        return '/' . ltrim($slug, '/') . '/admin';
    }

    private function redirectIfNotPedidos(string $identifier)
    {
        $cliente = $this->resolveClienteFallback($identifier);
        if (!is_array($cliente)) {
            return null;
        }

        $rubro = strtolower(trim((string) ($cliente['rubro'] ?? '')));
        if (!in_array($rubro, ['comida', 'pedidos'], true)) {
            return redirect()->to($this->tenantAdminPath($cliente));
        }

        return null;
    }

    public function login(string $identifier)
    {
        $redirect = $this->redirectIfNotPedidos($identifier);
        if ($redirect) {
            return $redirect;
        }
        return parent::login($identifier);
    }

    public function doLogin(string $identifier)
    {
        $redirect = $this->redirectIfNotPedidos($identifier);
        if ($redirect) {
            return $redirect;
        }
        return parent::doLogin($identifier);
    }

    public function logout(string $identifier)
    {
        $redirect = $this->redirectIfNotPedidos($identifier);
        if ($redirect) {
            return $redirect;
        }
        return parent::logout($identifier);
    }

    public function index(string $identifier)
    {
        $redirect = $this->redirectIfNotPedidos($identifier);
        if ($redirect) {
            return $redirect;
        }
        return parent::index($identifier);
    }
}
