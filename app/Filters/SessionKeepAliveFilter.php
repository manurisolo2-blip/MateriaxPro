<?php

namespace App\Filters;

use App\Libraries\PersistentAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\{RequestInterface, ResponseInterface};

class SessionKeepAliveFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (is_cli() || ENVIRONMENT === 'testing') {
            return;
        }

        $uriPath = trim($request->getUri()->getPath(), '/');
        if ($uriPath === 'logout' || str_ends_with($uriPath, '/logout')) {
            return;
        }

        PersistentAuth::ensureSession();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
