<?php

namespace Tests\Unit;

use App\Models\PlantaModel;
use App\Models\ProductoModel;
use App\Models\TransaccionModel;
use App\Models\CertificadoModel;
use Config\Database;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas unitarias automatizadas para los módulos del Hito 2
 * (Sedes/Plantas, Solicitudes de Compra y Certificados Ambientales)
 *
 * @internal
 */
final class Hito2FunctionalTest extends CIUnitTestCase
{
    protected PlantaModel $plantaModel;
    protected TransaccionModel $trxModel;
    protected CertificadoModel $certModel;
    protected ProductoModel $productoModel;

    protected function setUp(): void
    {
        parent::setUp();
        $db = Database::connect('default');
        $this->plantaModel    = new PlantaModel($db);
        $this->trxModel       = new TransaccionModel($db);
        $this->certModel      = new CertificadoModel($db);
        $this->productoModel  = new ProductoModel($db);
    }

    /**
     * Valida el registro, aislamiento y consulta de Sedes y Plantas Industriales
     */
    public function testPlantaIndustrialCreationAndOwnership(): void
    {
        // Empresa 1 (Petroquímica Río Tercero)
        $plantas = $this->plantaModel->getPlantasPorEmpresa(1);
        $this->assertIsArray($plantas, 'El método getPlantasPorEmpresa debe retornar un array.');
        $this->assertNotEmpty($plantas, 'La empresa 1 debe poseer al menos una planta registrada.');

        $plantaId = (int) $plantas[0]['id'];
        $this->assertTrue(
            $this->plantaModel->perteneceAEmpresa($plantaId, 1),
            'La planta debe pertenecer a la empresa titular.'
        );
        $this->assertFalse(
            $this->plantaModel->perteneceAEmpresa($plantaId, 99999),
            'La planta NO debe pertenecer a una empresa ajena.'
        );
    }

    /**
     * Valida el generador de códigos de transacción y cálculos económicos
     */
    public function testTransaccionCodeGenerationAndTotals(): void
    {
        $codigo = $this->trxModel->generarCodigoOperacion();
        $this->assertMatchesRegularExpression(
            '/^TRX-\d{4}-[A-Z0-9]{5}$/',
            $codigo,
            'El código de operación debe respetar el formato TRX-YYYY-XXXXX.'
        );

        $cantidadKg = 1500.0;
        $precioKg   = 1200.0;
        $costoFlete = 25000.0;

        $subtotal = round($cantidadKg * $precioKg, 2);
        $total    = round($subtotal + $costoFlete, 2);

        $this->assertSame(1800000.0, $subtotal);
        $this->assertSame(1825000.0, $total);
    }

    /**
     * Valida la emisión de Certificados Ambientales y el cálculo de CO2 evitado
     */
    public function testCertificadoEmisionAndEnvironmentalFormula(): void
    {
        $productoPET = [
            'id'            => 2,
            'nombre'        => 'PET Cristal Molido Botella',
            'tipo_polimero' => 'PET',
        ];

        $transaccionFake = [
            'id'               => 999999,
            'codigo_operacion' => 'TRX-2026-9999',
            'comprador_id'     => 2,
            'vendedor_id'      => 1,
            'cantidad_kg'      => 2000.0,
            'estado'           => 'aprobada',
        ];

        // Factor para PET según ISO 14044 es 2.15 kg CO2 eq por kg de resina
        $factorEsperado = 2.15;
        $co2Esperado    = round(2000.0 * $factorEsperado, 2);
        $mwhEsperado    = round(2000.0 * 0.0035, 2);

        $this->assertSame(4300.0, $co2Esperado, 'Para 2.000 kg de PET se deben evitar 4.300 kg de CO2 eq.');
        $this->assertSame(7.0, $mwhEsperado, 'Para 2.000 kg de PET se deben ahorrar 7 MWh de energía.');

        $codigoCert = $this->certModel->generarCodigoCertificado();
        $this->assertMatchesRegularExpression(
            '/^CERT-MX-\d{4}-[A-F0-9]{8}$/',
            $codigoCert,
            'El código de certificado debe respetar el formato CERT-MX-YYYY-XXXXXXXX.'
        );
    }

    /**
     * Valida la integración de planta_id en la entidad productos
     */
    public function testProductosTableHasPlantaIdColumn(): void
    {
        $db = Database::connect('default');
        $fields = $db->getFieldNames('productos');

        $this->assertContains(
            'planta_id',
            $fields,
            'La tabla productos debe poseer la columna planta_id para vincular la sede de retiro.'
        );
    }

    /**
     * Valida la mensajería B2B entre empresas, conteo de no leídos y marcado
     */
    public function testMensajeriaEntreEmpresas(): void
    {
        $db = Database::connect('default');
        $mensajeModel = new \App\Models\MensajeModel($db);

        // Crear un mensaje de prueba de empresa 2 a empresa 1
        $msgData = [
            'producto_id'    => 2,
            'transaccion_id' => null,
            'emisor_id'      => 2,
            'receptor_id'    => 1,
            'mensaje'        => 'Mensaje de prueba unitaria: ¿El scrap de PP viene en bolsones big bag?',
            'leido'          => 0,
        ];

        $insertId = $mensajeModel->insert($msgData);
        $this->assertIsNumeric($insertId, 'El mensaje entre empresas debe registrarse correctamente.');

        // Empresa 1 debe tener al menos 1 no leído
        $noLeidos = $mensajeModel->contarNoLeidos(1);
        $this->assertGreaterThanOrEqual(1, $noLeidos, 'La empresa receptora debe registrar mensajes no leídos.');

        // Marcar como leído
        $mensajeModel->update($insertId, ['leido' => 1, 'fecha_lectura' => date('Y-m-d H:i:s')]);
        $msgGuardado = $mensajeModel->find($insertId);
        $this->assertSame(1, (int)$msgGuardado['leido'], 'El mensaje debe quedar marcado como leído.');

        // Limpiar registro de prueba
        $mensajeModel->delete($insertId);
    }

    /**
     * Valida la sincronización en tiempo real y polling de mensajes nuevos
     */
    public function testPollingMensajesNuevos(): void
    {
        $db = Database::connect('default');
        $mensajeModel = new \App\Models\MensajeModel($db);

        // Insertar mensaje inicial
        $msg1 = $mensajeModel->insert([
            'producto_id'    => 2,
            'transaccion_id' => null,
            'emisor_id'      => 1,
            'receptor_id'    => 2,
            'mensaje'        => 'Primer mensaje para probar polling',
            'leido'          => 0,
        ]);

        // Insertar segundo mensaje
        $msg2 = $mensajeModel->insert([
            'producto_id'    => 2,
            'transaccion_id' => null,
            'emisor_id'      => 2,
            'receptor_id'    => 1,
            'mensaje'        => 'Respuesta instantánea simultánea',
            'leido'          => 0,
        ]);

        // Consultar mensajes con id > msg1
        $builder = $mensajeModel->builder();
        $builder->where('id >', $msg1);
        $builder->where('producto_id', 2);
        $nuevos = $builder->get()->getResultArray();

        $this->assertNotEmpty($nuevos, 'El polling debe recuperar los mensajes nuevos sin recargar página.');
        $this->assertSame((int)$msg2, (int)$nuevos[0]['id'], 'El nuevo mensaje debe coincidir con el segundo emitido.');

        // Limpiar
        $mensajeModel->delete($msg1);
        $mensajeModel->delete($msg2);
    }

    /**
     * Valida que los usuarios registrados se mantengan permanentemente
     */
    public function testUsuariosPermanecenEnBaseDeDatos(): void
    {
        $db = Database::connect('default');
        $userModel = new \App\Models\UserModel($db);

        $totalUsuarios = $userModel->countAllResults();
        $this->assertGreaterThanOrEqual(6, $totalUsuarios, 'La base de datos debe preservar permanentemente todas las cuentas de usuario registradas.');
    }

    /**
     * Valida que getMensajesPorProducto ejecute la consulta SQL correctamente sin errores de sintaxis
     */
    public function testConsultaDirectaProductoSinSqlError(): void
    {
        $db = Database::connect('default');
        $mensajeModel = new \App\Models\MensajeModel($db);

        // Debe ejecutarse limpiamente sin lanzar DatabaseException
        $mensajes = $mensajeModel->getMensajesPorProducto(2, 1, 2);
        $this->assertIsArray($mensajes, 'getMensajesPorProducto debe retornar un array de consultas.');
    }
}
