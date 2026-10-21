<?php

namespace App\Controllers;

use App\Models\CertificadoModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Certificados extends BaseController
{
    protected CertificadoModel $certificadoModel;
    protected UserModel $userModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->certificadoModel = new CertificadoModel();
        $this->userModel        = new UserModel();
    }

    /**
     * Listado y métricas de huella ecológica de la empresa logueada
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');

        $certificados = $this->certificadoModel->getCertificadosPorEmpresa($userId);
        $metricas     = $this->certificadoModel->getMetricasEmpresa($userId);

        return view('certificados/index', [
            'pageTitle'    => 'Certificados Ambientales y Huella Verde | MateriaX',
            'certificados' => $certificados,
            'metricas'     => $metricas,
        ]);
    }

    /**
     * Vista de constancia formal e imprimible del certificado
     */
    public function ver($codigo = null)
    {
        if (empty($codigo)) {
            throw PageNotFoundException::forPageNotFound('Identificador de certificado no provisto.');
        }

        $certificado = $this->certificadoModel->getDetalleCertificado($codigo);
        if (!$certificado) {
            throw PageNotFoundException::forPageNotFound('El certificado solicitado no existe en el registro circular.');
        }

        $userId = (int) session()->get('user_id');
        $rol    = session()->get('rol');

        // Verificación de acceso
        if ($rol !== 'admin' && (int)$certificado['generadora_id'] !== $userId && (int)$certificado['revalorizadora_id'] !== $userId) {
            return redirect()->to(site_url('panel'))
                             ->with('error', 'Acceso denegado: este certificado pertenece a operaciones de otras empresas.');
        }

        return view('certificados/ver', [
            'pageTitle'   => "Certificado {$certificado['codigo_certificado']} | MateriaX",
            'certificado' => $certificado,
        ]);
    }
}
