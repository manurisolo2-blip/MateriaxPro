<?php

namespace App\Controllers;

use App\Libraries\PersistentAuth;
use App\Models\{PlantaModel, ProductoModel, TransaccionModel, UserModel};

class Panel extends BaseController
{
    public function index()
    {
        $session   = session();
        $userId    = $this->userId();
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
            if ($userId <= 0 && !$session->get('isLoggedIn')) {
                return redirect()->to(site_url('logout'));
            }
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
        $transaccionModel = new TransaccionModel();
        $misProductos     = $productoModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        $lotesMercado = $productoModel
            ->select('productos.*, usuarios.nombre as empresa_nombre, usuarios.ciudad, usuarios.provincia')
            ->join('usuarios', 'usuarios.id = productos.user_id', 'left')
            ->where(['productos.user_id !=' => $userId, 'productos.estado' => 'Disponible'])
            ->orderBy('productos.created_at', 'DESC')
            ->findAll(4);

        return view('panel/index', [
            'pageTitle'             => 'Panel de Empresa | MateriaX',
            'user'                  => $user,
            'misProductos'          => $misProductos,
            'totalLotes'            => count($misProductos),
            'totalKilos'            => (float) array_sum(array_column($misProductos, 'cantidad_kg')),
            'valorEstimado'         => (float) array_sum(array_map(static fn ($p) => (float) ($p['cantidad_kg'] ?? 0) * (float) ($p['precio_unitario'] ?? 0), $misProductos)),
            'lotesDisponibles'      => count(array_filter($misProductos, static fn ($p) => ($p['estado'] ?? '') === 'Disponible')),
            'totalPlantas'          => (new PlantaModel())->where('empresa_id', $userId)->countAllResults(),
            'solicitudesPendientes' => $transaccionModel->contarPendientesRecibidas($userId),
            'totalComprasEnviadas'  => $transaccionModel->where('comprador_id', $userId)->countAllResults(),
            'lotesMercado'          => $lotesMercado,
        ]);
    }
}
