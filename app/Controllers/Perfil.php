<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProductoModel;

class Perfil extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Muestra la información de la cuenta y publicaciones de la empresa
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to(site_url('logout'));
        }

        $productoModel = new ProductoModel();
        $misProductos = $productoModel->where('user_id', $userId)
                                     ->orderBy('created_at', 'DESC')
                                     ->findAll();

        return view('perfil/index', [
            'pageTitle'    => 'Mi Cuenta Empresarial | MateriaX',
            'user'         => $user,
            'misProductos' => $misProductos,
        ]);
    }

    /**
     * Actualiza los datos de contacto y radicación de la empresa
     */
    public function actualizar()
    {
        $userId = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to(site_url('logout'));
        }

        $rules = [
            'nombre'    => 'required|min_length[3]|max_length[100]',
            'cuit'      => 'required|min_length[10]|max_length[20]',
            'telefono'  => 'required|min_length[6]|max_length[30]',
            'rubro'     => 'required|max_length[100]',
            'ciudad'    => 'required|max_length[100]',
            'provincia' => 'required|max_length[100]',
            'direccion' => 'required|max_length[150]',
        ];

        $messages = [
            'nombre' => [
                'required'   => 'La razón social es obligatoria.',
                'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            ],
            'cuit' => [
                'required'   => 'El CUIT es obligatorio.',
                'min_length' => 'El CUIT debe tener al menos 10 caracteres.',
            ],
            'telefono' => [
                'required'   => 'El teléfono de contacto es obligatorio.',
                'min_length' => 'El teléfono debe tener al menos 6 caracteres.',
            ],
            'rubro' => [
                'required' => 'El rubro industrial es obligatorio.',
            ],
            'ciudad' => [
                'required' => 'La ciudad es obligatoria.',
            ],
            'provincia' => [
                'required' => 'La provincia es obligatoria.',
            ],
            'direccion' => [
                'required' => 'El domicilio de planta es obligatorio.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre'    => trim((string) $this->request->getPost('nombre')),
            'cuit'      => trim((string) $this->request->getPost('cuit')),
            'telefono'  => trim((string) $this->request->getPost('telefono')),
            'rubro'     => trim((string) $this->request->getPost('rubro')),
            'ciudad'    => trim((string) $this->request->getPost('ciudad')),
            'provincia' => trim((string) $this->request->getPost('provincia')),
            'direccion' => trim((string) $this->request->getPost('direccion')),
        ];

        $userModel->update($userId, $data);

        // Actualizar datos de sesión activa
        session()->set([
            'nombre'    => $data['nombre'],
            'cuit'      => $data['cuit'],
            'telefono'  => $data['telefono'],
            'rubro'     => $data['rubro'],
            'ciudad'    => $data['ciudad'],
            'provincia' => $data['provincia'],
            'direccion' => $data['direccion'],
        ]);

        return redirect()->to(site_url('perfil'))->with('success', '¡Datos empresariales actualizados correctamente!');
    }

    /**
     * Permite cambiar la contraseña verificando la actual
     */
    public function cambiarPassword()
    {
        $userId = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to(site_url('logout'));
        }

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        $messages = [
            'current_password' => [
                'required' => 'Debes ingresar tu contraseña actual.',
            ],
            'new_password' => [
                'required'   => 'La nueva contraseña es requerida.',
                'min_length' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            ],
            'confirm_password' => [
                'required' => 'Debes confirmar la nueva contraseña.',
                'matches'  => 'La confirmación no coincide con la nueva contraseña.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $currentPass = (string) $this->request->getPost('current_password');
        $newPass     = (string) $this->request->getPost('new_password');

        if (!password_verify($currentPass, $user['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual ingresada es incorrecta.');
        }

        $userModel->update($userId, [
            'password' => password_hash($newPass, PASSWORD_DEFAULT),
        ]);

        return redirect()->to(site_url('perfil'))->with('success', '¡Contraseña actualizada exitosamente!');
    }
}
