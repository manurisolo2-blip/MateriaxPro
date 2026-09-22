<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rutas Públicas
$routes->get('/', 'Home::index');

// Rutas de Autenticación
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');

// Módulo de Gestión de Cuenta y Perfil (Protegido con 'auth')
$routes->group('perfil', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Perfil::index');
    $routes->post('actualizar', 'Perfil::actualizar');
    $routes->post('cambiar-password', 'Perfil::cambiarPassword');
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
    $routes->post('empresa/cambiar-estado/(:num)', 'Admin::cambiarEstado/$1');
    $routes->get('empresa/(:num)', 'Admin::verEmpresa/$1');
    $routes->get('lotes', 'Admin::lotes');
    $routes->post('lotes/eliminar/(:num)', 'Admin::eliminarLote/$1');
});

