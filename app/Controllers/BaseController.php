<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function userId(): int
    {
        return (int) session()->get('user_id');
    }

    protected function isAdmin(): bool
    {
        return session()->get('rol') === 'admin';
    }

    protected function isAjax(): bool
    {
        return $this->request->isAJAX()
            || str_contains($this->request->getHeaderLine('Accept'), 'application/json')
            || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    protected function releaseSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    protected function noStoreJson(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setStatusCode($status)
            ->setJSON($payload);
    }

    protected function resolveCiudadPost(): string
    {
        $ciudad = trim((string) $this->request->getPost('ciudad'));
        return $ciudad === '__otra__' ? trim((string) $this->request->getPost('ciudad_otra')) : $ciudad;
    }

    protected function empresaRules(bool $isRegister = false): array
    {
        $rules = [
            'nombre'    => ['rules' => 'required|min_length[3]|max_length[100]', 'errors' => ['required' => 'La razón social o nombre de la empresa es obligatorio.', 'min_length' => 'El nombre debe tener al menos 3 caracteres.']],
            'cuit'      => ['rules' => 'required|min_length[10]|max_length[20]', 'errors' => ['required' => 'El CUIT de la empresa es obligatorio.', 'min_length' => 'El CUIT debe tener al menos 10 caracteres (ej: 30-XXXXXXXX-X).']],
            'telefono'  => ['rules' => 'required|min_length[6]|max_length[30]', 'errors' => ['required' => 'El teléfono de contacto es obligatorio.', 'min_length' => 'El teléfono debe tener al menos 6 caracteres.']],
            'rubro'     => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'El rubro industrial es obligatorio.']],
            'provincia' => ['rules' => 'required|max_length[100]', 'errors' => ['required' => 'La provincia es obligatoria.']],
            'direccion' => ['rules' => 'required|max_length[150]', 'errors' => ['required' => 'El domicilio fiscal o de la planta es obligatorio.']],
        ];
        if ($isRegister) {
            $rules['email']        = ['rules' => 'required|valid_email|is_unique[usuarios.email]', 'errors' => ['required' => 'El correo electrónico corporativo es obligatorio.', 'valid_email' => 'Debes ingresar un correo electrónico válido.', 'is_unique' => 'Este correo electrónico ya se encuentra registrado en MateriaX.']];
            $rules['password']     = ['rules' => 'required|min_length[6]', 'errors' => ['required' => 'La contraseña es obligatoria.', 'min_length' => 'La contraseña debe tener un mínimo de 6 caracteres.']];
            $rules['pass_confirm'] = ['rules' => 'required|matches[password]', 'errors' => ['required' => 'Debes confirmar la contraseña.', 'matches' => 'Las contraseñas no coinciden.']];
        }
        return $rules;
    }
}
