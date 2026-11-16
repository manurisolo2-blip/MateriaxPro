<?php

namespace App\Controllers;

use App\Libraries\PersistentAuth;
use App\Models\{LocalidadModel, UserModel};
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    protected ?UserModel $userModel = null;

    protected function getModel(): UserModel
    {
        return $this->userModel ??= new UserModel();
    }

    protected function redirectIfLoggedIn(): ?RedirectResponse
    {
        if (ENVIRONMENT !== 'testing') {
            PersistentAuth::ensureSession();
        }
        return session()->get('isLoggedIn')
            ? redirect()->to(site_url($this->isAdmin() ? 'admin' : 'panel'))
            : null;
    }

    public function login()
    {
        return $this->redirectIfLoggedIn() ?? view('auth/login', ['pageTitle' => 'Iniciar Sesión | MateriaX']);
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
        $model    = $this->getModel();
        $user     = $model->findByEmail($email);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'El correo electrónico no se encuentra registrado en la red MateriaX. Si aún no eres miembro, puedes registrar tu empresa.');
        }

        $stateErrors = [
            'pendiente' => 'Tu cuenta empresarial para ' . esc($user['nombre']) . ' está en proceso de auditoría fiscal. Podrás ingresar una vez que el Administrador valide tu CUIT y datos de radicación.',
            'rechazado' => 'Tu solicitud de registro no fue aprobada por el Administrador tras la auditoría fiscal. Para consultas o reenvío de documentación, contacta a contacto@materiax.com.ar.',
            'inactivo'  => 'Esta cuenta empresarial se encuentra temporalmente suspendida o inactiva. Por favor comunícate con administración para gestionar su reactivación.',
        ];
        if (isset($stateErrors[$user['estado'] ?? ''])) {
            return redirect()->back()->withInput()->with('error', $stateErrors[$user['estado']]);
        }

        if (!password_verify($password, $user['password']) && !password_verify(trim($password), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'La contraseña ingresada es incorrecta. Por favor verifica tus credenciales e inténtalo nuevamente.');
        }

        $model->updateLastLogin((int) $user['id']);
        session()->regenerate();
        $sessionData = [
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
        ];
        session()->set($sessionData);
        PersistentAuth::persistUser($sessionData);

        $esAdmin = ($user['rol'] === 'admin');
        $tipo    = $esAdmin ? 'Panel de Administración' : 'Panel de Empresa';
        return redirect()->to(site_url($esAdmin ? 'admin' : 'panel'))
            ->with('success', "¡Bienvenido/a a tu {$tipo} en MateriaX, " . esc($user['nombre']) . '!');
    }

    public function register()
    {
        if ($r = $this->redirectIfLoggedIn()) {
            return $r;
        }
        $locModel = new LocalidadModel();
        return view('auth/register', [
            'pageTitle'         => 'Registro de Empresa | MateriaX',
            'provincias'        => $locModel->getProvincias(),
            'todasLocalidades'  => $locModel->getTodasConProvincia(),
            'selectedProvincia' => (string) old('provincia', 'Córdoba'),
            'selectedCiudad'    => (string) old('ciudad', 'Río Tercero'),
        ]);
    }

    public function localidades($provinciaId = null)
    {
        $id         = $provinciaId ? (int) $provinciaId : ($this->request->getGet('provincia_id') ? (int) $this->request->getGet('provincia_id') : null);
        $provNombre = trim((string) $this->request->getGet('provincia'));
        $search     = trim((string) ($this->request->getGet('q') ?? $this->request->getGet('buscar')));

        return $this->response->setJSON((new LocalidadModel())->buscar($search, $id, $provNombre, 100));
    }

    public function attemptRegister()
    {
        $ciudad = $this->resolveCiudadPost();
        if (!$this->validate($this->empresaRules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($ciudad === '') {
            return redirect()->back()->withInput()->with('errors', ['ciudad' => 'La ciudad o localidad de radicación es obligatoria.']);
        }

        $data = array_map(static fn ($v) => trim((string) $v), $this->request->getPost(['nombre', 'cuit', 'telefono', 'rubro', 'provincia', 'direccion']));
        $data += [
            'ciudad'       => $ciudad,
            'email'        => strtolower(trim((string) $this->request->getPost('email'))),
            'password'     => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'rol'          => 'empresa',
            'estado'       => 'pendiente',
            'ultimo_login' => null,
        ];

        if (!$this->getModel()->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar la cuenta en la base de datos.');
        }

        return redirect()->to(site_url('login'))
            ->with('success', '¡Solicitud de registro enviada con éxito! Tu empresa ha ingresado a la etapa de auditoría fiscal. El Administrador revisará tus datos corporativos para habilitar el acceso a la red.');
    }

    public function logout()
    {
        PersistentAuth::clear();
        return redirect()->to(site_url('login'))->with('success', 'Has cerrado tu sesión de forma segura.');
    }
}
