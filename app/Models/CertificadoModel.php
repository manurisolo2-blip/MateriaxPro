<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificadoModel extends Model
{
    protected $table            = 'certificados_ambientales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'codigo_certificado',
        'transaccion_id',
        'generadora_id',
        'revalorizadora_id',
        'kg_recuperados',
        'co2_evitado_kg',
        'mwh_ahorrado',
        'norma_referencia',
        'fecha_emision',
        'firmado_digitalmente',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'codigo_certificado'   => 'required|max_length[40]|is_unique[certificados_ambientales.codigo_certificado,id,{id}]',
        'transaccion_id'       => 'required|is_natural_no_zero',
        'generadora_id'        => 'required|is_natural_no_zero',
        'revalorizadora_id'    => 'required|is_natural_no_zero',
        'kg_recuperados'       => 'required|numeric|greater_than[0]',
        'co2_evitado_kg'       => 'required|numeric|greater_than_equal_to[0]',
        'mwh_ahorrado'         => 'required|numeric|greater_than_equal_to[0]',
        'norma_referencia'     => 'required|max_length[100]',
    ];

    /**
     * Factores de emisión de CO2 evitado por kg reciclado vs polímero virgen (ISO 14044)
     */
    protected array $factoresCO2 = [
        'PET'  => 2.15,
        'PEAD' => 1.90,
        'PEBD' => 1.80,
        'PE'   => 1.85,
        'PP'   => 1.85,
        'PVC'  => 1.70,
        'PS'   => 1.75,
        'ABS'  => 2.50,
        'PA'   => 2.30,
    ];

    /**
     * Obtiene el factor de CO2 según el tipo de resina
     */
    public function getFactorCO2(string $tipoPolimero): float
    {
        $tipo = strtoupper(trim($tipoPolimero));
        foreach ($this->factoresCO2 as $sigla => $factor) {
            if (str_contains($tipo, $sigla)) {
                return $factor;
            }
        }
        return 1.80; // Factor promedio estándar de polímeros técnicos
    }

    /**
     * Genera un código de certificado único tipo UUID/Alfanumérico
     */
    public function generarCodigoCertificado(): string
    {
        $anio = date('Y');
        $hash = strtoupper(bin2hex(random_bytes(4)));
        return "CERT-MX-{$anio}-{$hash}";
    }

    /**
     * Emite automáticamente el certificado ambiental si la transacción no tenía uno previo
     */
    public function emitirParaTransaccion(array $transaccion, array $producto): ?array
    {
        // Evitar duplicados
        $existente = $this->where('transaccion_id', $transaccion['id'])->first();
        if ($existente) {
            return $existente;
        }

        $kg = (float) $transaccion['cantidad_kg'];
        $factorCO2 = $this->getFactorCO2($producto['tipo_polimero'] ?? '');
        $co2Evitado = round($kg * $factorCO2, 2);
        
        // Ahorro energético estimado: 0.0035 MWh por cada kg recuperado vs síntesis petroquímica
        $mwhAhorrado = round($kg * 0.0035, 2);

        $codigo = $this->generarCodigoCertificado();

        $data = [
            'codigo_certificado'   => $codigo,
            'transaccion_id'       => (int) $transaccion['id'],
            'generadora_id'        => (int) $transaccion['vendedor_id'],
            'revalorizadora_id'    => (int) $transaccion['comprador_id'],
            'kg_recuperados'       => $kg,
            'co2_evitado_kg'       => $co2Evitado,
            'mwh_ahorrado'         => $mwhAhorrado,
            'norma_referencia'     => 'ISO 14044 / GHG Protocol Alcance 3 — Economía Circular',
            'fecha_emision'        => date('Y-m-d H:i:s'),
            'firmado_digitalmente' => 1,
        ];

        $id = $this->insert($data);
        if ($id) {
            return $this->find($id);
        }

        return null;
    }

    /**
     * Certificados de una empresa (como generadora o revalorizadora)
     */
    public function getCertificadosPorEmpresa(int $empresaId): array
    {
        return $this->select('certificados_ambientales.*, 
                             transacciones.codigo_operacion,
                             productos.nombre as producto_nombre,
                             productos.tipo_polimero,
                             generadora.nombre as generadora_nombre,
                             revalorizadora.nombre as revalorizadora_nombre')
                    ->join('transacciones', 'transacciones.id = certificados_ambientales.transaccion_id', 'left')
                    ->join('productos', 'productos.id = transacciones.producto_id', 'left')
                    ->join('usuarios as generadora', 'generadora.id = certificados_ambientales.generadora_id', 'left')
                    ->join('usuarios as revalorizadora', 'revalorizadora.id = certificados_ambientales.revalorizadora_id', 'left')
                    ->groupStart()
                        ->where('certificados_ambientales.generadora_id', $empresaId)
                        ->orWhere('certificados_ambientales.revalorizadora_id', $empresaId)
                    ->groupEnd()
                    ->orderBy('certificados_ambientales.fecha_emision', 'DESC')
                    ->findAll();
    }

    /**
     * Detalle completo del certificado para la vista imprimible
     */
    public function getDetalleCertificado(string $codigo): ?array
    {
        return $this->select('certificados_ambientales.*, 
                             transacciones.codigo_operacion,
                             transacciones.modalidad_retiro,
                             transacciones.fecha_solicitud,
                             transacciones.fecha_acuerdo,
                             productos.id as producto_id,
                             productos.nombre as producto_nombre,
                             productos.tipo_polimero,
                             productos.descripcion as producto_descripcion,
                             generadora.nombre as generadora_nombre,
                             generadora.cuit as generadora_cuit,
                             generadora.ciudad as generadora_ciudad,
                             generadora.provincia as generadora_provincia,
                             revalorizadora.nombre as revalorizadora_nombre,
                             revalorizadora.cuit as revalorizadora_cuit,
                             revalorizadora.ciudad as revalorizadora_ciudad,
                             revalorizadora.provincia as revalorizadora_provincia,
                             plantas_industriales.nombre_planta,
                             plantas_industriales.localidad as planta_localidad,
                             plantas_industriales.provincia as planta_provincia')
                    ->join('transacciones', 'transacciones.id = certificados_ambientales.transaccion_id', 'left')
                    ->join('productos', 'productos.id = transacciones.producto_id', 'left')
                    ->join('plantas_industriales', 'plantas_industriales.id = transacciones.planta_origen_id', 'left')
                    ->join('usuarios as generadora', 'generadora.id = certificados_ambientales.generadora_id', 'left')
                    ->join('usuarios as revalorizadora', 'revalorizadora.id = certificados_ambientales.revalorizadora_id', 'left')
                    ->where('certificados_ambientales.codigo_certificado', $codigo)
                    ->first();
    }

    /**
     * Totales acumulados para métricas ecológicas de la empresa
     */
    public function getMetricasEmpresa(int $empresaId): array
    {
        $res = $this->selectSum('kg_recuperados', 'total_kg')
                    ->selectSum('co2_evitado_kg', 'total_co2')
                    ->selectSum('mwh_ahorrado', 'total_mwh')
                    ->groupStart()
                        ->where('generadora_id', $empresaId)
                        ->orWhere('revalorizadora_id', $empresaId)
                    ->groupEnd()
                    ->first();

        return [
            'total_kg'     => (float) ($res['total_kg'] ?? 0),
            'total_co2'    => (float) ($res['total_co2'] ?? 0),
            'total_mwh'    => (float) ($res['total_mwh'] ?? 0),
            'certificados' => $this->groupStart()
                                    ->where('generadora_id', $empresaId)
                                    ->orWhere('revalorizadora_id', $empresaId)
                                   ->groupEnd()
                                   ->countAllResults(),
        ];
    }
}
