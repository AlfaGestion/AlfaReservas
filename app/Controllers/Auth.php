<?php

namespace App\Controllers;

use App\Models\UsersModel;
use Config\Database;

class Auth extends BaseController
{
    private function sanitizeRedirectPath(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $path)) {
            return null;
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        if (!preg_match('#^/[A-Za-z0-9/_-]+$#', $path)) {
            return null;
        }

        return $path;
    }

    private function masterAdminEmail(): string
    {
        return strtolower(trim((string) env('MASTER_ADMIN_EMAIL', 'marcoslromero23@gmail.com')));
    }

    private function canManageUsers(): bool
    {
        if (!session()->get('logueado')) {
            return false;
        }

        if ((int) session()->get('superadmin') !== 1) {
            return false;
        }

        $sessionEmail = strtolower(trim((string) session()->get('email')));
        return $sessionEmail !== '' && $sessionEmail === $this->masterAdminEmail();
    }

    private function isValidPasswordComplexity(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password) === 1;
    }

    private function blockIfCannotManageUsers()
    {
        if ($this->canManageUsers()) {
            return null;
        }

        if (!session()->get('logueado')) {
            return redirect()->to('auth/login')->with('msg', ['type' => 'danger', 'body' => 'Debe iniciar sesion para acceder.']);
        }

        return redirect()->to('abmRubros')->with('msg', ['type' => 'danger', 'body' => 'Solo el admin maestro puede crear usuarios.']);
    }

    private function resolveClienteAdminPath(array $userData): ?string
    {
        $sessionEmail = strtolower(trim((string) ($userData['email'] ?? '')));
        $cuenta = trim((string) ($userData['cuenta'] ?? ''));
        $isMasterAdmin = ((int) ($userData['superadmin'] ?? 0) === 1) && $sessionEmail === $this->masterAdminEmail();
        if ($isMasterAdmin || strtolower($cuenta) === 'alfa') {
            return null;
        }

        $db = Database::connect('alfareserva');
        if (!$db->tableExists('clientes')) {
            return null;
        }

        $builder = $db->table('clientes')->select('link');
        if ($cuenta !== '') {
            $builder->groupStart()
                ->where('codigo', $cuenta)
                ->orWhere('base', $cuenta)
                ->groupEnd();
        }
        if ($sessionEmail !== '') {
            if ($cuenta !== '') {
                $builder->orWhere('email', $sessionEmail);
            } else {
                $builder->where('email', $sessionEmail);
            }
        }

        $cliente = $builder->orderBy('id', 'DESC')->get()->getRowArray();
        $link = trim((string) ($cliente['link'] ?? ''));
        if ($link === '') {
            return null;
        }

        $path = '/' . ltrim($link, '/');
        return $this->sanitizeRedirectPath($path . '/admin');
    }

    private function resolveClienteForUser(array $userData): ?array
    {
        $tenant = \Config\Services::tenant();
        $sessionEmail = strtolower(trim((string) ($userData['email'] ?? '')));
        $cuenta = trim((string) ($userData['cuenta'] ?? ''));

        if (preg_match('/^[0-9]{9}$/', $cuenta) === 1) {
            $byCode = $tenant->resolveByCodigo($cuenta);
            if (is_array($byCode)) {
                return $byCode;
            }
        }

        if ($cuenta !== '') {
            $bySlug = $tenant->resolveBySlug($cuenta);
            if (is_array($bySlug)) {
                return $bySlug;
            }
        }

        if ($sessionEmail !== '') {
            $db = Database::connect('alfareserva');
            if ($db->tableExists('clientes')) {
                $row = $db->table('clientes')
                    ->select('codigo')
                    ->where('LOWER(email)', $sessionEmail)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();
                $codigo = trim((string) ($row['codigo'] ?? ''));
                if ($codigo !== '') {
                    $byCode = $tenant->resolveByCodigo($codigo);
                    if (is_array($byCode)) {
                        return $byCode;
                    }
                }
            }
        }

        return null;
    }

    private function activateTenantForUser(array $userData): void
    {
        $tenant = \Config\Services::tenant();
        $tenant->clear();

        $sessionEmail = strtolower(trim((string) ($userData['email'] ?? '')));
        $cuenta = strtolower(trim((string) ($userData['cuenta'] ?? '')));
        $isMasterAdmin = ((int) ($userData['superadmin'] ?? 0) === 1) && $sessionEmail === $this->masterAdminEmail();
        if ($isMasterAdmin || $cuenta === 'alfa') {
            return;
        }

        $cliente = $this->resolveClienteForUser($userData);
        if (is_array($cliente)) {
            $tenant->activate($cliente);
        }
    }

    public function index()
    {
        $redirectPath = $this->sanitizeRedirectPath((string) $this->request->getVar('redirect'));

        if (session()->get('logueado')) {
            if ($redirectPath) {
                return redirect()->to($redirectPath);
            }
            $panelPath = $this->sanitizeRedirectPath((string) session()->get('admin_panel_path'));
            return redirect()->to($panelPath ?: '/abmRubros');
        }

        return view('auth/login', ['redirectPath' => $redirectPath]);
    }

    public function login()
    {
        $modelUsers = new UsersModel();

        $credential = trim((string) $this->request->getVar('account'));
        if ($credential === '') {
            $credential = trim((string) $this->request->getVar('user'));
        }
        $password = $this->request->getVar('password');
        $redirectPath = $this->sanitizeRedirectPath((string) $this->request->getVar('redirect'));

        $userData = $modelUsers
            ->groupStart()
            ->where('cuenta', $credential)
            ->orWhere('email', $credential)
            ->orWhere('user', $credential)
            ->groupEnd()
            ->first();

        if (isset($userData) && (int) ($userData['active'] ?? 0) === 1 && password_verify($password, $userData['password'])) {
            $sessionData = [
                'id_user'    => $userData['id'],
                'user'       => $userData['user'],
                'email'      => $userData['email'] ?? null,
                'cuenta'     => $userData['cuenta'] ?? null,
                'active'     => $userData['active'],
                'name'       => $userData['name'],
                'superadmin' => $userData['superadmin'],
                'logueado'   => true,
            ];

            session()->set($sessionData);
            $this->activateTenantForUser($userData);

            if ($redirectPath) {
                return redirect()->to($redirectPath);
            }
            $clienteAdminPath = $this->resolveClienteAdminPath($userData);
            if ($clienteAdminPath) {
                session()->set('admin_panel_path', $clienteAdminPath);
                return redirect()->to($clienteAdminPath);
            }
            session()->set('admin_panel_path', '/abmRubros');
            return redirect()->to('/abmRubros');
        }

        $loginPath = '/auth/login';
        if ($redirectPath) {
            $loginPath .= '?redirect=' . rawurlencode($redirectPath);
        }

        return redirect()->to($loginPath)->with('msg', ['type' => 'danger', 'body' => 'La cuenta o la contrasena no son correctas']);
    }

    public function log_out()
    {
        session()->destroy();

        return redirect()->to('/auth/login');
    }

    public function register()
    {
        $blocked = $this->blockIfCannotManageUsers();
        if ($blocked) {
            return $blocked;
        }

        $modelUsers = new UsersModel();
        $users = $modelUsers->findAll();

        return view('auth/register', ['users' => $users]);
    }

    public function dbRegister()
    {
        $blocked = $this->blockIfCannotManageUsers();
        if ($blocked) {
            return $blocked;
        }

        $modelUsers = new UsersModel();

        $usuario = trim((string) $this->request->getVar('user'));
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $cuenta = trim((string) $this->request->getVar('cuenta'));
        $password = $this->request->getVar('password');
        $repeatPassword = $this->request->getVar('repeat_password');

        if ($password !== $repeatPassword) {
            return redirect()->to('auth/register')->with('msg', ['type' => 'danger', 'body' => 'Las contrasenas no coinciden']);
        }

        if ($usuario === '' || $email === '' || $password === '') {
            return redirect()->to('auth/register')->with('msg', ['type' => 'danger', 'body' => 'Debe completar todos los datos']);
        }
        if (!$this->isValidPasswordComplexity((string) $password)) {
            return redirect()->to('auth/register')->with('msg', ['type' => 'danger', 'body' => 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('auth/register')->with('msg', ['type' => 'danger', 'body' => 'El email ingresado no es valido']);
        }

        if ($cuenta === '') {
            $cuenta = $usuario;
        }

        $exists = $modelUsers
            ->groupStart()
            ->where('email', $email)
            ->orWhere('cuenta', $cuenta)
            ->orWhere('user', $usuario)
            ->groupEnd()
            ->first();

        if ($exists) {
            return redirect()->to('auth/register')->with('msg', ['type' => 'danger', 'body' => 'El usuario, email o cuenta ya existe']);
        }

        $query = [
            'user' => $usuario,
            'email' => $email,
            'cuenta' => $cuenta,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'superadmin' => $email === $this->masterAdminEmail() ? 1 : 0,
            'name' => $usuario,
            'active' => 1,
        ];

        try {
            $modelUsers->insert($query);
        } catch (\Exception $e) {
            return 'Error al insertar datos: ' . $e->getMessage();
        }

        return redirect()->to('auth/register')->with('msg', ['type' => 'success', 'body' => 'Usuario creado correctamente']);
    }
}
