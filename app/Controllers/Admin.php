<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductoModel;
use App\Models\UserModel;

class Admin extends BaseController
{
    protected UserModel $userModel;
    protected ProductoModel $productoModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->productoModel = new ProductoModel();
    }

    /**
     * Dashboard principal del Administrador: Métricas, Bandeja de Auditoría y Empresas
     */
    public function index()
    {
        // 1. Empresas pendientes de auditoría (prioridad)
        $empresasPendientes = $this->userModel->where('rol !=', 'admin')
                                              ->where('estado', 'pendiente')
                                              ->orderBy('created_at', 'ASC')
                                              ->findAll();

        foreach ($empresasPendientes as &$ep) {
            $ep['total_lotes'] = 0;
        }
        unset($ep);

        // 2. Empresas ya procesadas (activas, inactivas, rechazadas)
        $empresasAuditadas = $this->userModel->where('rol !=', 'admin')
                                            ->where('estado !=', 'pendiente')
                                            ->orderBy('created_at', 'DESC')
                                            ->findAll();

        foreach ($empresasAuditadas as &$ea) {
            $ea['total_lotes'] = $this->productoModel->where('user_id', $ea['id'])->countAllResults();
        }
        unset($ea);

        // Métricas globales
        $totalPendientes    = count($empresasPendientes);
        $totalAuditadas     = count($empresasAuditadas);
        $totalEmpresas      = $totalPendientes + $totalAuditadas;
        $empresasActivas    = count(array_filter($empresasAuditadas, fn($e) => $e['estado'] === 'activo'));
        $empresasInactivas  = count(array_filter($empresasAuditadas, fn($e) => $e['estado'] === 'inactivo'));
        $empresasRechazadas = count(array_filter($empresasAuditadas, fn($e) => $e['estado'] === 'rechazado'));
        $totalLotes         = $this->productoModel->countAllResults();

        // Sumatoria de volumen en KG
        $db = \Config\Database::connect();
        $queryKg = $db->query("SELECT SUM(cantidad_kg) as total_kg FROM productos");
        $totalKg = $queryKg->getRow()->total_kg ?? 0;

        return view('admin/dashboard', [
            'pageTitle'          => 'Panel de Administración y Auditoría | MateriaX',
            'empresasPendientes' => $empresasPendientes,
            'empresasAuditadas'  => $empresasAuditadas,
            'totalPendientes'    => $totalPendientes,
            'totalEmpresas'      => $totalEmpresas,
            'empresasActivas'    => $empresasActivas,
            'empresasInactivas'  => $empresasInactivas,
            'empresasRechazadas' => $empresasRechazadas,
            'totalLotes'         => $totalLotes,
            'totalKg'            => (float) $totalKg,
        ]);
    }

    /**
     * Aprueba una empresa en auditoría, otorgándole estado 'activo' para poder ingresar
     */
    public function aprobar($usuarioId)
    {
        $empresa = $this->userModel->find($usuarioId);

        if (!$empresa || $empresa['rol'] === 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Empresa no encontrada o no sujeta a aprobación.');
        }

        $this->userModel->update($usuarioId, [
            'estado' => 'activo',
        ]);

        return redirect()->to(site_url('admin'))->with('success', "¡Empresa \"{$empresa['nombre']}\" aprobada con éxito! Ya tiene habilitado el acceso comercial a la plataforma.");
    }

    /**
     * Rechaza una solicitud de registro de empresa
     */
    public function rechazar($usuarioId)
    {
        $empresa = $this->userModel->find($usuarioId);

        if (!$empresa || $empresa['rol'] === 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Empresa no encontrada o no sujeta a moderación.');
        }

        $this->userModel->update($usuarioId, [
            'estado' => 'rechazado',
        ]);

        return redirect()->to(site_url('admin'))->with('success', "La solicitud de registro de \"{$empresa['nombre']}\" ha sido rechazada.");
    }

    /**
     * Alterna el estado operativo de una empresa (activo <-> inactivo)
     */
    public function cambiarEstado($usuarioId)
    {
        $empresa = $this->userModel->find($usuarioId);

        if (!$empresa || $empresa['rol'] === 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Empresa no encontrada o no sujeta a moderación.');
        }

        $nuevoEstado = ($empresa['estado'] === 'activo') ? 'inactivo' : 'activo';

        $this->userModel->update($usuarioId, [
            'estado' => $nuevoEstado,
        ]);

        $accion = ($nuevoEstado === 'activo') ? 'reactivada' : 'suspendida/inactivada';
        return redirect()->to(site_url('admin'))->with('success', "La empresa \"{$empresa['nombre']}\" ha sido {$accion} correctamente.");
    }

    /**
     * Ver ficha detallada de una empresa y sus publicaciones
     */
    public function verEmpresa($usuarioId)
    {
        $empresa = $this->userModel->find($usuarioId);

        if (!$empresa) {
            return redirect()->to(site_url('admin'))->with('error', 'Empresa no encontrada.');
        }

        $lotes = $this->productoModel->where('user_id', $usuarioId)
                                     ->orderBy('created_at', 'DESC')
                                     ->findAll();

        return view('admin/ver_empresa', [
            'pageTitle' => "Detalle de Empresa: {$empresa['nombre']} | MateriaX",
            'empresa'   => $empresa,
            'lotes'     => $lotes,
        ]);
    }

    /**
     * Supervisión general de todos los lotes de la plataforma
     */
    public function lotes()
    {
        $lotes = $this->productoModel->getProductosConUsuario();

        return view('admin/lotes', [
            'pageTitle' => 'Supervisión de Lotes de Polímeros | MateriaX',
            'lotes'     => $lotes,
        ]);
    }

    /**
     * Eliminación de lote por moderación administrativa (Cero JS, formulario POST)
     */
    public function eliminarLote($productoId)
    {
        $producto = $this->productoModel->find($productoId);

        if (!$producto) {
            return redirect()->to(site_url('admin/lotes'))->with('error', 'El lote no existe o ya fue eliminado.');
        }

        $this->productoModel->delete($productoId);

        return redirect()->to(site_url('admin/lotes'))->with('success', "El lote \"{$producto['nombre']}\" ha sido eliminado por moderación.");
    }
}
