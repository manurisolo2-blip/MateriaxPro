<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProductoModel;

class Panel extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $userId = (int) session()->get('user_id');
        if (!($user = (new UserModel())->find($userId))) {
            return redirect()->to(site_url('logout'));
        }

        $productoModel = new ProductoModel();
        $misProductos  = $productoModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        $totalKilos       = (float) array_sum(array_column($misProductos, 'cantidad_kg'));
        $valorEstimado    = (float) array_sum(array_map(fn($p) => (float)($p['cantidad_kg'] ?? 0) * (float)($p['precio_unitario'] ?? 0), $misProductos));
        $lotesDisponibles = count(array_filter($misProductos, fn($p) => ($p['estado'] ?? '') === 'Disponible'));

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
            'totalLotes'       => count($misProductos),
            'totalKilos'       => $totalKilos,
            'valorEstimado'    => $valorEstimado,
            'lotesDisponibles' => $lotesDisponibles,
            'lotesMercado'     => $lotesMercado,
        ]);
    }
}
