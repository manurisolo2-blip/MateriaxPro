<?php

namespace App\Filters;

use App\Libraries\PersistentAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\{IncomingRequest, RequestInterface, ResponseInterface};

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (ENVIRONMENT !== 'testing') {
            PersistentAuth::ensureSession();
        }
        if (!session()->get('isLoggedIn')) {
            $isAjax = ($request instanceof IncomingRequest && $request->isAJAX())
                || str_contains($request->getHeaderLine('Accept'), 'application/json')
                || $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                return service('response')->setStatusCode(401)->setJSON([
                    'status' => 'error', 'code' => 'unauthenticated',
                    'message' => 'Tu sesión no está iniciada. Por favor vuelve a ingresar.',
                ]);
            }
            return redirect()->to(site_url('login'))
                ->with('error', 'Debes iniciar sesión para acceder al sistema y ver el listado de productos.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
