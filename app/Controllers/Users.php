<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class Users extends BaseController
{
    private function isValidPasswordComplexity(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password) === 1;
    }

    public function index()
    {
        //
    }

    public function getUser($id)
    {
        $modelUsers = new UsersModel();

        $user = $modelUsers->where('id', $id)->first();


        try {
            return  $this->response->setJSON($this->setResponse(null, null, $user, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function editUser()
    {
        $modelUsers = new UsersModel();
        $data = $this->request->getJSON();
        $password = $data->password;

        if (!is_string($password) || trim($password) === '') {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Debe ingresar una contrasena.'));
        }
        if (!$this->isValidPasswordComplexity($password)) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'La contrasena debe tener al menos una mayuscula, una minuscula y un numero.'));
        }

        $query = [
            'user' => $data->user,
            'email' => strtolower(trim((string) ($data->email ?? ''))),
            'cuenta' => trim((string) ($data->cuenta ?? $data->user)),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $data->name,
            'superadmin' => $data->superadmin,
        ];

        try {
            $modelUsers->update($data->id, $query);

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
