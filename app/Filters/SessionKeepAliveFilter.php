<?php

namespace App\Filters;

use App\Libraries\PersistentAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionKeepAliveFilter implements FilterInterface
{
    /**
     * Restaura y mantiene viva la sesión del usuario en todas las rutas públicas y privadas,
     * salvo cuando solicita explícitamente cerrar sesión (/logout).
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (is_cli()) {
            return;
        }

        $uriPath = trim($request->getUri()->getPath(), '/');
        if ($uriPath === 'logout' || str_ends_with($uriPath, '/logout')) {
            return;
        }

        PersistentAuth::ensureSession();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere procesamiento posterior
    }
}
