<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Controlador base del sistema.
 * Contiene la configuración inicial compartida por todos los controladores.
 */
abstract class BaseController extends Controller
{
    /**
     * Inicialización del controlador.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // No modificar esta llamada al padre
        parent::initController($request, $response, $logger);

        // Carga inicial de helpers, modelos o servicios si se requieren
    }
}
