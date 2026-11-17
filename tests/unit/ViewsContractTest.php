<?php

namespace Tests\Unit;

use App\Controllers\{Mensajes, Productos, Solicitudes};
use App\Models\{CertificadoModel, MensajeModel, PlantaModel, ProductoModel, TransaccionModel, UserModel};
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\{Database, Services};

/**
 * Verifica que las vistas de Solicitudes, Productos y Mensajes reciban
 * todas las variables esperadas sin errores de variables o claves indefinidas.
 *
 * @internal
 */
final class ViewsContractTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect('tests');
        $p        = $this->db->DBPrefix;
        $this->db->query("CREATE TABLE IF NOT EXISTS {$p}usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT, email TEXT, password TEXT, cuit TEXT, telefono TEXT,
            rubro TEXT, ciudad TEXT, provincia TEXT, direccion TEXT,
            rol TEXT DEFAULT 'empresa', estado TEXT DEFAULT 'activo',
            created_at DATETIME, updated_at DATETIME, ultimo_login DATETIME
        )");
        $this->db->query("CREATE TABLE IF NOT EXISTS {$p}plantas_industriales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            empresa_id INTEGER, nombre_planta TEXT, direccion TEXT, localidad TEXT,
            provincia TEXT, coordenadas_gps TEXT, posee_bascula INTEGER DEFAULT 1,
            capacidad_carga_camiones TEXT, contacto_responsable TEXT, telefono_planta TEXT,
            activa INTEGER DEFAULT 1, created_at DATETIME, updated_at DATETIME
        )");
        $this->db->query("CREATE TABLE IF NOT EXISTS {$p}productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER, planta_id INTEGER, nombre TEXT, tipo_polimero TEXT,
            cantidad_kg REAL, precio_unitario REAL, ubicacion TEXT, descripcion TEXT,
            estado TEXT DEFAULT 'Disponible', created_at DATETIME, updated_at DATETIME
        )");
        $this->db->query("CREATE TABLE IF NOT EXISTS {$p}transacciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_operacion TEXT, producto_id INTEGER, comprador_id INTEGER, vendedor_id INTEGER,
            planta_origen_id INTEGER, cantidad_kg REAL, precio_unitario_pactado REAL,
            subtotal REAL, costo_flete REAL DEFAULT 0, monto_total REAL, modalidad_retiro TEXT,
            estado TEXT DEFAULT 'solicitada', ticket_balanza_kg REAL, numero_remito TEXT,
            observaciones TEXT, fecha_solicitud DATETIME, fecha_acuerdo DATETIME,
            fecha_despacho DATETIME, fecha_cierre DATETIME
        )");
        $this->db->query("CREATE TABLE IF NOT EXISTS {$p}certificados_ambientales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_certificado TEXT, transaccion_id INTEGER, generadora_id INTEGER,
            revalorizadora_id INTEGER, kg_recuperados REAL, co2_evitado_kg REAL,
            mwh_ahorrado REAL, norma_referencia TEXT, fecha_emision DATETIME,
            firmado_digitalmente INTEGER DEFAULT 1
        )");
        $this->db->query("CREATE TABLE IF NOT EXISTS {$p}mensajes_negociacion (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            producto_id INTEGER, transaccion_id INTEGER, emisor_id INTEGER,
            receptor_id INTEGER, mensaje TEXT, leido INTEGER DEFAULT 0,
            fecha_lectura DATETIME, created_at DATETIME
        )");

        foreach (['mensajes_negociacion', 'certificados_ambientales', 'transacciones', 'productos', 'plantas_industriales', 'usuarios'] as $t) {
            $this->db->table($t)->emptyTable();
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('usuarios')->insertBatch([
            ['id' => 1, 'nombre' => 'Vendedor S.A.', 'email' => 'vendedor@test.com', 'password' => 'x', 'cuit' => '30-11111111-1', 'telefono' => '3571-111111', 'rol' => 'empresa', 'estado' => 'activo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nombre' => 'Comprador S.A.', 'email' => 'comprador@test.com', 'password' => 'x', 'cuit' => '30-22222222-2', 'telefono' => '3571-222222', 'rol' => 'empresa', 'estado' => 'activo', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->table('productos')->insert([
            'id' => 10, 'user_id' => 1, 'nombre' => 'PP Recuperado Lote 10', 'tipo_polimero' => 'Polipropileno (PP)',
            'cantidad_kg' => 1200, 'precio_unitario' => 1500, 'ubicacion' => 'Río Tercero, Córdoba',
            'descripcion' => 'Molienda limpia', 'estado' => 'Disponible', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->db->table('transacciones')->insert([
            'id' => 20, 'codigo_operacion' => 'TRX-2026-AB12C', 'producto_id' => 10,
            'comprador_id' => 2, 'vendedor_id' => 1, 'cantidad_kg' => 1000,
            'precio_unitario_pactado' => 1500, 'subtotal' => 1500000, 'costo_flete' => 0,
            'monto_total' => 1500000, 'modalidad_retiro' => 'retiro_comprador_planta',
            'estado' => 'solicitada', 'fecha_solicitud' => $now,
        ]);
        $this->db->table('mensajes_negociacion')->insertBatch([
            ['producto_id' => 10, 'transaccion_id' => null, 'emisor_id' => 2, 'receptor_id' => 1, 'mensaje' => '¿Tienen stock disponible?', 'leido' => 0, 'created_at' => $now],
            ['producto_id' => 10, 'transaccion_id' => 20, 'emisor_id' => 2, 'receptor_id' => 1, 'mensaje' => 'Coordinemos el retiro.', 'leido' => 0, 'created_at' => $now],
        ]);
    }

    public function testAceptarSolicitudYVerFichaNoLanzaUndefinedVariable(): void
    {
        session()->set(['user_id' => 1, 'isLoggedIn' => true, 'rol' => 'empresa']);

        $request = Services::request();
        $request->setGlobal('post', ['accion' => 'aceptar']);

        $controller = new Solicitudes();
        $this->setPrivateProperty($controller, 'transaccionModel', new TransaccionModel($this->db));
        $this->setPrivateProperty($controller, 'productoModel', new ProductoModel($this->db));
        $this->setPrivateProperty($controller, 'userModel', new UserModel($this->db));
        $this->setPrivateProperty($controller, 'certificadoModel', new CertificadoModel($this->db));
        $this->setPrivateProperty($controller, 'mensajeModel', new MensajeModel($this->db));
        $controller->initController($request, Services::response(), Services::logger());

        $resp = $controller->responder(20);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertStringContainsString('solicitudes/ver/20', $resp->getHeaderLine('Location'));

        $html = $controller->ver(20);
        $this->assertIsString($html);
        $this->assertStringContainsString('TRX-2026-AB12C', $html);
        $this->assertStringContainsString('Aprobada (Reservado)', $html);
        $this->assertStringContainsString('CERT-MX-', $html);
        $this->assertStringContainsString('Coordinemos el retiro.', $html);
    }

    public function testProductoVerComoVendedorYComprador(): void
    {
        $controller = new Productos();
        $this->setPrivateProperty($controller, 'productoModel', new ProductoModel($this->db));
        $this->setPrivateProperty($controller, 'plantaModel', new PlantaModel($this->db));
        $controller->initController(Services::request(), Services::response(), Services::logger());

        session()->set(['user_id' => 1, 'isLoggedIn' => true, 'rol' => 'empresa']);
        $htmlOwner = $controller->ver(10);
        $this->assertStringContainsString('Editar Lote', $htmlOwner);
        $this->assertStringContainsString('Comprador S.A.', $htmlOwner);
        $this->assertStringContainsString('¿Tienen stock disponible?', $htmlOwner);

        session()->set(['user_id' => 2, 'isLoggedIn' => true, 'rol' => 'empresa']);
        $htmlBuyer = $controller->ver(10);
        $this->assertStringContainsString('Solicitar Compra', $htmlBuyer);
        $this->assertStringContainsString('Consultas Directas al Vendedor', $htmlBuyer);
    }

    public function testMensajesBandejaYChatsRenderizanSinRutasRotas(): void
    {
        session()->set(['user_id' => 1, 'isLoggedIn' => true, 'rol' => 'empresa']);

        $controller = new Mensajes();
        $this->setPrivateProperty($controller, 'mensajeModel', new MensajeModel($this->db));
        $this->setPrivateProperty($controller, 'transaccionModel', new TransaccionModel($this->db));
        $this->setPrivateProperty($controller, 'productoModel', new ProductoModel($this->db));
        $this->setPrivateProperty($controller, 'userModel', new UserModel($this->db));
        $controller->initController(Services::request(), Services::response(), Services::logger());

        $inboxHtml = $controller->index();
        $this->assertStringContainsString('mensajes/chat/transaccion/20', $inboxHtml);
        $this->assertStringNotContainsString('mensajes/chat/transaccion/20/', $inboxHtml);
        $this->assertStringContainsString('data-conv-key="trx_20"', $inboxHtml);
        $this->assertStringContainsString('Coordinemos el retiro.', $inboxHtml);

        $chatTrxHtml = $controller->chatTransaccion(20);
        $this->assertStringContainsString('30-22222222-2', $chatTrxHtml);
        $this->assertStringContainsString('comprador@test.com', $chatTrxHtml);

        $chatProdHtml = $controller->chatProducto(10, 2);
        $this->assertStringContainsString('30-22222222-2', $chatProdHtml);
        $this->assertStringContainsString('¿Tienen stock disponible?', $chatProdHtml);
    }
}
