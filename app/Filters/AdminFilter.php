<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    /**
     * Verificar que el usuario tenga sesión activa y rol 'admin'
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Debe estar logueado
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Debes iniciar sesión con una cuenta de administrador para acceder a esta sección.');
        }

        // 2. Debe poseer rol de administrador
        if ($session->get('rol') !== 'admin') {
            return redirect()->to(site_url('productos'))->with('error', 'Acceso denegado: Esta sección es exclusiva para el administrador del sistema.');
        }
    }

    /**
     * Procesamiento posterior al filtro (no requerido)
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción posterior
    }
}
