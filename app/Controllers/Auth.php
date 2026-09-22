<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected ?UserModel $userModel = null;

    /**
     * Muestra la vista de Inicio de Sesión
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            if (session()->get('rol') === 'admin') {
                return redirect()->to(site_url('admin'));
            }
            return redirect()->to(site_url('panel'));
        }

        return view('auth/login', [
            'pageTitle' => 'Iniciar Sesión | MateriaX',
        ]);
    }

    /**
     * Procesa las credenciales de inicio de sesión con base de datos real
     */
    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        $messages = [
            'email' => [
                'required'    => 'El correo electrónico corporativo es requerido.',
                'valid_email' => 'Por favor ingresa un correo electrónico válido.',
            ],
            'password' => [
                'required' => 'La contraseña es requerida.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $userModel = $this->userModel ?? new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'El correo electrónico no se encuentra registrado en la red MateriaX.');
        }

        // Verificación de estado de auditoría y operatividad
        if (isset($user['estado'])) {
            if ($user['estado'] === 'pendiente') {
                return redirect()->back()->withInput()->with('error', 'Tu cuenta empresarial se encuentra en etapa de auditoría y pendiente de aprobación por el administrador. Podrás ingresar tan pronto como tus datos fiscales sean validados.');
            }
            if ($user['estado'] === 'rechazado') {
                return redirect()->back()->withInput()->with('error', 'Tu solicitud de registro ha sido desestimada tras la auditoría fiscal. Comunícate con la administración.');
            }
            if ($user['estado'] === 'inactivo') {
                return redirect()->back()->withInput()->with('error', 'Esta cuenta empresarial se encuentra inactiva o suspendida. Comuníquese con la administración.');
            }
        }

        // Permitir verificación con y sin espacios accidentales por copia y pega
        if (!password_verify($password, $user['password']) && !password_verify(trim($password), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Contraseña incorrecta. Por favor verifica tus credenciales.');
        }

        // Actualizar marca temporal del último login en la base de datos
        $userModel->updateLastLogin((int) $user['id']);

        // Regenerar ID de sesión para prevenir fijación de sesión
        session()->regenerate();

        // Configuración completa de la sesión de usuario
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

        if ($user['rol'] === 'admin') {
            return redirect()->to(site_url('admin'))->with('success', '¡Bienvenido al Panel de Administración de MateriaX, ' . esc($user['nombre']) . '!');
        }

        return redirect()->to(site_url('panel'))->with('success', '¡Bienvenido/a a tu Panel de Empresa en MateriaX, ' . esc($user['nombre']) . '!');
    }

    /**
     * Muestra la vista de Registro de Usuario
     */
    public function register()
    {
        if (session()->get('isLoggedIn')) {
            if (session()->get('rol') === 'admin') {
                return redirect()->to(site_url('admin'));
            }
            return redirect()->to(site_url('panel'));
        }

        return view('auth/register', [
            'pageTitle' => 'Registro de Empresa | MateriaX',
        ]);
    }

    /**
     * Procesa el formulario de registro y persiste el nuevo usuario en MySQL en estado pendiente de auditoría
     */
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
            'nombre' => [
                'required'   => 'La razón social o nombre de la empresa es obligatorio.',
                'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            ],
            'email' => [
                'required'    => 'El correo electrónico corporativo es obligatorio.',
                'valid_email' => 'Debes ingresar un correo electrónico válido.',
                'is_unique'   => 'Este correo electrónico ya se encuentra registrado en MateriaX.',
            ],
            'cuit' => [
                'required'   => 'El CUIT de la empresa es obligatorio.',
                'min_length' => 'El CUIT debe tener al menos 10 caracteres (ej: 30-XXXXXXXX-X).',
            ],
            'telefono' => [
                'required'   => 'El teléfono institucional de contacto es obligatorio.',
                'min_length' => 'El teléfono debe tener al menos 6 caracteres.',
            ],
            'rubro' => [
                'required' => 'Debe seleccionar el rubro o sector productivo principal.',
            ],
            'ciudad' => [
                'required' => 'La ciudad o localidad de radicación es obligatoria.',
            ],
            'provincia' => [
                'required' => 'La provincia es obligatoria.',
            ],
            'direccion' => [
                'required' => 'El domicilio fiscal o de la planta es obligatorio.',
            ],
            'password' => [
                'required'   => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener un mínimo de 6 caracteres.',
            ],
            'pass_confirm' => [
                'required' => 'Debes confirmar la contraseña.',
                'matches'  => 'Las contraseñas no coinciden.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();

        $userData = [
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
        ];

        $newUserId = $userModel->insert($userData);

        if (!$newUserId) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar la cuenta en la base de datos.');
        }

        // Notificar y redirigir al login para esperar la auditoría del administrador
        return redirect()->to(site_url('login'))->with('success', '¡Solicitud de registro recibida con éxito! Tu cuenta ha ingresado a la etapa de auditoría. El administrador revisará tus datos corporativos para habilitar tu acceso.');
    }

    /**
     * Cierre de sesión seguro y destrucción de variables
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'Has cerrado tu sesión de forma segura.');
    }
}
