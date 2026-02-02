<?php

namespace App\Controllers;

use App\Models\CustomersModel;

class Customers extends BaseController
{

    public function register()
    {
        return view('customers/register');
    }

    public function dbRegister()
    {
        $modelCustomers = new CustomersModel();

        $phone = $this->request->getVar('areaCode') . $this->request->getVar('phone');
        $name = $this->request->getVar('name');
        $lastName = $this->request->getVar('last_name');
        $dni = $this->request->getVar('dni');
        $city = $this->request->getVar('city');


        $existingPhone = $modelCustomers->where('phone', $phone)->findAll();

        if ($phone == '' || $name == '' || $lastName == '' || $dni == '') {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'Debe completar todos los campos']);
        }

        if ($existingPhone) {
            return redirect()->to('customers/register')->with('msg', ['type' => 'danger', 'body' => 'El teléfono coincide con un usuario ya registrado']);
        }

        $query = [
            'name' => $name,
            'last_name' => $lastName,
            'dni' => $dni,
            'phone' => $phone,
            'offer' => 0,
            'city' => $city,
        ];


        try {
            $modelCustomers->insert($query);
        } catch (\Exception $e) {
            return "Error al insertar datos: " . $e->getMessage();
        }

        return redirect()->to(base_url())->with('msg', ['type' => 'success', 'body' => 'Usuario registrado correctamente']);
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

        $customer = $customersModel->where('phone', $phone)->first();

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $customer, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function getCustomers()
    {
        $customersModel = new CustomersModel();

        $customers = $customersModel->findAll();

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
        $data = $this->request->getJSON();
        
        try {

            $customersModel->set(['offer' => $data])->where('offer', false)->update();

            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }


    public function setOfferFalse(){
        $customersModel = new CustomersModel();
        $data = $this->request->getJSON();
        
        try {

            $customersModel->set(['offer' => $data])->where('offer', true)->update();

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
