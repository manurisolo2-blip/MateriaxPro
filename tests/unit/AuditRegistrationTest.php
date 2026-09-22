<?php

namespace Tests\Unit;

use App\Controllers\Admin;
use App\Models\UserModel;
use Config\Database;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas automatizadas para el flujo de auditoría y aprobación de empresas
 *
 * @internal
 */
final class AuditRegistrationTest extends CIUnitTestCase
{
    private UserModel $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $db = Database::connect('default');
        $this->userModel = new UserModel($db);
        $this->userModel->skipValidation(true);
    }

    /**
     * Verifica que una nueva empresa registrada se guarde con estado 'pendiente'
     */
    public function testNewCompanyRegistrationIsPending(): void
    {
        $testEmail = 'auditoria_test_' . time() . '@empresa.com';
        $testCuit  = '30-' . rand(10000000, 99999999) . '-1';

        $userId = $this->userModel->insert([
            'nombre'    => 'Empresa Aspirante S.A.',
            'email'     => $testEmail,
            'password'  => password_hash('clave123', PASSWORD_BCRYPT),
            'cuit'      => $testCuit,
            'telefono'  => '3571-555555',
            'rubro'     => 'Moldeo por Inyección',
            'ciudad'    => 'Río Tercero',
            'provincia' => 'Córdoba',
            'direccion' => 'Parque Industrial Sur',
            'rol'       => 'empresa',
            'estado'    => 'pendiente',
        ]);

        $this->assertIsInt($userId, 'El usuario debe registrarse exitosamente.');
        
        $saved = $this->userModel->find($userId);
        $this->assertSame('pendiente', $saved['estado'], 'La nueva cuenta debe iniciar en estado pendiente de auditoría.');

        // Limpieza
        $this->userModel->delete($userId);
    }

    /**
     * Verifica que el Administrador pueda aprobar una cuenta pendiente cambiando su estado a 'activo'
     */
    public function testAdminCanApprovePendingCompany(): void
    {
        $testEmail = 'aprobar_test_' . time() . '@empresa.com';
        $testCuit  = '30-' . rand(10000000, 99999999) . '-2';

        $userId = $this->userModel->insert([
            'nombre'    => 'Empresa para Aprobar S.A.',
            'email'     => $testEmail,
            'password'  => password_hash('clave123', PASSWORD_BCRYPT),
            'cuit'      => $testCuit,
            'telefono'  => '3571-444444',
            'rol'       => 'empresa',
            'estado'    => 'pendiente',
        ]);

        // Simular sesión del Administrador
        $session = Services::session();
        $session->set([
            'user_id'    => 4,
            'nombre'     => 'Administrador General',
            'email'      => 'myadminpro@gmail.com',
            'rol'        => 'admin',
            'isLoggedIn' => true,
        ]);

        $adminController = new Admin();
        $this->setPrivateProperty($adminController, 'userModel', $this->userModel);
        
        $response = $adminController->aprobar($userId);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        // Verificar cambio en base de datos
        $approved = $this->userModel->find($userId);
        $this->assertSame('activo', $approved['estado'], 'La empresa aprobada debe poseer estado activo.');

        // Limpieza
        $this->userModel->delete($userId);
    }

    /**
     * Verifica que el Administrador pueda rechazar una solicitud de empresa
     */
    public function testAdminCanRejectPendingCompany(): void
    {
        $testEmail = 'rechazar_test_' . time() . '@empresa.com';
        $testCuit  = '30-' . rand(10000000, 99999999) . '-3';

        $userId = $this->userModel->insert([
            'nombre'    => 'Empresa Dudosa S.A.',
            'email'     => $testEmail,
            'password'  => password_hash('clave123', PASSWORD_BCRYPT),
            'cuit'      => $testCuit,
            'telefono'  => '3571-333333',
            'rol'       => 'empresa',
            'estado'    => 'pendiente',
        ]);

        $adminController = new Admin();
        $this->setPrivateProperty($adminController, 'userModel', $this->userModel);

        $response = $adminController->rechazar($userId);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        // Verificar cambio en base de datos
        $rejected = $this->userModel->find($userId);
        $this->assertSame('rechazado', $rejected['estado'], 'La empresa rechazada debe poseer estado rechazado.');

        // Limpieza
        $this->userModel->delete($userId);
    }
}
