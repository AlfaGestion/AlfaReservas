<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use App\Models\RubrosModel;

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
        $modelCustomers = new CustomersModel();

        $rubrosModel = new RubrosModel();
        $phone = preg_replace('/\D+/', '', (string) $this->request->getVar('phone'));
        $name = trim((string) $this->request->getVar('name'));
        $razonSocial = trim((string) $this->request->getVar('razon_social'));
        $dni = trim((string) $this->request->getVar('dni'));
        $city = trim((string) $this->request->getVar('city'));
        $idRubro = (int) $this->request->getVar('id_rubro');
        $email = strtolower(trim((string) $this->request->getVar('email')));
        $password = (string) $this->request->getVar('password');


        $existingPhone = $modelCustomers->where('phone', $phone)->first();

        if ($phone === '' || $name === '' || $razonSocial === '' || $dni === '' || $city === '' || $idRubro <= 0 || $email === '' || $password === '') {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'Debe completar todos los campos']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'El email ingresado no es valido']);
        }

        if (!$rubrosModel->find($idRubro)) {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'El rubro seleccionado no es valido']);
        }

        if ($existingPhone) {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'El telefono coincide con un usuario ya registrado']);
        }

        $existingEmail = $modelCustomers->where('email', $email)->first();
        if ($existingEmail) {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'El email ya se encuentra registrado']);
        }

        $query = [
            'name' => $name,
            'last_name' => '-',
            'razon_social' => $razonSocial,
            'dni' => $dni,
            'phone' => $phone,
            'offer' => 0,
            'city' => $city,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id_rubro' => $idRubro,
        ];


        try {
            $modelCustomers->insert($query);
        } catch (\Exception $e) {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'Error al registrar: ' . $e->getMessage()]);
        }

        return redirect()->to(base_url('auth/login'))->with('msg', ['type' => 'success', 'body' => 'Alta registrada correctamente']);
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
