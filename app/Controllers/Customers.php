<?php

namespace App\Controllers;

use App\Models\ClientesModel;
use App\Models\CustomersModel;
use App\Models\RubrosModel;
use App\Models\UsersModel;
use Config\Database;

class Customers extends BaseController
{

    public function register()
    {
        $rubrosModel = new RubrosModel();
        $rubros = [];

        try {
            $rubros = $rubrosModel->orderBy('descripcion', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $rubros = [];
        }

        return view('customers/register', ['rubros' => $rubros]);
    }

    public function dbRegister()
    {
        $clientesModel = new ClientesModel();
        $usersModel = new UsersModel();
        $rubrosModel = new RubrosModel();

        $phone = preg_replace('/\D+/', '', (string) $this->request->getVar('phone'));
        $name = trim((string) $this->request->getVar('name'));
        $razonSocial = trim((string) $this->request->getVar('razon_social'));
        $dni = trim((string) $this->request->getVar('dni'));
        $city = trim((string) $this->request->getVar('city'));
        $idRubro = (int) $this->request->getVar('id_rubro');
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $password = (string) $this->request->getVar('password');
        $linkPathInput = trim((string) $this->request->getVar('link_path'));

        if ($phone === '' || $name === '' || $razonSocial === '' || $dni === '' || $city === '' || $idRubro <= 0 || $email === '' || $password === '') {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'Debe completar todos los campos']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'El email ingresado no es valido']);
        }
        if (!$this->isValidPasswordComplexity($password)) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero']);
        }
        $rubro = $rubrosModel->find($idRubro);
        if (!$rubro) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'El rubro seleccionado no es valido']);
        }

        if ($usersModel->where('email', $email)->first() || $clientesModel->where('email', $email)->first()) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'El email ya se encuentra registrado']);
        }

        $base = $this->normalizeTenantKey($razonSocial);
        if ($base === '') {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'No se pudo generar la base del cliente']);
        }
        if ($this->databaseExists($base) || $this->clienteBaseExists($base)) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'Ya existe una base con ese nombre']);
        }

        $linkSlug = $this->normalizeTenantKey($linkPathInput !== '' ? ltrim($linkPathInput, '/') : $base);
        if ($linkSlug === '') {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'No se pudo generar el link del cliente']);
        }
        $link = '/' . ltrim($linkSlug, '/');
        if ($this->clienteLinkExists($link)) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'El link ingresado ya existe']);
        }

        $codigo = $this->getNextClienteCodigo();
        $username = $this->nextAvailableUsername($usersModel, explode('@', $email)[0] ?? 'usuario');
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!$passwordHash) {
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'No se pudo procesar la contrasena']);
        }

        $databaseCreated = false;
        $userId = 0;
        try {
            $this->createDatabase($base);
            $databaseCreated = true;
            $this->provisionClienteDatabase($base, (string) ($rubro['descripcion'] ?? ''));

            $usersModel->insert([
                'user' => $username,
                'email' => $email,
                'cuenta' => $codigo,
                'password' => $passwordHash,
                'superadmin' => 0,
                'name' => $name,
                'active' => 1,
            ]);
            $userId = (int) $usersModel->getInsertID();

            $clientesModel->insert([
                'codigo' => $codigo,
                'NombreApellido' => $name,
                'razon_social' => $razonSocial,
                'base' => $base,
                'id_rubro' => $idRubro,
                'email' => $email,
                'telefono' => $phone !== '' ? $phone : null,
                'dni' => $dni !== '' ? $dni : null,
                'localidad' => $city !== '' ? $city : null,
                'habilitado' => 1,
                'estado' => 'TRIAL',
                'link' => $link,
            ]);

            $this->upsertTenantUser($base, $username, $email, $passwordHash, $name);
        } catch (\Throwable $e) {
            if ($userId > 0) {
                try {
                    $usersModel->delete($userId);
                } catch (\Throwable $ignore) {
                }
            }
            if ($databaseCreated) {
                try {
                    $db = Database::connect('alfareserva');
                    $db->query('DROP DATABASE `' . $base . '`');
                } catch (\Throwable $ignore) {
                }
            }
            return redirect()->to('customers/register')->withInput()->with('msg', ['type' => 'danger', 'body' => 'Error al registrar: ' . $e->getMessage()]);
        }

        return redirect()->to(base_url('auth/login'))->with('msg', ['type' => 'success', 'body' => 'Alta registrada correctamente']);
    }

    private function isValidPasswordComplexity(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password) === 1;
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

    private function getNextClienteCodigo(): string
    {
        $prefix = '11201';
        $db = Database::connect('alfareserva');
        $row = $db->query(
            "SELECT MAX(CAST(SUBSTRING(codigo, 6) AS UNSIGNED)) AS max_suffix
             FROM clientes
             WHERE codigo LIKE ?",
            [$prefix . '%']
        )->getRowArray();
        $maxSuffix = (int) ($row['max_suffix'] ?? 0);
        return $prefix . str_pad((string) ($maxSuffix > 0 ? $maxSuffix + 1 : 1), 4, '0', STR_PAD_LEFT);
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

    private function clienteBaseExists(string $base): bool
    {
        $db = Database::connect('alfareserva');
        return (bool) $db->table('clientes')->select('id')->where('base', $base)->get()->getRowArray();
    }

    private function clienteLinkExists(string $link): bool
    {
        $db = Database::connect('alfareserva');
        return (bool) $db->table('clientes')->select('id')->where('link', $link)->get()->getRowArray();
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

    private function upsertTenantUser(string $base, string $user, string $email, string $passwordHash, string $name): void
    {
        $db = Database::connect('alfareserva');
        $table = "`{$base}`.`user`";
        $existing = $db->query(
            "SELECT id FROM {$table} WHERE email = ? OR user = ? LIMIT 1",
            [$email, $user]
        )->getRowArray();

        if ($existing) {
            $db->query(
                "UPDATE {$table} SET `user` = ?, `email` = ?, `password` = ?, `name` = ?, `active` = 1 WHERE id = ?",
                [$user, $email, $passwordHash, $name !== '' ? $name : $user, (int) $existing['id']]
            );
            return;
        }

        $db->query(
            "INSERT INTO {$table} (`user`, `email`, `password`, `name`, `active`) VALUES (?, ?, ?, ?, 1)",
            [$user, $email, $passwordHash, $name !== '' ? $name : $user]
        );
    }

    public function createOffer()
    {
        return view('customers/createOffer');
    }

    public function delete($id)
    {
        $customersModel = new CustomersModel();

        try {
            $customersModel->delete($id);
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Cliente eliminado existosamente']);
        } catch (\Exception $e) {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'El cliente no se pudo eliminar']);
        }
    }

    public function editWindow($id)
    {

        $customersModel = new CustomersModel();
        $customer = $customersModel->find($id);

        return view('customers/editar', ['customer' => $customer]);
    }

    public function edit()
    {
        $customersModel = new CustomersModel();

        $id = $this->request->getVar('idCustomer');
        $phone = $this->request->getVar('phone');
        $name = $this->request->getVar('name');
        $lastName = $this->request->getVar('last_name');
        $dni = $this->request->getVar('dni');
        $offer = $this->request->getVar('offer');
        $city = $this->request->getVar('city');
        $this->ensureLocalityExists($city);

        $query = [
            'name' => $name,
            'last_name' => $lastName,
            'dni' => $dni,
            'phone' => $phone,
            'offer' => $offer,
            'city' => $city
        ];

        try {
            $customersModel->update($id, $query);
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Cliente editado existosamente']);
        } catch (\Exception $e) {
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'El cliente no se pudo editar']);
        }
    }

    public function getCustomer($phone)
    {
        $customersModel = new CustomersModel();
        $rawPhone = trim((string)$phone);
        $digits = preg_replace('/\D+/', '', $rawPhone);
        $base = $digits !== '' ? $digits : $rawPhone;

        $variants = [$base];
        if ($base !== '') {
            $withoutLeadingZero = ltrim($base, '0');
            if ($withoutLeadingZero !== '') {
                $variants[] = $withoutLeadingZero;
                $variants[] = '0' . $withoutLeadingZero;
            }
        }
        $variants = array_values(array_unique(array_filter($variants, fn($v) => $v !== null && $v !== '')));

        $query = $customersModel;
        $first = true;
        foreach ($variants as $variant) {
            if ($first) {
                $query = $query->where('phone', $variant);
                $first = false;
            } else {
                $query = $query->orWhere('phone', $variant);
            }
        }
        $customer = $query->first();

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $customer, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function getCustomers()
    {
        $customersModel = new CustomersModel();
        $limitParam = $this->request->getGet('limit');
        $limit = is_numeric($limitParam) ? (int)$limitParam : 0;
        if ($limit > 0) {
            // Tope defensivo para evitar consultas excesivas.
            $limit = min($limit, 200);
            $customers = $customersModel->orderBy('id', 'DESC')->findAll($limit);
        } else {
            $customers = $customersModel->findAll();
        }

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $customers, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function getCustomersWithOffer()
    {
        $customersModel = new CustomersModel();

        $customers = $customersModel->where('offer', 1)->findAll();

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $customers, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }
    
    public function setOfferTrue(){
        $customersModel = new CustomersModel();
        
        try {
            // Aplicar oferta a todos los clientes sin depender del estado previo.
            $customersModel->builder()->set('offer', 1)->update();

            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }


    public function setOfferFalse(){
        $customersModel = new CustomersModel();
        
        try {
            // Quitar oferta a todos los clientes sin depender del estado previo.
            $customersModel->builder()->set('offer', 0)->update();

            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
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
