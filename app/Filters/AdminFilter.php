<?php

namespace App\Filters;

use App\Libraries\PersistentAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    /**
     * Verificar que el usuario tenga sesión activa y rol 'admin'
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        PersistentAuth::ensureSession();

        $session = session();
        $isAjax  = ($request instanceof IncomingRequest && $request->isAJAX())
            || str_contains($request->getHeaderLine('Accept'), 'application/json')
            || $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        // 1. Debe estar logueado
        if (!$session->get('isLoggedIn')) {
            if ($isAjax) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['status' => 'error', 'code' => 'unauthenticated', 'message' => 'Sesión no iniciada.']);
            }
            return redirect()->to(site_url('login'))->with('error', 'Debes iniciar sesión con una cuenta de administrador para acceder a esta sección.');
        }

        // 2. Debe poseer rol de administrador
        if ($session->get('rol') !== 'admin') {
            if ($isAjax) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['status' => 'error', 'code' => 'forbidden', 'message' => 'Acceso denegado.']);
            }
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
