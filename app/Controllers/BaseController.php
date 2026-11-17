<?php

namespace App\Controllers;

use App\Models\LocalidadModel;
use App\Validation\EmpresaRules;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    public const RUBROS_INDUSTRIALES = [
        'Inyección de Plásticos',
        'Extrusión y Soplado',
        'Rotomoldeo',
        'Termoformado',
        'Reciclador / Recuperador',
        'Automotriz y Autopartes',
        'Construcción y Tuberías',
        'Envases y Packaging',
        'Otro / Distribuidor Industrial',
    ];

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

    protected function empresaRules(bool $isRegister = false, ?string $currentCuit = null, ?string $currentTelefono = null): array
    {
        $postedCuit = (string) $this->request->getPost('cuit');
        $postedTel  = (string) $this->request->getPost('telefono');
        $cuitChanged = $isRegister || EmpresaRules::formatCuit((string) $currentCuit) !== EmpresaRules::formatCuit($postedCuit);
        $telChanged  = $isRegister || trim((string) $currentTelefono) !== trim($postedTel);

        $rules = [
            'nombre'    => ['rules' => 'required|valid_razon_social', 'errors' => ['required' => 'La razón social o nombre de la empresa es obligatorio.']],
            'cuit'      => ['rules' => $cuitChanged ? 'required|valid_cuit' : 'required|min_length[10]|max_length[20]', 'errors' => ['required' => 'El CUIT o CUIL es obligatorio.']],
            'telefono'  => ['rules' => $telChanged ? 'required|valid_telefono' : 'required|min_length[6]|max_length[30]', 'errors' => ['required' => 'El teléfono de contacto es obligatorio.']],
            'rubro'     => $isRegister
                ? ['rules' => 'required|in_list[' . implode(',', self::RUBROS_INDUSTRIALES) . ']', 'errors' => ['required' => 'Seleccioná el rubro industrial principal.', 'in_list' => 'Seleccioná un rubro válido de la lista.']]
                : ['rules' => 'required|min_length[3]|max_length[100]', 'errors' => ['required' => 'El rubro industrial es obligatorio.']],
            'provincia' => ['rules' => 'required|valid_provincia', 'errors' => ['required' => 'Seleccioná la provincia de radicación.']],
            'ciudad'    => ['rules' => 'required|valid_ciudad_provincia[provincia]', 'errors' => ['required' => 'Seleccioná una ciudad o localidad válida.']],
            'direccion' => ['rules' => 'required|valid_direccion', 'errors' => ['required' => 'El domicilio fiscal o de la planta es obligatorio.']],
        ];
        if ($isRegister) {
            $rules['email']        = ['rules' => 'required|valid_email_corporativo|unique_email_empresa', 'errors' => ['required' => 'El correo electrónico corporativo es obligatorio.']];
            $rules['password']     = ['rules' => 'required|valid_password_fuerte', 'errors' => ['required' => 'La contraseña es obligatoria.']];
            $rules['pass_confirm'] = ['rules' => 'required|matches[password]', 'errors' => ['required' => 'Debés confirmar la contraseña.', 'matches' => 'Las contraseñas no coinciden.']];
        }
        return $rules;
    }

    protected function buildCleanEmpresaData(array $raw): array
    {
        $provInput   = trim((string) ($raw['provincia'] ?? ''));
        $ciudadInput = trim((string) ($raw['ciudad'] ?? ''));
        $geo         = (new LocalidadModel())->resolverEnProvincia($provInput, $ciudadInput);

        return [
            'nombre'    => EmpresaRules::normalizeWhitespace((string) ($raw['nombre'] ?? '')),
            'cuit'      => EmpresaRules::formatCuit((string) ($raw['cuit'] ?? '')),
            'telefono'  => EmpresaRules::normalizeWhitespace((string) ($raw['telefono'] ?? '')),
            'rubro'     => EmpresaRules::normalizeWhitespace((string) ($raw['rubro'] ?? '')),
            'provincia' => $geo['provincia'] ?? $provInput,
            'ciudad'    => $geo['ciudad'] ?? $ciudadInput,
            'direccion' => EmpresaRules::normalizeWhitespace((string) ($raw['direccion'] ?? '')),
        ];
    }
}
