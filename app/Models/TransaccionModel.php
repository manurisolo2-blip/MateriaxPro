<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaccionModel extends Model
{
    protected $table            = 'transacciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'codigo_operacion',
        'producto_id',
        'comprador_id',
        'vendedor_id',
        'planta_origen_id',
        'cantidad_kg',
        'precio_unitario_pactado',
        'subtotal',
        'costo_flete',
        'monto_total',
        'modalidad_retiro',
        'estado',
        'ticket_balanza_kg',
        'numero_remito',
        'observaciones',
        'fecha_solicitud',
        'fecha_acuerdo',
        'fecha_despacho',
        'fecha_cierre',
    ];

    // Fechas
    protected $useTimestamps = false; // Manejado explícitamente en el flujo de negocio

    // Reglas de validación
    protected $validationRules = [
        'codigo_operacion'        => 'required|max_length[20]',
        'producto_id'             => 'required|is_natural_no_zero',
        'comprador_id'            => 'required|is_natural_no_zero',
        'vendedor_id'             => 'required|is_natural_no_zero',
        'cantidad_kg'             => 'required|numeric|greater_than[0]',
        'precio_unitario_pactado' => 'required|numeric|greater_than_equal_to[0]',
        'subtotal'                => 'required|numeric|greater_than_equal_to[0]',
        'monto_total'             => 'required|numeric|greater_than_equal_to[0]',
        'modalidad_retiro'        => 'required|in_list[retiro_comprador_planta,entrega_vendedor,flete_convenido]',
        'estado'                  => 'required|in_list[solicitada,en_evaluacion,aprobada,pesaje_balanza,en_transito,completada,rechazada,cancelada]',
    ];

    protected $validationMessages = [
        'cantidad_kg' => [
            'required'     => 'Debe indicar la cantidad de kilogramos solicitados.',
            'greater_than' => 'La cantidad debe ser mayor a 0 kg.',
        ],
        'precio_unitario_pactado' => [
            'required'              => 'Debe indicar el precio por kilogramo propuesto.',
            'greater_than_equal_to' => 'El precio debe ser mayor o igual a 0.',
        ],
        'modalidad_retiro' => [
            'required' => 'Debe seleccionar una modalidad logística de retiro.',
            'in_list'  => 'Modalidad de retiro inválida.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Genera un código de operación único para la transacción (ej: TRX-2026-X8A2)
     */
    public function generarCodigoOperacion(): string
    {
        $anio = date('Y');
        do {
            $aleatorio = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $codigo = "TRX-{$anio}-{$aleatorio}";
            $existe = $this->where('codigo_operacion', $codigo)->first();
        } while ($existe !== null);

        return $codigo;
    }

    /**
     * Solicitudes enviadas por el usuario logueado como comprador
     */
    public function getSolicitudesEnviadas(int $compradorId): array
    {
        return $this->select('transacciones.*, 
                             productos.nombre as producto_nombre, 
                             productos.tipo_polimero,
                             productos.cantidad_kg as producto_stock_original,
                             vendedores.nombre as vendedor_nombre,
                             vendedores.email as vendedor_email,
                             vendedores.telefono as vendedor_telefono,
                             vendedores.cuit as vendedor_cuit,
                             plantas_industriales.nombre_planta,
                             plantas_industriales.localidad as planta_localidad,
                             plantas_industriales.provincia as planta_provincia')
                    ->join('productos', 'productos.id = transacciones.producto_id', 'left')
                    ->join('usuarios as vendedores', 'vendedores.id = transacciones.vendedor_id', 'left')
                    ->join('plantas_industriales', 'plantas_industriales.id = transacciones.planta_origen_id', 'left')
                    ->where('transacciones.comprador_id', $compradorId)
                    ->orderBy('transacciones.fecha_solicitud', 'DESC')
                    ->findAll();
    }

    /**
     * Solicitudes recibidas por el usuario logueado como vendedor de sus lotes
     */
    public function getSolicitudesRecibidas(int $vendedorId): array
    {
        return $this->select('transacciones.*, 
                             productos.nombre as producto_nombre, 
                             productos.tipo_polimero,
                             productos.cantidad_kg as producto_stock_original,
                             compradores.nombre as comprador_nombre,
                             compradores.email as comprador_email,
                             compradores.telefono as comprador_telefono,
                             compradores.cuit as comprador_cuit,
                             plantas_industriales.nombre_planta,
                             plantas_industriales.localidad as planta_localidad')
                    ->join('productos', 'productos.id = transacciones.producto_id', 'left')
                    ->join('usuarios as compradores', 'compradores.id = transacciones.comprador_id', 'left')
                    ->join('plantas_industriales', 'plantas_industriales.id = transacciones.planta_origen_id', 'left')
                    ->where('transacciones.vendedor_id', $vendedorId)
                    ->orderBy('transacciones.fecha_solicitud', 'DESC')
                    ->findAll();
    }

    /**
     * Detalle completo de una transacción específica
     */
    public function getDetalleTransaccion(int $id): ?array
    {
        return $this->select('transacciones.*, 
                             productos.nombre as producto_nombre, 
                             productos.tipo_polimero, 
                             productos.descripcion as producto_descripcion,
                             productos.ubicacion as producto_ubicacion,
                             productos.estado as producto_estado,
                             compradores.nombre as comprador_nombre,
                             compradores.email as comprador_email,
                             compradores.telefono as comprador_telefono,
                             compradores.cuit as comprador_cuit,
                             compradores.ciudad as comprador_ciudad,
                             vendedores.nombre as vendedor_nombre,
                             vendedores.email as vendedor_email,
                             vendedores.telefono as vendedor_telefono,
                             vendedores.cuit as vendedor_cuit,
                             vendedores.ciudad as vendedor_ciudad,
                             plantas_industriales.nombre_planta,
                             plantas_industriales.direccion as planta_direccion,
                             plantas_industriales.localidad as planta_localidad,
                             plantas_industriales.provincia as planta_provincia,
                             plantas_industriales.posee_bascula as planta_posee_bascula,
                             plantas_industriales.capacidad_carga_camiones as planta_capacidad_carga')
                    ->join('productos', 'productos.id = transacciones.producto_id', 'left')
                    ->join('usuarios as compradores', 'compradores.id = transacciones.comprador_id', 'left')
                    ->join('usuarios as vendedores', 'vendedores.id = transacciones.vendedor_id', 'left')
                    ->join('plantas_industriales', 'plantas_industriales.id = transacciones.planta_origen_id', 'left')
                    ->where('transacciones.id', $id)
                    ->first();
    }

    /**
     * Cuenta solicitudes pendientes recibidas para mostrar insignia/alerta
     */
    public function contarPendientesRecibidas(int $vendedorId): int
    {
        return $this->where('vendedor_id', $vendedorId)
                    ->where('estado', 'solicitada')
                    ->countAllResults();
    }
}
