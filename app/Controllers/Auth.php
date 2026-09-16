<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Muestra la vista de Inicio de Sesión
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(site_url('productos'));
        }

        return view('auth/login', [
            'pageTitle' => 'Iniciar Sesión | MateriaX',
        ]);
    }

    /**
     * Procesa las credenciales de inicio de sesión
     */
    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        $messages = [
            'email' => [
                'required'    => 'El correo electrónico es requerido.',
                'valid_email' => 'Por favor ingresa un correo electrónico válido.',
            ],
            'password' => [
                'required' => 'La contraseña es requerida.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'El correo electrónico no se encuentra registrado.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Contraseña incorrecta. Por favor verifica tus datos.');
        }

        // Configuración de la sesión de usuario
        $sessionData = [
            'user_id'    => $user['id'],
            'nombre'     => $user['nombre'],
            'email'      => $user['email'],
            'cuit'       => $user['cuit'] ?? '',
            'telefono'   => $user['telefono'] ?? '',
            'rol'        => $user['rol'],
            'isLoggedIn' => true,
        ];

        session()->set($sessionData);

        return redirect()->to(site_url('productos'))->with('success', '¡Bienvenido a MateriaX, ' . esc($user['nombre']) . '!');
    }

    /**
     * Muestra la vista de Registro de Usuario
     */
    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(site_url('productos'));
        }

        return view('auth/register', [
            'pageTitle' => 'Registro de Empresa | MateriaX',
        ]);
    }

    /**
     * Procesa el formulario de registro y crea el usuario
     */
    public function attemptRegister()
    {
        $rules = [
            'nombre'       => 'required|min_length[3]|max_length[100]',
            'email'        => 'required|valid_email|is_unique[usuarios.email]',
            'password'     => 'required|min_length[6]',
            'pass_confirm' => 'required|matches[password]',
            'cuit'         => 'permit_empty|min_length[10]|max_length[20]',
            'telefono'     => 'permit_empty|min_length[6]|max_length[30]',
        ];

        $messages = [
            'nombre' => [
                'required'   => 'El nombre o razón social es obligatorio.',
                'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            ],
            'email' => [
                'required'    => 'El correo electrónico es obligatorio.',
                'valid_email' => 'Debes ingresar un correo electrónico válido.',
                'is_unique'   => 'Este correo electrónico ya está registrado en la red MateriaX.',
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
            'nombre'   => trim($this->request->getPost('nombre')),
            'email'    => trim(strtolower($this->request->getPost('email'))),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'cuit'     => trim($this->request->getPost('cuit') ?? ''),
            'telefono' => trim($this->request->getPost('telefono') ?? ''),
            'rol'      => 'empresa',
        ];

        $newUserId = $userModel->insert($userData);

        if (!$newUserId) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el usuario en la base de datos.');
        }

        // Iniciar sesión automáticamente al registrarse
        $sessionData = [
            'user_id'    => $newUserId,
            'nombre'     => $userData['nombre'],
            'email'      => $userData['email'],
            'cuit'       => $userData['cuit'],
            'telefono'   => $userData['telefono'],
            'rol'        => $userData['rol'],
            'isLoggedIn' => true,
        ];

        session()->set($sessionData);

        return redirect()->to(site_url('productos'))->with('success', '¡Registro completado exitosamente! Tu cuenta empresarial está activa.');
    }

    /**
     * Cierre de sesión seguro
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'Has cerrado tu sesión de forma segura.');
    }
}
