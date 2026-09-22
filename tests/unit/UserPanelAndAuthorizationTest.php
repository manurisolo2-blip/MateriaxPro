<?php

namespace Tests\Unit;

use App\Controllers\Auth;
use App\Controllers\Productos;
use App\Controllers\Panel;
use App\Filters\AdminFilter;
use App\Models\UserModel;
use App\Models\ProductoModel;
use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas automatizadas para el Panel de Empresa y Controles Estrictos de Autorización
 *
 * @internal
 */
final class UserPanelAndAuthorizationTest extends CIUnitTestCase
{
    private UserModel $userModel;
    private ProductoModel $productoModel;

    protected function setUp(): void
    {
        parent::setUp();
        $db = Database::connect('default');
        $this->userModel = new UserModel($db);
        $this->userModel->skipValidation(true);
        $this->productoModel = new ProductoModel($db);
        $this->productoModel->skipValidation(true);
    }

    /**
     * Verifica que el login con rol empresa redirija hacia /panel
     */
    public function testCompanyLoginRedirectsToUserPanel(): void
    {
        $testEmail = 'panel_empresa_' . time() . '@empresa.com';
        $testPass  = 'EmpresaPass123';

        $userId = $this->userModel->insert([
            'nombre'    => 'Empresa Panel Test S.A.',
            'email'     => $testEmail,
            'password'  => password_hash($testPass, PASSWORD_BCRYPT),
            'cuit'      => '30-11223344-5',
            'telefono'  => '3571-444444',
            'rubro'     => 'Extrusión',
            'ciudad'    => 'Río Tercero',
            'provincia' => 'Córdoba',
            'direccion' => 'Calle Falsa 123',
            'rol'       => 'empresa',
            'estado'    => 'activo',
        ]);

        $request = Services::request();
        $request->setGlobal('request', [
            'email'    => $testEmail,
            'password' => $testPass,
        ]);
        $request->setGlobal('post', [
            'email'    => $testEmail,
            'password' => $testPass,
        ]);
        
        $controller = new Auth();
        $this->setPrivateProperty($controller, 'userModel', $this->userModel);
        $controller->initController($request, Services::response(), Services::logger());

        $response = $controller->attemptLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('panel', (string) $response->getHeaderLine('Location'));
        $this->assertSame('empresa', session()->get('rol'));

        // Limpieza
        unset($_POST['email'], $_POST['password']);
        $this->userModel->delete($userId);
    }

    /**
     * Verifica que una empresa NO pueda editar un producto que no le pertenece
     */
    public function testCompanyCannotEditAnotherCompanysProduct(): void
    {
        // 1. Crear producto propiedad del usuario 1
        $prodId = $this->productoModel->insert([
            'user_id'         => 1, // Pertenece a usuario 1
            'nombre'          => 'Lote Ajeno Test ' . time(),
            'tipo_polimero'   => 'Polietileno (PE)',
            'cantidad_kg'     => 500,
            'precio_unitario' => 1200,
            'ubicacion'       => 'Río Tercero',
            'estado'          => 'Disponible',
        ]);

        // 2. Simular sesión de otra empresa (usuario 2)
        session()->set([
            'user_id'    => 99999, // Otro usuario
            'nombre'     => 'Empresa Intrusiva',
            'email'      => 'intruso@empresa.com',
            'rol'        => 'empresa',
            'isLoggedIn' => true,
        ]);

        $request = Services::request();
        $controller = new Productos();
        $this->setPrivateProperty($controller, 'productoModel', $this->productoModel);
        $controller->initController($request, Services::response(), Services::logger());

        // Intentar editar
        $response = $controller->editar($prodId);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('productos', (string) $response->getHeaderLine('Location'));
        $this->assertNotEmpty(session()->getFlashdata('error'), 'Debe existir un mensaje de error por denegación de acceso.');

        // Limpieza
        $this->productoModel->delete($prodId);
    }

    /**
     * Verifica que una empresa NO pueda eliminar un producto que no le pertenece
     */
    public function testCompanyCannotDeleteAnotherCompanysProduct(): void
    {
        // 1. Crear producto propiedad del usuario 1
        $prodId = $this->productoModel->insert([
            'user_id'         => 1,
            'nombre'          => 'Lote Ajeno No Borrable ' . time(),
            'tipo_polimero'   => 'Polipropileno (PP)',
            'cantidad_kg'     => 1000,
            'precio_unitario' => 1500,
            'ubicacion'       => 'Córdoba',
            'estado'          => 'Disponible',
        ]);

        // 2. Simular sesión de empresa con otro ID
        session()->set([
            'user_id'    => 88888,
            'nombre'     => 'Empresa B',
            'email'      => 'empresaB@test.com',
            'rol'        => 'empresa',
            'isLoggedIn' => true,
        ]);

        $request = Services::request();
        $controller = new Productos();
        $this->setPrivateProperty($controller, 'productoModel', $this->productoModel);
        $controller->initController($request, Services::response(), Services::logger());

        // Intentar eliminar
        $response = $controller->eliminar($prodId);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('productos', (string) $response->getHeaderLine('Location'));
        $this->assertNotEmpty(session()->getFlashdata('error'));

        // Verificar que el producto sigue existiendo intacto en la base de datos
        $producto = $this->productoModel->find($prodId);
        $this->assertNotNull($producto, 'El producto ajeno NO debe haber sido eliminado.');

        // Limpieza
        $this->productoModel->delete($prodId);
    }

    /**
     * Verifica que un usuario con rol 'empresa' sea bloqueado al intentar acceder a rutas admin
     */
    public function testCompanyCannotAccessAdminRoutes(): void
    {
        session()->set([
            'user_id'    => 12345,
            'nombre'     => 'Empresa Regular',
            'email'      => 'regular@empresa.com',
            'rol'        => 'empresa',
            'isLoggedIn' => true,
        ]);

        $filter = new AdminFilter();
        $response = $filter->before(Services::request());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('productos', (string) $response->getHeaderLine('Location'));
        $this->assertNotEmpty(session()->getFlashdata('error'), 'Debe alertar que la sección es exclusiva para el administrador.');
    }
}
