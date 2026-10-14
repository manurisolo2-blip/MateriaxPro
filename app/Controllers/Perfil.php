<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProductoModel;
use CodeIgniter\HTTP\RedirectResponse;

class Perfil extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected UserModel $userModel;
    protected ProductoModel $productoModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->productoModel = new ProductoModel();
    }

    protected function getAuthUser(): array|RedirectResponse
    {
        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);
        return $user ?: redirect()->to(site_url('logout'));
    }

    public function index()
    {
        $user = $this->getAuthUser();
        if ($user instanceof RedirectResponse) return $user;

        $localidadModel = new \App\Models\LocalidadModel();
        $provincias = $localidadModel->getProvincias();
        $todasLocalidades = $localidadModel->getTodasConProvincia();

        $userProv = old('provincia', $user['provincia'] ?? 'Córdoba');
        $userCiudad = old('ciudad', $user['ciudad'] ?? 'Río Tercero');

        return view('perfil/index', [
            'pageTitle'        => 'Mi Cuenta Empresarial | MateriaX',
            'user'             => $user,
            'provincias'       => $provincias,
            'todasLocalidades' => $todasLocalidades,
            'userProv'         => $userProv,
            'userCiudad'       => $userCiudad,
            'misProductos'     => $this->productoModel->where('user_id', $user['id'])->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function actualizar()
    {
        $user = $this->getAuthUser();
        if ($user instanceof RedirectResponse) return $user;

        $ciudad = trim((string) $this->request->getPost('ciudad'));
        $ciudadOtra = trim((string) $this->request->getPost('ciudad_otra'));
        if ($ciudad === '__otra__') {
            $ciudad = $ciudadOtra;
        }

        $rules = [
            'nombre'    => ['rules' => 'required|min_length[3]|max_length[100]', 'errors' => ['required' => 'La razón social es obligatoria.', 'min_length' => 'El nombre debe tener al menos 3 caracteres.']],
            'cuit'      => ['rules' => 'required|min_length[10]|max_length[20]', 'errors' => ['required' => 'El CUIT es obligatorio.', 'min_length' => 'El CUIT debe tener al menos 10 caracteres.']],
            'telefono'  => ['rules' => 'required|min_length[6]|max_length[30]', 'errors' => ['required' => 'El teléfono de contacto es obligatorio.', 'min_length' => 'El teléfono debe tener al menos 6 caracteres.']],
            'rubro'     => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'El rubro industrial es obligatorio.']],
            'provincia' => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'La provincia es obligatoria.']],
            'direccion' => ['rules' => 'required|max_length[150]', 'errors' => ['required' => 'El domicilio de planta es obligatorio.']],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (empty($ciudad)) {
            return redirect()->back()->withInput()->with('errors', ['ciudad' => 'La ciudad o localidad es obligatoria.']);
        }

        $fields = ['nombre', 'cuit', 'telefono', 'rubro', 'provincia', 'direccion'];
        $data   = array_map(fn($v) => trim((string)$v), $this->request->getPost($fields));
        $data['ciudad'] = $ciudad;

        $this->userModel->update($user['id'], $data);
        session()->set($data);

        return redirect()->to(site_url('perfil'))->with('success', '¡Datos empresariales actualizados correctamente!');
    }

    public function cambiarPassword()
    {
        $user = $this->getAuthUser();
        if ($user instanceof RedirectResponse) return $user;

        $rules = [
            'current_password' => ['rules' => 'required', 'errors' => ['required' => 'Debes ingresar tu contraseña actual.']],
            'new_password'     => ['rules' => 'required|min_length[6]', 'errors' => ['required' => 'La nueva contraseña es requerida.', 'min_length' => 'La nueva contraseña debe tener al menos 6 caracteres.']],
            'confirm_password' => ['rules' => 'required|matches[new_password]', 'errors' => ['required' => 'Debes confirmar la nueva contraseña.', 'matches' => 'La confirmación no coincide con la nueva contraseña.']],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        if (!password_verify((string) $this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual ingresada es incorrecta.');
        }

        $this->userModel->update($user['id'], [
            'password' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to(site_url('perfil'))->with('success', '¡Contraseña actualizada exitosamente!');
    }
}
