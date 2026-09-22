<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProductoModel;

class Panel extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Muestra el Panel de Control para la Empresa Registrada
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to(site_url('logout'));
        }

        // Si es el administrador general, ofrecer enlace directo a /admin o permitirle ver su panel personal
        $productoModel = new ProductoModel();

        // 1. Obtener exclusivamente los lotes pertenecientes a esta empresa
        $misProductos = $productoModel->where('user_id', $userId)
                                     ->orderBy('created_at', 'DESC')
                                     ->findAll();

        // 2. Calcular métricas personales de negocio
        $totalLotes       = count($misProductos);
        $totalKilos       = 0.0;
        $valorEstimado    = 0.0;
        $lotesDisponibles = 0;

        foreach ($misProductos as $p) {
            $kilos = (float) ($p['cantidad_kg'] ?? 0);
            $precio = (float) ($p['precio_unitario'] ?? 0);
            $totalKilos += $kilos;
            $valorEstimado += ($kilos * $precio);

            if (($p['estado'] ?? '') === 'Disponible') {
                $lotesDisponibles++;
            }
        }

        // 3. Obtener oportunidades destacadas del mercado (lotes de otras empresas)
        $lotesMercado = $productoModel->select('productos.*, usuarios.nombre as empresa_nombre, usuarios.ciudad, usuarios.provincia')
                                     ->join('usuarios', 'usuarios.id = productos.user_id', 'left')
                                     ->where('productos.user_id !=', $userId)
                                     ->where('productos.estado', 'Disponible')
                                     ->orderBy('productos.created_at', 'DESC')
                                     ->findAll(4);

        return view('panel/index', [
            'pageTitle'        => 'Panel de Empresa | MateriaX',
            'user'             => $user,
            'misProductos'     => $misProductos,
            'totalLotes'       => $totalLotes,
            'totalKilos'       => $totalKilos,
            'valorEstimado'    => $valorEstimado,
            'lotesDisponibles' => $lotesDisponibles,
            'lotesMercado'     => $lotesMercado,
        ]);
    }
}
