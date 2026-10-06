<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected ?UserModel $userModel = null;

    protected function getModel(): UserModel
    {
        return $this->userModel ??= new UserModel();
    }

    protected function redirectIfLoggedIn(): ?RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(site_url(session()->get('rol') === 'admin' ? 'admin' : 'panel'));
        }
        return null;
    }

    public function login()
    {
        if ($redirect = $this->redirectIfLoggedIn()) return $redirect;
        return view('auth/login', ['pageTitle' => 'Iniciar Sesión | MateriaX']);
    }

    public function attemptLogin()
    {
        $rules = [
            'email'    => ['rules' => 'required|valid_email', 'errors' => ['required' => 'El correo electrónico corporativo es requerido.', 'valid_email' => 'Por favor ingresa un correo electrónico válido.']],
            'password' => ['rules' => 'required', 'errors' => ['required' => 'La contraseña es requerida.']],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $userModel = $this->getModel();
        $user      = $userModel->findByEmail($email);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'El correo electrónico no se encuentra registrado en la red MateriaX. Si aún no eres miembro, puedes registrar tu empresa.');
        }

        if (($user['estado'] ?? '') === 'pendiente') {
            return redirect()->back()->withInput()->with('error', 'Tu cuenta empresarial para ' . esc($user['nombre']) . ' está en proceso de auditoría fiscal. Podrás ingresar una vez que el Administrador valide tu CUIT y datos de radicación.');
        }
        if (($user['estado'] ?? '') === 'rechazado') {
            return redirect()->back()->withInput()->with('error', 'Tu solicitud de registro no fue aprobada por el Administrador tras la auditoría fiscal. Para consultas o reenvío de documentación, contacta a contacto@materiax.com.ar.');
        }
        if (($user['estado'] ?? '') === 'inactivo') {
            return redirect()->back()->withInput()->with('error', 'Esta cuenta empresarial se encuentra temporalmente suspendida o inactiva. Por favor comunícate con administración para gestionar su reactivación.');
        }

        if (!password_verify($password, $user['password']) && !password_verify(trim($password), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'La contraseña ingresada es incorrecta. Por favor verifica tus credenciales e inténtalo nuevamente.');
        }

        $userModel->updateLastLogin((int) $user['id']);
        session()->regenerate();
        session()->set([
            'user_id'    => (int) $user['id'],
            'nombre'     => $user['nombre'],
            'email'      => $user['email'],
            'cuit'       => $user['cuit'] ?? '',
            'telefono'   => $user['telefono'] ?? '',
            'rubro'      => $user['rubro'] ?? '',
            'ciudad'     => $user['ciudad'] ?? '',
            'provincia'  => $user['provincia'] ?? '',
            'direccion'  => $user['direccion'] ?? '',
            'rol'        => $user['rol'] ?? 'empresa',
            'isLoggedIn' => true,
        ]);

        $esAdmin = ($user['rol'] === 'admin');
        $destino = $esAdmin ? 'admin' : 'panel';
        $tipo    = $esAdmin ? 'Panel de Administración' : 'Panel de Empresa';

        return redirect()->to(site_url($destino))->with('success', "¡Bienvenido/a a tu {$tipo} en MateriaX, " . esc($user['nombre']) . '!');
    }

    public function register()
    {
        if ($redirect = $this->redirectIfLoggedIn()) return $redirect;
        return view('auth/register', ['pageTitle' => 'Registro de Empresa | MateriaX']);
    }

    public function attemptRegister()
    {
        $rules = [
            'nombre'       => 'required|min_length[3]|max_length[100]',
            'email'        => 'required|valid_email|is_unique[usuarios.email]',
            'cuit'         => 'required|min_length[10]|max_length[20]',
            'telefono'     => 'required|min_length[6]|max_length[30]',
            'rubro'        => 'required|max_length[100]',
            'ciudad'       => 'required|max_length[100]',
            'provincia'    => 'required|max_length[100]',
            'direccion'    => 'required|max_length[150]',
            'password'     => 'required|min_length[6]',
            'pass_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'nombre'       => ['required' => 'La razón social o nombre de la empresa es obligatorio.', 'min_length' => 'El nombre debe tener al menos 3 caracteres.'],
            'email'        => ['required' => 'El correo electrónico corporativo es obligatorio.', 'valid_email' => 'Debes ingresar un correo electrónico válido.', 'is_unique' => 'Este correo electrónico ya se encuentra registrado en MateriaX.'],
            'cuit'         => ['required' => 'El CUIT de la empresa es obligatorio.', 'min_length' => 'El CUIT debe tener al menos 10 caracteres (ej: 30-XXXXXXXX-X).'],
            'telefono'     => ['required' => 'El teléfono institucional de contacto es obligatorio.', 'min_length' => 'El teléfono debe tener al menos 6 caracteres.'],
            'rubro'        => ['required' => 'Debe seleccionar el rubro o sector productivo principal.'],
            'ciudad'       => ['required' => 'La ciudad o localidad de radicación es obligatoria.'],
            'provincia'    => ['required' => 'La provincia es obligatoria.'],
            'direccion'    => ['required' => 'El domicilio fiscal o de la planta es obligatorio.'],
            'password'     => ['required' => 'La contraseña es obligatoria.', 'min_length' => 'La contraseña debe tener un mínimo de 6 caracteres.'],
            'pass_confirm' => ['required' => 'Debes confirmar la contraseña.', 'matches' => 'Las contraseñas no coinciden.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = $this->getModel();
        $newUserId = $userModel->insert([
            'nombre'       => trim((string) $this->request->getPost('nombre')),
            'email'        => strtolower(trim((string) $this->request->getPost('email'))),
            'password'     => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'cuit'         => trim((string) $this->request->getPost('cuit')),
            'telefono'     => trim((string) $this->request->getPost('telefono')),
            'rubro'        => trim((string) $this->request->getPost('rubro')),
            'ciudad'       => trim((string) $this->request->getPost('ciudad')),
            'provincia'    => trim((string) $this->request->getPost('provincia')),
            'direccion'    => trim((string) $this->request->getPost('direccion')),
            'rol'          => 'empresa',
            'estado'       => 'pendiente',
            'ultimo_login' => null,
        ]);

        if (!$newUserId) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar la cuenta en la base de datos.');
        }

        return redirect()->to(site_url('login'))->with('success', '¡Solicitud de registro enviada con éxito! Tu empresa ha ingresado a la etapa de auditoría fiscal. El Administrador revisará tus datos corporativos para habilitar el acceso a la red.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'Has cerrado tu sesión de forma segura.');
    }
}
