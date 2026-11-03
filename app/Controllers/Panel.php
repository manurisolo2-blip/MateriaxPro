<?php

namespace App\Controllers;

use App\Libraries\PersistentAuth;
use App\Models\UserModel;
use App\Models\ProductoModel;
use App\Models\PlantaModel;
use App\Models\TransaccionModel;

class Panel extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $session   = session();
        $userId    = (int) $session->get('user_id');
        $userModel = new UserModel();
        $user      = $userId > 0 ? $userModel->find($userId) : null;

        if (!$user && $session->get('email')) {
            $user = $userModel->findByEmail((string) $session->get('email'));
            if ($user) {
                $userId = (int) $user['id'];
                $session->set('user_id', $userId);
                PersistentAuth::syncFromSession();
            }
        }

        if (!$user) {
            $user = [
                'id'        => $userId,
                'nombre'    => (string) ($session->get('nombre') ?: 'Empresa Registrada'),
                'email'     => (string) $session->get('email'),
                'cuit'      => (string) $session->get('cuit'),
                'telefono'  => (string) $session->get('telefono'),
                'rubro'     => (string) $session->get('rubro'),
                'ciudad'    => (string) $session->get('ciudad'),
                'provincia' => (string) $session->get('provincia'),
                'direccion' => (string) $session->get('direccion'),
                'rol'       => (string) ($session->get('rol') ?: 'empresa'),
            ];
        }

        $productoModel    = new ProductoModel();
        $plantaModel      = new PlantaModel();
        $transaccionModel = new TransaccionModel();

        $misProductos  = $productoModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        $totalKilos            = (float) array_sum(array_column($misProductos, 'cantidad_kg'));
        $valorEstimado         = (float) array_sum(array_map(fn($p) => (float)($p['cantidad_kg'] ?? 0) * (float)($p['precio_unitario'] ?? 0), $misProductos));
        $lotesDisponibles      = count(array_filter($misProductos, fn($p) => ($p['estado'] ?? '') === 'Disponible'));
        $totalPlantas          = $plantaModel->where('empresa_id', $userId)->countAllResults();
        $solicitudesPendientes = $transaccionModel->where('vendedor_id', $userId)->where('estado', 'solicitada')->countAllResults();
        $totalComprasEnviadas  = $transaccionModel->where('comprador_id', $userId)->countAllResults();

        $lotesMercado = $productoModel->select('productos.*, usuarios.nombre as empresa_nombre, usuarios.ciudad, usuarios.provincia')
                                      ->join('usuarios', 'usuarios.id = productos.user_id', 'left')
                                      ->where('productos.user_id !=', $userId)
                                      ->where('productos.estado', 'Disponible')
                                      ->orderBy('productos.created_at', 'DESC')
                                      ->findAll(4);

        return view('panel/index', [
            'pageTitle'             => 'Panel de Empresa | MateriaX',
            'user'                  => $user,
            'misProductos'          => $misProductos,
            'totalLotes'            => count($misProductos),
            'totalKilos'            => $totalKilos,
            'valorEstimado'         => $valorEstimado,
            'lotesDisponibles'      => $lotesDisponibles,
            'totalPlantas'          => $totalPlantas,
            'solicitudesPendientes' => $solicitudesPendientes,
            'totalComprasEnviadas'  => $totalComprasEnviadas,
            'lotesMercado'          => $lotesMercado,
        ]);
    }
}
