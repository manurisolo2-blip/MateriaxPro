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

// Módulo Funcional: CRUD de Entidad Secundaria (Productos)
// Protegido estrictamente con el filtro de autenticación 'auth' (Solo visible para usuarios logueados)
$routes->group('productos', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Productos::index');
    $routes->get('crear', 'Productos::crear');
    $routes->post('guardar', 'Productos::guardar');
    $routes->get('ver/(:num)', 'Productos::ver/$1');
    $routes->get('editar/(:num)', 'Productos::editar/$1');
    $routes->post('actualizar/(:num)', 'Productos::actualizar/$1');
    $routes->post('eliminar/(:num)', 'Productos::eliminar/$1');
});
