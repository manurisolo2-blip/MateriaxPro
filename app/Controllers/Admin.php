<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Admin extends BaseController
{
    protected UserModel $userModel;
    protected ProductoModel $productoModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $empresasPendientes = $this->userModel->where('rol !=', 'admin')->where('estado', 'pendiente')->orderBy('created_at', 'ASC')->findAll();
        $empresasAuditadas  = $this->userModel->where('rol !=', 'admin')->where('estado !=', 'pendiente')->orderBy('created_at', 'DESC')->findAll();

        $loteCounts = $this->productoModel->getCountsByUser();
        foreach ($empresasAuditadas as &$ea) {
            $ea['total_lotes'] = $loteCounts[$ea['id']] ?? 0;
        }
        unset($ea);

        return view('admin/dashboard', [
            'pageTitle'          => 'Panel de Administración y Auditoría | MateriaX',
            'empresasPendientes' => $empresasPendientes,
            'empresasAuditadas'  => $empresasAuditadas,
            'totalPendientes'    => count($empresasPendientes),
            'empresasActivas'    => count(array_filter($empresasAuditadas, fn($e) => $e['estado'] === 'activo')),
            'totalLotes'         => $this->productoModel->countAllResults(),
            'totalKg'            => $this->productoModel->getTotalKg(),
        ]);
    }

    public function aprobar($usuarioId)
    {
        $empresa = $this->findEmpresa($usuarioId, 'aprobación');
        if ($empresa instanceof RedirectResponse) return $empresa;

        $this->userModel->update($usuarioId, ['estado' => 'activo']);
        return redirect()->to(site_url('admin'))->with('success', "¡Empresa \"{$empresa['nombre']}\" aprobada con éxito! Ya tiene habilitado el acceso comercial a la plataforma.");
    }

    public function rechazar($usuarioId)
    {
        $empresa = $this->findEmpresa($usuarioId, 'moderación');
        if ($empresa instanceof RedirectResponse) return $empresa;

        $this->userModel->update($usuarioId, ['estado' => 'rechazado']);
        return redirect()->to(site_url('admin'))->with('success', "La solicitud de registro de \"{$empresa['nombre']}\" ha sido rechazada.");
    }

    public function cambiarEstado($usuarioId)
    {
        $empresa = $this->findEmpresa($usuarioId, 'moderación');
        if ($empresa instanceof RedirectResponse) return $empresa;

        $nuevoEstado = ($empresa['estado'] === 'activo') ? 'inactivo' : 'activo';
        $this->userModel->update($usuarioId, ['estado' => $nuevoEstado]);

        $accion = ($nuevoEstado === 'activo') ? 'reactivada' : 'suspendida/inactivada';
        return redirect()->to(site_url('admin'))->with('success', "La empresa \"{$empresa['nombre']}\" ha sido {$accion} correctamente.");
    }

    public function verEmpresa($usuarioId)
    {
        if (!($empresa = $this->userModel->find($usuarioId))) {
            return redirect()->to(site_url('admin'))->with('error', 'Empresa no encontrada.');
        }

        return view('admin/ver_empresa', [
            'pageTitle' => "Detalle de Empresa: {$empresa['nombre']} | MateriaX",
            'empresa'   => $empresa,
            'lotes'     => $this->productoModel->where('user_id', $usuarioId)->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function lotes()
    {
        return view('admin/lotes', [
            'pageTitle' => 'Supervisión de Lotes de Polímeros | MateriaX',
            'lotes'     => $this->productoModel->getProductosConUsuario(),
        ]);
    }

    public function eliminarLote($productoId)
    {
        if (!($producto = $this->productoModel->find($productoId))) {
            return redirect()->to(site_url('admin/lotes'))->with('error', 'El lote no existe o ya fue eliminado.');
        }

        $this->productoModel->delete($productoId);
        return redirect()->to(site_url('admin/lotes'))->with('success', "El lote \"{$producto['nombre']}\" ha sido eliminado por moderación.");
    }

    protected function findEmpresa($usuarioId, string $context): array|RedirectResponse
    {
        $empresa = $this->userModel->find($usuarioId);
        if (!$empresa || $empresa['rol'] === 'admin') {
            return redirect()->to(site_url('admin'))->with('error', "Empresa no encontrada o no sujeta a {$context}.");
        }
        return $empresa;
    }
}
