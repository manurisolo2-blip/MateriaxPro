<?php

namespace Tests\Unit;

use App\Controllers\Auth;
use App\Models\LocalidadModel;
use App\Models\UserModel;
use App\Validation\EmpresaRules;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Pruebas para validaciones realistas de registro empresarial,
 * consistencia provincia/localidad y ausencia de scripts en vistas core.
 *
 * @internal
 */
final class RegistrationValidationTest extends CIUnitTestCase
{
    public function testEmpresaRulesRejectBogusAndAcceptValidInputs(): void
    {
        $rules = new EmpresaRules();

        // CUIT / CUIL válidos (módulo 11)
        $this->assertTrue($rules->valid_cuit('30-70308853-4'));
        $this->assertTrue($rules->valid_cuit('23492887269'));
        $this->assertTrue($rules->valid_cuit('33-69345023-9'));
        $this->assertSame('23-49288726-9', EmpresaRules::formatCuit('23492887269'));

        // CUIT / CUIL falsos o erróneos
        $this->assertFalse($rules->valid_cuit('30-12345678-9'));
        $this->assertFalse($rules->valid_cuit('20-11111111-1'));
        $this->assertFalse($rules->valid_cuit('20-00000000-0'));
        $this->assertFalse($rules->valid_cuit('99-70308853-4'));
        $this->assertFalse($rules->valid_cuit('30-70308853-5')); // dígito verificador incorrecto
        $this->assertFalse($rules->valid_cuit('abcdef12345'));

        // Teléfonos válidos e inválidos
        $this->assertTrue($rules->valid_telefono('3571 421500'));
        $this->assertTrue($rules->valid_telefono('+54 9 351 5551234'));
        $this->assertTrue($rules->valid_telefono('(03571) 435390'));
        $this->assertFalse($rules->valid_telefono('123456'));
        $this->assertFalse($rules->valid_telefono('1111111111'));
        $this->assertFalse($rules->valid_telefono('3571-123456'));
        $this->assertFalse($rules->valid_telefono('3571-000000'));
        $this->assertFalse($rules->valid_telefono('abc3571421500'));

        // Razón social y domicilio
        $this->assertTrue($rules->valid_razon_social('Industrias Plásticas Río III S.A.'));
        $this->assertFalse($rules->valid_razon_social('111111'));
        $this->assertFalse($rules->valid_razon_social('aaaaaa'));
        $this->assertTrue($rules->valid_direccion('Av. Savio 1420'));
        $this->assertFalse($rules->valid_direccion('aaa'));
        $this->assertFalse($rules->valid_direccion('123456'));

        // Email y contraseña fuerte
        $this->assertTrue($rules->valid_email_corporativo('compras@polimeros.com.ar'));
        $this->assertFalse($rules->valid_email_corporativo('admin@localhost'));
        $this->assertTrue($rules->valid_password_fuerte('Plastico2026'));
        $this->assertFalse($rules->valid_password_fuerte('12345678'));
        $this->assertFalse($rules->valid_password_fuerte('abcdefgh'));
    }

    public function testLocalidadModelFiltersJunkAndResolvesProvinceCity(): void
    {
        $model = new LocalidadModel();

        // Ninguna localidad puramente numérica debe exponerse
        foreach ($model->getTodasConProvincia() as $loc) {
            $this->assertDoesNotMatchRegularExpression('/^\d+$/', (string) $loc['localidad']);
        }

        // Búsqueda en Córdoba debe incluir todas (>100) y resolver sin tildes
        $cordoba = $model->buscar(null, null, 'Córdoba', 0);
        $this->assertGreaterThan(200, count($cordoba));

        $resolved = $model->resolverEnProvincia('Córdoba', 'rio tercero');
        $this->assertSame(['provincia' => 'Córdoba', 'ciudad' => 'Río Tercero'], $resolved);

        // Ciudad de otra provincia o inventada debe fallar
        $this->assertNull($model->resolverEnProvincia('Santa Fe', 'Río Tercero'));
        $this->assertNull($model->resolverEnProvincia('Córdoba', 'riotercrereo'));
        $this->assertNull($model->resolverEnProvincia('Córdoba', '7'));
    }

    public function testRegisterViewDoesNotLeakScriptToWelcomeMessage(): void
    {
        $locModel = new LocalidadModel();
        $regHtml  = view('auth/register', [
            'provincias'        => $locModel->getProvincias(),
            'todasLocalidades'  => [],
            'selectedProvincia' => '',
            'selectedCiudad'    => '',
        ]);
        $this->assertStringContainsString('localidades.js', $regHtml);
        $this->assertStringNotContainsString('site-footer', $regHtml);

        $welcomeHtml = view('welcome_message', ['pageTitle' => 'MateriaX']);
        $this->assertStringNotContainsStringIgnoringCase('<script', $welcomeHtml);
        $this->assertStringNotContainsString('site-footer', $welcomeHtml);
    }

    protected function tearDown(): void
    {
        $_POST    = [];
        $_REQUEST = [];
        Services::reset(true);
        parent::tearDown();
    }

    public function testAttemptRegisterRejectsBogusDataAndPersistsNormalizedValidCompany(): void
    {
        $userModel = new UserModel(Database::connect('default'));
        $testEmail = 'reg_valid_' . time() . '_' . rand(100, 999) . '@empresa.com.ar';

        // 1. Envío con datos inválidos -> rechazado con errores de validación
        $badPost = [
            'nombre'       => '1111',
            'cuit'         => '20-11111111-1',
            'rubro'        => 'Rotomoldeo',
            'email'        => 'malo@localhost',
            'telefono'     => '123456',
            'provincia'    => 'Santa Fe',
            'ciudad'       => 'Río Tercero',
            'direccion'    => 'aaa',
            'password'     => '12345678',
            'pass_confirm' => '12345678',
        ];
        $_POST    = $badPost;
        $_REQUEST = $badPost;
        Services::validation()->reset();
        $reqBad = Services::request();
        $reqBad->setMethod('post')->setGlobal('post', $badPost)->setGlobal('request', $badPost);
        $authBad = new Auth();
        $this->setPrivateProperty($authBad, 'userModel', $userModel);
        $authBad->initController($reqBad, Services::response(), Services::logger());

        $badResp = $authBad->attemptRegister();
        $this->assertInstanceOf(RedirectResponse::class, $badResp);
        $errors = session()->getFlashdata('errors') ?? [];
        foreach (['nombre', 'cuit', 'email', 'telefono', 'ciudad', 'direccion', 'password'] as $f) {
            $this->assertArrayHasKey($f, $errors, "Debe validar el campo {$f}");
        }
        $this->assertNull($userModel->findByEmail('malo@localhost'));

        // 2. Envío válido -> normaliza CUIT y localidad y guarda como pendiente
        $okPost = [
            'nombre'       => '  Polímeros   Río Tercero S.R.L.  ',
            'cuit'         => '30703088534',
            'rubro'        => 'Rotomoldeo',
            'email'        => $testEmail,
            'telefono'     => '3571 421500',
            'provincia'    => 'Córdoba',
            'ciudad'       => 'rio tercero',
            'direccion'    => 'Parque Industrial Norte Lote 4',
            'password'     => 'Polimero2026',
            'pass_confirm' => 'Polimero2026',
        ];
        $_POST    = $okPost;
        $_REQUEST = $okPost;
        Services::validation()->reset();
        $reqOk = Services::request();
        $reqOk->setMethod('post')->setGlobal('post', $okPost)->setGlobal('request', $okPost);
        $authOk = new Auth();
        $this->setPrivateProperty($authOk, 'userModel', $userModel);
        $authOk->initController($reqOk, Services::response(), Services::logger());

        $okResp = $authOk->attemptRegister();
        $this->assertInstanceOf(RedirectResponse::class, $okResp);
        $this->assertStringContainsString('login', (string) $okResp->getHeaderLine('Location'));

        $saved = $userModel->findByEmail($testEmail);
        $this->assertNotNull($saved, 'La empresa válida debe registrarse.');
        $this->assertSame('Polímeros Río Tercero S.R.L.', $saved['nombre']);
        $this->assertSame('30-70308853-4', $saved['cuit']);
        $this->assertSame('Córdoba', $saved['provincia']);
        $this->assertSame('Río Tercero', $saved['ciudad']);
        $this->assertSame('pendiente', $saved['estado']);

        $userModel->delete((int) $saved['id']);
    }
}
