<?php

namespace App\Controllers;

use App\Models\{CertificadoModel, UserModel};
use CodeIgniter\Exceptions\PageNotFoundException;

class Certificados extends BaseController
{
    protected CertificadoModel $certificadoModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->certificadoModel = new CertificadoModel();
        $this->userModel        = new UserModel();
    }

    public function index()
    {
        $userId = $this->userId();
        return view('certificados/index', [
            'pageTitle'    => 'Certificados Ambientales y Huella Verde | MateriaX',
            'certificados' => $this->certificadoModel->getCertificadosPorEmpresa($userId),
            'metricas'     => $this->certificadoModel->getMetricasEmpresa($userId),
        ]);
    }

    public function ver($codigo = null)
    {
        if (empty($codigo) || !($cert = $this->certificadoModel->getDetalleCertificado($codigo))) {
            throw PageNotFoundException::forPageNotFound(
                empty($codigo) ? 'Identificador de certificado no provisto.' : 'El certificado solicitado no existe en el registro circular.'
            );
        }

        $userId = $this->userId();
        if (!$this->isAdmin() && (int) $cert['generadora_id'] !== $userId && (int) $cert['revalorizadora_id'] !== $userId) {
            return redirect()->to(site_url('panel'))
                ->with('error', 'Acceso denegado: este certificado pertenece a operaciones de otras empresas.');
        }

        return view('certificados/ver', [
            'pageTitle'   => "Certificado {$cert['codigo_certificado']} | MateriaX",
            'certificado' => $cert,
        ]);
    }
}
