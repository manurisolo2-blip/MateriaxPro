<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rutas Públicas
$routes->get('/', 'Home::index');

// Diagnostico de Conexion de Base de Datos y Persistencia
$routes->get('estado-db', static function () {
    $db = \Config\Database::connect();

    $users = [];
    $totalUsuarios = 0;
    try {
        $users = $db->table('usuarios')->select('id, nombre, email, rol, estado, created_at')->get()->getResultArray();
        $totalUsuarios = count($users);
    } catch (\Throwable $e) {
        $users = ['error' => $e->getMessage()];
    }

    $driver = $db->DBDriver;
    $isMySQL = ($driver === 'MySQLi');

    $resolveEnv = static function (array $keys): ?string {
        foreach ($keys as $key) {
            $val = getenv($key);
            if ($val !== false && $val !== null && trim((string)$val) !== '') {
                return trim((string)$val);
            }
            if (!empty($_ENV[$key]) && trim((string)$_ENV[$key]) !== '') {
                return trim((string)$_ENV[$key]);
            }
            if (!empty($_SERVER[$key]) && trim((string)$_SERVER[$key]) !== '') {
                return trim((string)$_SERVER[$key]);
            }
        }
        return null;
    };

    $tidbHost = $resolveEnv(['TIDB_HOST']);
    $dbHost   = $resolveEnv(['DB_HOST', 'MYSQL_HOST', 'MYSQLHOST']);
    $dbUrl    = $resolveEnv(['DATABASE_URL', 'TIDB_URL']);

    $response = [
        'sistema' => 'MateriaX Pro - Diagnostico de Base de Datos y Persistencia',
        'ambiente' => [
            'plataforma'       => (getenv('VERCEL') || !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL'])) ? 'Vercel Serverless' : 'Servidor Local (XAMPP / Dedicado)',
            'driver_activo'    => $driver,
            'host_conectado'   => $isMySQL ? $db->hostname : '(Archivo local SQLite en contenedor)',
            'base_datos'       => $db->database,
            'persistencia'     => $isMySQL ? 'PERMANENTE (Nube)' : 'TEMPORAL (SQLite efimero)',
        ],
        'variables_entorno' => [
            'TIDB_HOST'    => $tidbHost ? 'CONFIGURADA (' . substr($tidbHost, 0, 15) . '...)' : 'NO DEFINIDA',
            'DB_HOST'      => $dbHost ? 'CONFIGURADA (' . substr($dbHost, 0, 15) . '...)' : 'NO DEFINIDA',
            'DATABASE_URL' => $dbUrl ? 'CONFIGURADA (' . substr($dbUrl, 0, 15) . '...)' : 'NO DEFINIDA',
        ],
        'estado_persistencia' => [
            'los_usuarios_se_mantienen_tras_actualizacion' => $isMySQL,
            'diagnostico' => $isMySQL
                ? 'Base de datos en la nube conectada exitosamente. Las cuentas y mensajes son 100% permanentes y no se borraran con ninguna actualizacion.'
                : 'ALERTA: Estas corriendo en SQLite efimero dentro del contenedor de Vercel. Cada vez que se sube un cambio de codigo a GitHub, Vercel crea un contenedor nuevo y borra los datos temporales. Para que las cuentas nunca mas se borren, conecta TiDB Cloud o MySQL en Vercel Settings -> Environment Variables.',
        ],
        'metricas' => [
            'total_usuarios_en_base' => $totalUsuarios,
        ],
        'ultimos_usuarios_registrados' => is_array($users) ? array_slice($users, -10) : [],
    ];

    return service('response')->setJSON($response);
});

// Rutas de Autenticación
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');
$routes->get('api/localidades', 'Auth::localidades');
$routes->get('api/localidades/(:num)', 'Auth::localidades/$1');

// Panel de Control para Empresa Registrada (Protegido con 'auth')
$routes->get('panel', 'Panel::index', ['filter' => 'auth']);

// Módulo de Gestión de Cuenta y Perfil (Protegido con 'auth')
$routes->group('perfil', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Perfil::index');
    $routes->post('actualizar', 'Perfil::actualizar');
    $routes->post('cambiar-password', 'Perfil::cambiarPassword');
});

// Módulo Funcional (Hito 2): Sedes y Plantas Industriales (Protegido con 'auth')
$routes->group('plantas', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Plantas::index');
    $routes->get('crear', 'Plantas::crear');
    $routes->post('guardar', 'Plantas::guardar');
    $routes->get('editar/(:num)', 'Plantas::editar/$1');
    $routes->post('actualizar/(:num)', 'Plantas::actualizar/$1');
    $routes->post('cambiar-estado/(:num)', 'Plantas::cambiarEstado/$1');
});

// Módulo Funcional (Hito 2): Solicitudes de Compra y Transacciones (Protegido con 'auth')
$routes->group('solicitudes', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Solicitudes::index');
    $routes->get('recibidas', 'Solicitudes::recibidas');
    $routes->get('enviadas', 'Solicitudes::enviadas');
    $routes->get('crear/(:num)', 'Solicitudes::crear/$1');
    $routes->post('guardar', 'Solicitudes::guardar');
    $routes->get('ver/(:num)', 'Solicitudes::ver/$1');
    $routes->post('responder/(:num)', 'Solicitudes::responder/$1');
    $routes->post('cancelar/(:num)', 'Solicitudes::cancelar/$1');
});

// Módulo Funcional (Hito 2): Certificados Ambientales y Huella Verde (Protegido con 'auth')
$routes->group('certificados', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Certificados::index');
    $routes->get('ver/(:segment)', 'Certificados::ver/$1');
});

// Módulo de Comunicación B2B y Mensajería entre Empresas (Protegido con 'auth')
$routes->group('mensajes', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Mensajes::index');
    $routes->post('enviar', 'Mensajes::enviar');
    $routes->get('nuevos', 'Mensajes::nuevos');
    $routes->get('conteo', 'Mensajes::conteoNoLeidos');
    $routes->get('chat/transaccion/(:num)', 'Mensajes::chatTransaccion/$1');
    $routes->get('chat/producto/(:num)/(:num)', 'Mensajes::chatProducto/$1/$2');
    $routes->get('ver/(:num)', 'Mensajes::ver/$1');
});

// Módulo Funcional: CRUD de Entidad Secundaria (Productos)
// Protegido estrictamente con el filtro de autenticación 'auth' (Solo visible para usuarios logueados)
$routes->group('productos', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Productos::index');
    $routes->get('crear', 'Productos::crear');
    $routes->post('guardar', 'Productos::guardar');
    $routes->get('ver/(:num)', 'Productos::ver/$1');
    $routes->get('editar/(:num)', 'Productos::editar/$1');
    $routes->post('actualizar/(:num)', 'Productos::actualizar/$1');
    $routes->get('confirmar-eliminar/(:num)', 'Productos::confirmarEliminar/$1');
    $routes->post('eliminar/(:num)', 'Productos::eliminar/$1');
});

// Módulo de Administración Exclusivo (Protegido estrictamente con el filtro 'admin')
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->post('empresa/aprobar/(:num)', 'Admin::aprobar/$1');
    $routes->post('empresa/rechazar/(:num)', 'Admin::rechazar/$1');
    $routes->post('empresa/cambiar-estado/(:num)', 'Admin::cambiarEstado/$1');
    $routes->get('empresa/(:num)', 'Admin::verEmpresa/$1');
    $routes->get('lotes', 'Admin::lotes');
    $routes->post('lotes/eliminar/(:num)', 'Admin::eliminarLote/$1');
});
