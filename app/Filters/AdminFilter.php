<?php

namespace App\Filters;

use App\Libraries\PersistentAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\{IncomingRequest, RequestInterface, ResponseInterface};

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (ENVIRONMENT !== 'testing') {
            PersistentAuth::ensureSession();
        }
        $session = session();
        $isAjax  = ($request instanceof IncomingRequest && $request->isAJAX())
            || str_contains($request->getHeaderLine('Accept'), 'application/json')
            || $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        if (!$session->get('isLoggedIn')) {
            if ($isAjax) {
                return service('response')->setStatusCode(401)->setJSON([
                    'status' => 'error', 'code' => 'unauthenticated', 'message' => 'Sesión no iniciada.',
                ]);
            }
            return redirect()->to(site_url('login'))
                ->with('error', 'Debes iniciar sesión con una cuenta de administrador para acceder a esta sección.');
        }
        if ($session->get('rol') !== 'admin') {
            if ($isAjax) {
                return service('response')->setStatusCode(403)->setJSON([
                    'status' => 'error', 'code' => 'forbidden', 'message' => 'Acceso denegado.',
                ]);
            }
            return redirect()->to(site_url('productos'))
                ->with('error', 'Acceso denegado: Esta sección es exclusiva para el administrador del sistema.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
