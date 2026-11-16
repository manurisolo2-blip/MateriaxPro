<?php

namespace App\Controllers;

use App\Models\{ProductoModel, UserModel};
use CodeIgniter\HTTP\RedirectResponse;

class Admin extends BaseController
{
    protected ?UserModel $userModel = null;
    protected ?ProductoModel $productoModel = null;

    protected function getUserModel(): UserModel
    {
        return $this->userModel ??= new UserModel();
    }

    protected function getProductoModel(): ProductoModel
    {
        return $this->productoModel ??= new ProductoModel();
    }

    public function index()
    {
        $userModel     = $this->getUserModel();
        $productoModel = $this->getProductoModel();
        $pendientes    = $userModel->where(['rol !=' => 'admin', 'estado' => 'pendiente'])->orderBy('created_at', 'ASC')->findAll();
        $auditadas     = $userModel->where(['rol !=' => 'admin', 'estado !=' => 'pendiente'])->orderBy('created_at', 'DESC')->findAll();
        $loteCounts    = $productoModel->getCountsByUser();

        foreach ($auditadas as &$ea) {
            $ea['total_lotes'] = $loteCounts[$ea['id']] ?? 0;
        }

        return view('admin/dashboard', [
            'pageTitle'          => 'Panel de Administración y Auditoría | MateriaX',
            'empresasPendientes' => $pendientes,
            'empresasAuditadas'  => $auditadas,
            'totalPendientes'    => count($pendientes),
            'empresasActivas'    => count(array_filter($auditadas, static fn ($e) => $e['estado'] === 'activo')),
            'totalLotes'         => $productoModel->countAllResults(),
            'totalKg'            => $productoModel->getTotalKg(),
        ]);
    }

    public function aprobar($usuarioId)
    {
        $empresa = $this->findEmpresa($usuarioId, 'aprobación');
        if ($empresa instanceof RedirectResponse) {
            return $empresa;
        }
        $this->getUserModel()->update($usuarioId, ['estado' => 'activo']);
        return redirect()->to(site_url('admin'))->with('success', "¡Empresa \"{$empresa['nombre']}\" aprobada con éxito! Ya tiene habilitado el acceso comercial a la plataforma.");
    }

    public function rechazar($usuarioId)
    {
        $empresa = $this->findEmpresa($usuarioId, 'moderación');
        if ($empresa instanceof RedirectResponse) {
            return $empresa;
        }
        $this->getUserModel()->update($usuarioId, ['estado' => 'rechazado']);
        return redirect()->to(site_url('admin'))->with('success', "La solicitud de registro de \"{$empresa['nombre']}\" ha sido rechazada.");
    }

    public function cambiarEstado($usuarioId)
    {
        $empresa = $this->findEmpresa($usuarioId, 'moderación');
        if ($empresa instanceof RedirectResponse) {
            return $empresa;
        }
        $nuevo  = ($empresa['estado'] === 'activo') ? 'inactivo' : 'activo';
        $accion = ($nuevo === 'activo') ? 'reactivada' : 'suspendida/inactivada';
        $this->getUserModel()->update($usuarioId, ['estado' => $nuevo]);
        return redirect()->to(site_url('admin'))->with('success', "La empresa \"{$empresa['nombre']}\" ha sido {$accion} correctamente.");
    }

    public function verEmpresa($usuarioId)
    {
        if (!($empresa = $this->getUserModel()->find($usuarioId))) {
            return redirect()->to(site_url('admin'))->with('error', 'Empresa no encontrada.');
        }

        return view('admin/ver_empresa', [
            'pageTitle' => "Detalle de Empresa: {$empresa['nombre']} | MateriaX",
            'empresa'   => $empresa,
            'lotes'     => $this->getProductoModel()->where('user_id', $usuarioId)->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function lotes()
    {
        return view('admin/lotes', [
            'pageTitle' => 'Supervisión de Lotes de Polímeros | MateriaX',
            'lotes'     => $this->getProductoModel()->getProductosConUsuario(),
        ]);
    }

    public function eliminarLote($productoId)
    {
        $productoModel = $this->getProductoModel();
        if (!($producto = $productoModel->find($productoId))) {
            return redirect()->to(site_url('admin/lotes'))->with('error', 'El lote no existe o ya fue eliminado.');
        }
        $productoModel->delete($productoId);
        return redirect()->to(site_url('admin/lotes'))->with('success', "El lote \"{$producto['nombre']}\" ha sido eliminado por moderación.");
    }

    protected function findEmpresa($usuarioId, string $context): array|RedirectResponse
    {
        $empresa = $this->getUserModel()->find($usuarioId);
        if (!$empresa || $empresa['rol'] === 'admin') {
            return redirect()->to(site_url('admin'))->with('error', "Empresa no encontrada o no sujeta a {$context}.");
        }
        return $empresa;
    }
}
