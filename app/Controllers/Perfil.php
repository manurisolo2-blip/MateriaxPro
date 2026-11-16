<?php

namespace App\Controllers;

use App\Libraries\PersistentAuth;
use App\Models\{LocalidadModel, ProductoModel, UserModel};
use CodeIgniter\HTTP\RedirectResponse;

class Perfil extends BaseController
{
    protected UserModel $userModel;
    protected ProductoModel $productoModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->productoModel = new ProductoModel();
    }

    protected function getAuthUser(): array|RedirectResponse
    {
        $session = session();
        $userId  = $this->userId();
        $user    = $userId > 0 ? $this->userModel->find($userId) : null;

        if (!$user && $session->get('email')) {
            $user = $this->userModel->findByEmail((string) $session->get('email'));
            if ($user) {
                $session->set('user_id', (int) $user['id']);
                PersistentAuth::syncFromSession();
            }
        }

        if ($user) {
            return $user;
        }
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(site_url('logout'));
        }

        return [
            'id'        => $userId,
            'nombre'    => (string) ($session->get('nombre') ?: 'Empresa Registrada'),
            'email'     => (string) $session->get('email'),
            'cuit'      => (string) $session->get('cuit'),
            'telefono'  => (string) $session->get('telefono'),
            'rubro'     => (string) $session->get('rubro'),
            'ciudad'    => (string) $session->get('ciudad'),
            'provincia' => (string) $session->get('provincia'),
            'direccion' => (string) $session->get('direccion'),
            'rol'       => (string) ($session->get('rol') ?: 'empresa'),
            'password'  => '',
        ];
    }

    public function index()
    {
        $user = $this->getAuthUser();
        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $locModel = new LocalidadModel();
        return view('perfil/index', [
            'pageTitle'        => 'Mi Cuenta Empresarial | MateriaX',
            'user'             => $user,
            'provincias'       => $locModel->getProvincias(),
            'todasLocalidades' => $locModel->getTodasConProvincia(),
            'userProv'         => old('provincia', $user['provincia'] ?? 'Córdoba'),
            'userCiudad'       => old('ciudad', $user['ciudad'] ?? 'Río Tercero'),
            'misProductos'     => $this->productoModel->where('user_id', $user['id'])->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function actualizar()
    {
        $user = $this->getAuthUser();
        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $ciudad = $this->resolveCiudadPost();
        if (!$this->validate($this->empresaRules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($ciudad === '') {
            return redirect()->back()->withInput()->with('errors', ['ciudad' => 'La ciudad o localidad es obligatoria.']);
        }

        $data           = array_map(static fn ($v) => trim((string) $v), $this->request->getPost(['nombre', 'cuit', 'telefono', 'rubro', 'provincia', 'direccion']));
        $data['ciudad'] = $ciudad;

        if (!empty($user['id'])) {
            $this->userModel->update($user['id'], $data);
        }
        session()->set($data);
        PersistentAuth::syncFromSession();

        return redirect()->to(site_url('perfil'))->with('success', '¡Datos empresariales actualizados correctamente!');
    }

    public function cambiarPassword()
    {
        $user = $this->getAuthUser();
        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $rules = [
            'current_password' => ['rules' => 'required', 'errors' => ['required' => 'Debes ingresar tu contraseña actual.']],
            'new_password'     => ['rules' => 'required|min_length[6]', 'errors' => ['required' => 'La nueva contraseña es requerida.', 'min_length' => 'La nueva contraseña debe tener al menos 6 caracteres.']],
            'confirm_password' => ['rules' => 'required|matches[new_password]', 'errors' => ['required' => 'Debes confirmar la nueva contraseña.', 'matches' => 'La confirmación no coincide con la nueva contraseña.']],
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }
        if (empty($user['password']) || !password_verify((string) $this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual ingresada es incorrecta.');
        }

        if (!empty($user['id'])) {
            $this->userModel->update($user['id'], ['password' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT)]);
        }
        return redirect()->to(site_url('perfil'))->with('success', '¡Contraseña actualizada exitosamente!');
    }
}
