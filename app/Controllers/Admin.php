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
     * Dashboard principal del Administrador: Métricas y Listado de Empresas
     */
    public function index()
    {
        $empresas = $this->userModel->where('rol !=', 'admin')
                                    ->orderBy('created_at', 'DESC')
                                    ->findAll();

        // Enriquecer cada empresa con la cantidad de lotes publicados
        foreach ($empresas as &$empresa) {
            $empresa['total_lotes'] = $this->productoModel->where('user_id', $empresa['id'])->countAllResults();
        }
        unset($empresa);

        // Métricas del sistema
        $totalEmpresas     = count($empresas);
        $empresasActivas   = count(array_filter($empresas, fn($e) => $e['estado'] === 'activo'));
        $empresasInactivas = count(array_filter($empresas, fn($e) => $e['estado'] === 'inactivo'));
        $totalLotes        = $this->productoModel->countAllResults();

        // Sumatoria de volumen en KG
        $db = \Config\Database::connect();
        $queryKg = $db->query("SELECT SUM(cantidad_kg) as total_kg FROM productos");
        $totalKg = $queryKg->getRow()->total_kg ?? 0;

        return view('admin/dashboard', [
            'pageTitle'         => 'Panel de Administración | MateriaX',
            'empresas'          => $empresas,
            'totalEmpresas'     => $totalEmpresas,
            'empresasActivas'   => $empresasActivas,
            'empresasInactivas' => $empresasInactivas,
            'totalLotes'        => $totalLotes,
            'totalKg'           => (float) $totalKg,
        ]);
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

        $accion = ($nuevoEstado === 'activo') ? 'activada' : 'suspendida/inactivada';
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
