<?php

namespace App\Models;

use CodeIgniter\Model;

class MensajeModel extends Model
{
    protected $table            = 'mensajes_negociacion';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'producto_id',
        'transaccion_id',
        'emisor_id',
        'receptor_id',
        'mensaje',
        'leido',
        'fecha_lectura',
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No se actualiza la fecha de creación

    // Reglas de validación
    protected $validationRules = [
        'producto_id'    => 'required|is_natural_no_zero',
        'emisor_id'      => 'required|is_natural_no_zero',
        'receptor_id'    => 'required|is_natural_no_zero',
        'mensaje'        => 'required|min_length[1]|max_length[2000]',
    ];

    protected $validationMessages = [
        'mensaje' => [
            'required'   => 'El mensaje no puede estar vacío.',
            'min_length' => 'El mensaje debe contener al menos 1 carácter.',
            'max_length' => 'El mensaje no puede superar los 2000 caracteres.',
        ],
    ];

    /**
     * Obtiene el hilo cronológico de mensajes de una transacción comercial
     */
    public function getMensajesPorTransaccion(int $transaccionId): array
    {
        return $this->select('mensajes_negociacion.*, 
                              emisor.nombre AS emisor_nombre, 
                              receptor.nombre AS receptor_nombre')
                    ->join('usuarios emisor', 'emisor.id = mensajes_negociacion.emisor_id', 'left')
                    ->join('usuarios receptor', 'receptor.id = mensajes_negociacion.receptor_id', 'left')
                    ->where('mensajes_negociacion.transaccion_id', $transaccionId)
                    ->orderBy('mensajes_negociacion.created_at', 'ASC')
                    ->orderBy('mensajes_negociacion.id', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene mensajes de consulta directa sobre un lote entre dos empresas (o todos los del lote si no se especifican usuarios)
     */
    public function getMensajesPorProducto(int $productoId, int $userId1 = 0, int $userId2 = 0): array
    {
        $builder = $this->select('mensajes_negociacion.*, 
                                  emisor.nombre AS emisor_nombre, 
                                  receptor.nombre AS receptor_nombre')
                        ->join('usuarios emisor', 'emisor.id = mensajes_negociacion.emisor_id', 'left')
                        ->join('usuarios receptor', 'receptor.id = mensajes_negociacion.receptor_id', 'left')
                        ->where('mensajes_negociacion.producto_id', $productoId)
                        ->where('mensajes_negociacion.transaccion_id', null);

        if ($userId1 > 0 && $userId2 > 0) {
            $builder->groupStart()
                        ->groupStart()
                            ->where('mensajes_negociacion.emisor_id', $userId1)
                            ->where('mensajes_negociacion.receptor_id', $userId2)
                        ->groupEnd()
                        ->orGroupStart()
                            ->where('mensajes_negociacion.emisor_id', $userId2)
                            ->where('mensajes_negociacion.receptor_id', $userId1)
                        ->groupEnd()
                    ->groupEnd()
                    ->orderBy('mensajes_negociacion.created_at', 'ASC')
                    ->orderBy('mensajes_negociacion.id', 'ASC');
        } else {
            $builder->orderBy('mensajes_negociacion.created_at', 'DESC')
                    ->orderBy('mensajes_negociacion.id', 'DESC');
        }

        return $builder->findAll();
    }

    /**
     * Marca como leídos los mensajes de consulta sobre un producto
     */
    public function marcarLeidosProducto(int $productoId, int $emisorId, int $receptorId): void
    {
        $this->where('producto_id', $productoId)
             ->where('transaccion_id', null)
             ->where('emisor_id', $emisorId)
             ->where('receptor_id', $receptorId)
             ->where('leido', 0)
             ->set(['leido' => 1, 'fecha_lectura' => date('Y-m-d H:i:s')])
             ->update();
    }

    /**
     * Marca como leídos los mensajes de una transacción donde el usuario es receptor
     */
    public function marcarLeidosTransaccion(int $transaccionId, int $userId): void
    {
        $this->where('transaccion_id', $transaccionId)
             ->where('receptor_id', $userId)
             ->where('leido', 0)
             ->set(['leido' => 1, 'fecha_lectura' => date('Y-m-d H:i:s')])
             ->update();
    }

    /**
     * Cuenta la cantidad de mensajes no leídos que tiene un usuario
     */
    public function contarNoLeidos(int $userId): int
    {
        return $this->where('receptor_id', $userId)
                    ->where('leido', 0)
                    ->countAllResults();
    }

    /**
     * Obtiene la lista de conversaciones recientes del usuario (agrupadas por transacción)
     */
    public function getConversacionesUsuario(int $userId): array
    {
        // Traemos todos los mensajes donde el usuario participa
        $mensajes = $this->select('mensajes_negociacion.*, 
                                   p.nombre AS producto_nombre, 
                                   p.tipo_polimero,
                                   t.codigo_operacion,
                                   t.estado AS transaccion_estado,
                                   emisor.nombre AS emisor_nombre, 
                                   receptor.nombre AS receptor_nombre')
                         ->join('productos p', 'p.id = mensajes_negociacion.producto_id', 'left')
                         ->join('transacciones t', 't.id = mensajes_negociacion.transaccion_id', 'left')
                         ->join('usuarios emisor', 'emisor.id = mensajes_negociacion.emisor_id', 'left')
                         ->join('usuarios receptor', 'receptor.id = mensajes_negociacion.receptor_id', 'left')
                         ->groupStart()
                             ->where('mensajes_negociacion.emisor_id', $userId)
                             ->orWhere('mensajes_negociacion.receptor_id', $userId)
                         ->groupEnd()
                         ->orderBy('mensajes_negociacion.created_at', 'DESC')
                         ->orderBy('mensajes_negociacion.id', 'DESC')
                         ->findAll();

        $hilos = [];
        foreach ($mensajes as $m) {
            if ((int)$m['emisor_id'] === (int)$m['receptor_id']) {
                continue;
            }

            // Clave del hilo: por transaccion_id o por producto_id + otra empresa
            $esMio             = ((int)$m['emisor_id'] === $userId);
            $otraEmpresaId     = $esMio ? (int)$m['receptor_id'] : (int)$m['emisor_id'];
            $otraEmpresaNombre = $esMio ? ($m['receptor_nombre'] ?? 'Empresa Registrada') : ($m['emisor_nombre'] ?? 'Empresa Registrada');

            $clave = !empty($m['transaccion_id']) 
                ? 'trx_' . $m['transaccion_id'] 
                : 'prod_' . $m['producto_id'] . '_' . $otraEmpresaId;

            if (!isset($hilos[$clave])) {
                $hilos[$clave] = [
                    'clave'              => $clave,
                    'transaccion_id'     => $m['transaccion_id'],
                    'producto_id'        => $m['producto_id'],
                    'producto_nombre'    => $m['producto_nombre'] ?? 'Lote Industrial',
                    'tipo_polimero'      => $m['tipo_polimero'] ?? '',
                    'codigo_operacion'   => $m['codigo_operacion'] ?? null,
                    'transaccion_estado' => $m['transaccion_estado'] ?? null,
                    'otra_empresa_id'    => $otraEmpresaId,
                    'otra_empresa_nombre'=> $otraEmpresaNombre,
                    'ultimo_mensaje'     => $m['mensaje'],
                    'ultimo_emisor_id'   => $m['emisor_id'],
                    'ultima_fecha'       => $m['created_at'],
                    'no_leidos'          => 0,
                    'total_mensajes'     => 0,
                    'mensajes_recientes' => [],
                ];
            }

            $hilos[$clave]['total_mensajes']++;

            if (count($hilos[$clave]['mensajes_recientes']) < 4) {
                $hilos[$clave]['mensajes_recientes'][] = [
                    'id'            => (int) $m['id'],
                    'emisor_id'     => (int) $m['emisor_id'],
                    'emisor_nombre' => $esMio ? 'Tú' : ($m['emisor_nombre'] ?? $otraEmpresaNombre),
                    'es_mio'        => $esMio,
                    'mensaje'       => (string) $m['mensaje'],
                    'fecha'         => !empty($m['created_at']) ? date('d/m H:i', strtotime($m['created_at'])) : '',
                ];
            }

            // Sumar no leídos si yo soy el receptor
            if ((int)$m['receptor_id'] === $userId && (int)$m['leido'] === 0) {
                $hilos[$clave]['no_leidos']++;
            }
        }

        foreach ($hilos as &$hilo) {
            $hilo['mensajes_recientes'] = array_reverse($hilo['mensajes_recientes']);
        }
        unset($hilo);

        return array_values($hilos);
    }
}
