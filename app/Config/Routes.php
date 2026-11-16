<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('estado-db', static function () {
    $db = \Config\Database::connect();
    try {
        $users = $db->table('usuarios')->select('id, nombre, email, rol, estado, created_at')->get()->getResultArray();
        $total = count($users);
        $ok    = true;
    } catch (\Throwable $e) {
        $users = ['error' => $e->getMessage()];
        $total = 0;
        $ok    = false;
    }

    $driver     = $db->DBDriver;
    $persistent = in_array($driver, ['Postgre', 'MySQLi'], true);
    $engineName = $driver === 'Postgre' ? 'Supabase PostgreSQL' : 'MySQL';
    $prevAlert  = \Config\Database::resolveEnv(['MATERIAX_PG_ERROR', 'MATERIAX_CLOUD_ERROR']);
    $envCheck   = static fn (array $keys, int $len = 18): string => ($v = \Config\Database::resolveEnv($keys))
        ? 'CONFIGURADA (' . substr($v, 0, $len) . '...)'
        : 'NO DEFINIDA';

    return service('response')->setJSON([
        'sistema'  => 'MateriaX Pro - Diagnostico de Base de Datos y Persistencia',
        'ambiente' => [
            'plataforma'     => \Config\Database::resolveEnv(['VERCEL']) ? 'Vercel Serverless' : 'Servidor Local (XAMPP / Dedicado)',
            'driver_activo'  => $driver,
            'host_conectado' => $persistent ? $db->hostname : '(Archivo local SQLite en contenedor)',
            'base_datos'     => $db->database,
            'persistencia'   => $persistent ? "PERMANENTE (Nube - {$engineName})" : 'TEMPORAL (SQLite de respaldo)',
        ],
        'variables_entorno' => [
            'POSTGRES_URL'  => $envCheck(['POSTGRES_URL_NON_POOLING', 'POSTGRES_URL', 'SUPABASE_DB_URL']),
            'POSTGRES_HOST' => $envCheck(['POSTGRES_HOST', 'SUPABASE_HOST']),
            'DATABASE_URL'  => $envCheck(['DATABASE_URL', 'TIDB_URL']),
            'TIDB_HOST'     => $envCheck(['TIDB_HOST'], 15),
            'DB_HOST'       => $envCheck(['DB_HOST', 'MYSQL_HOST', 'MYSQLHOST'], 15),
        ],
        'estado_persistencia' => [
            'operativa'                                    => $ok,
            'los_usuarios_se_mantienen_tras_actualizacion' => $persistent,
            'alerta_conexion_previa'                       => $prevAlert,
            'diagnostico' => $persistent
                ? "Base de datos en la nube ({$engineName}) conectada exitosamente. Las cuentas, lotes y mensajes son 100% permanentes y no se borraran con ninguna actualizacion."
                : ($prevAlert
                    ? 'Respaldo SQLite activo: PostgreSQL/MySQL en la nube no responde (proyecto Supabase pausado, eliminado o credenciales desactualizadas). El sistema sigue funcionando en SQLite temporal; actualiza POSTGRES_URL o reactiva el proyecto en Supabase para persistencia permanente.'
                    : 'Operando en SQLite local/temporal. Conecta Supabase o MySQL en Vercel Environment Variables para persistencia permanente en la nube.'),
        ],
        'metricas'                     => ['total_usuarios_en_base' => $total],
        'ultimos_usuarios_registrados' => is_array($users) && $ok ? array_slice($users, -10) : $users,
    ]);
});

// Autenticación y registro
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');
$routes->get('api/localidades', 'Auth::localidades');
$routes->get('api/localidades/(:num)', 'Auth::localidades/$1');

$routes->get('panel', 'Panel::index', ['filter' => 'auth']);

$routes->group('perfil', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Perfil::index');
    $routes->post('actualizar', 'Perfil::actualizar');
    $routes->post('cambiar-password', 'Perfil::cambiarPassword');
});

$routes->group('plantas', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Plantas::index');
    $routes->get('crear', 'Plantas::crear');
    $routes->post('guardar', 'Plantas::guardar');
    $routes->get('editar/(:num)', 'Plantas::editar/$1');
    $routes->post('actualizar/(:num)', 'Plantas::actualizar/$1');
    $routes->post('cambiar-estado/(:num)', 'Plantas::cambiarEstado/$1');
});

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

$routes->group('certificados', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Certificados::index');
    $routes->get('ver/(:segment)', 'Certificados::ver/$1');
});

$routes->group('mensajes', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Mensajes::index');
    $routes->post('enviar', 'Mensajes::enviar');
    $routes->get('nuevos', 'Mensajes::nuevos');
    $routes->get('bandeja', 'Mensajes::bandeja');
    $routes->get('conteo', 'Mensajes::conteoNoLeidos');
    $routes->get('chat/transaccion/(:num)', 'Mensajes::chatTransaccion/$1');
    $routes->get('chat/producto/(:num)/(:num)', 'Mensajes::chatProducto/$1/$2');
    $routes->get('ver/(:num)', 'Mensajes::ver/$1');
});

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

$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->post('empresa/aprobar/(:num)', 'Admin::aprobar/$1');
    $routes->post('empresa/rechazar/(:num)', 'Admin::rechazar/$1');
    $routes->post('empresa/cambiar-estado/(:num)', 'Admin::cambiarEstado/$1');
    $routes->get('empresa/(:num)', 'Admin::verEmpresa/$1');
    $routes->get('lotes', 'Admin::lotes');
    $routes->post('lotes/eliminar/(:num)', 'Admin::eliminarLote/$1');
});
