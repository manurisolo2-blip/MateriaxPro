<?php

namespace Tests\Unit;

use App\Filters\AdminFilter;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas unitarias para el módulo de administración exclusiva y AdminFilter
 *
 * @internal
 */
final class AdminModuleTest extends CIUnitTestCase
{
    /**
     * Verifica que el usuario administrador exista en la base de datos principal
     * con rol 'admin', estado 'activo' y que la contraseña 'Bautiprouwu123' sea válida
     */
    public function testAdminUserCredentialsExistAndAreValid(): void
    {
        $db = \Config\Database::connect('default');
        $admin = $db->table('usuarios')->where('email', 'myadminpro@gmail.com')->get()->getRowArray();

        $this->assertNotNull($admin, 'El usuario administrador myadminpro@gmail.com debe existir en la base de datos MySQL.');
        $this->assertSame('admin', $admin['rol'], 'El usuario debe poseer rol exclusivo admin.');
        $this->assertSame('activo', $admin['estado'], 'El usuario admin debe estar en estado activo.');
        $this->assertTrue(
            password_verify('Bautiprouwu123', $admin['password']),
            'La contraseña Bautiprouwu123 debe ser verificada exitosamente contra el hash bcrypt.'
        );
    }

    /**
     * Verifica que AdminFilter bloquee a usuarios invitados (sin sesión) y los redirija a login
     */
    public function testAdminFilterRejectsGuestUser(): void
    {
        $session = Services::session();
        $session->set(['isLoggedIn' => false]);
        $session->remove(['rol', 'user_id', 'nombre']);

        $filter = new AdminFilter();
        $request = new IncomingRequest(config('App'), new URI('http://localhost/admin'), null, new UserAgent());

        $response = $filter->before($request);

        $this->assertInstanceOf(RedirectResponse::class, $response, 'AdminFilter debe responder con RedirectResponse para usuarios sin sesión.');
    }

    /**
     * Verifica que AdminFilter bloquee a empresas ordinarias (rol = 'empresa')
     */
    public function testAdminFilterRejectsRegularCompanyUser(): void
    {
        $session = Services::session();
        $session->set([
            'user_id'    => 1,
            'nombre'     => 'Empresa de Prueba S.A.',
            'email'      => 'empresa@test.com',
            'rol'        => 'empresa',
            'isLoggedIn' => true,
        ]);

        $filter = new AdminFilter();
        $request = new IncomingRequest(config('App'), new URI('http://localhost/admin'), null, new UserAgent());

        $response = $filter->before($request);

        $this->assertInstanceOf(RedirectResponse::class, $response, 'AdminFilter debe responder con RedirectResponse para rol no admin.');
    }

    /**
     * Verifica que AdminFilter permita el acceso sin redirección a usuarios con rol 'admin'
     */
    public function testAdminFilterAllowsAdminUser(): void
    {
        $session = Services::session();
        $session->set([
            'user_id'    => 4,
            'nombre'     => 'Administrador General',
            'email'      => 'myadminpro@gmail.com',
            'rol'        => 'admin',
            'isLoggedIn' => true,
        ]);

        $filter = new AdminFilter();
        $request = new IncomingRequest(config('App'), new URI('http://localhost/admin'), null, new UserAgent());

        $response = $filter->before($request);

        $this->assertNull($response, 'AdminFilter debe retornar null para permitir el paso a usuarios admin.');
    }
}
