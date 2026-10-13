<?php

use CodeIgniter\Boot;
use Config\Paths;

// Verificar versión de PHP
$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Tu versión de PHP debe ser %s o superior para ejecutar CodeIgniter. Versión actual: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

// Ruta al controlador frontal
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Asegurar que el directorio de trabajo apunte a esta carpeta
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

// Cargar archivo de configuración de rutas
require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

// Iniciar el framework
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
