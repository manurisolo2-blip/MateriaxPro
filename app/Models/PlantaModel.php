<?php

namespace App\Models;

use CodeIgniter\Model;

class PlantaModel extends Model
{
    protected $table            = 'plantas_industriales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'empresa_id',
        'nombre_planta',
        'direccion',
        'localidad',
        'provincia',
        'coordenadas_gps',
        'posee_bascula',
        'capacidad_carga_camiones',
        'contacto_responsable',
        'telefono_planta',
        'activa',
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Reglas de validación
    protected $validationRules = [
        'empresa_id'               => 'required|is_natural_no_zero',
        'nombre_planta'            => 'required|min_length[3]|max_length[100]',
        'direccion'                => 'required|min_length[3]|max_length[150]',
        'localidad'                => 'required|min_length[2]|max_length[100]',
        'provincia'                => 'required|max_length[100]',
        'posee_bascula'            => 'permit_empty|in_list[0,1]',
        'capacidad_carga_camiones' => 'permit_empty|max_length[50]',
        'contacto_responsable'     => 'permit_empty|max_length[100]',
        'telefono_planta'          => 'permit_empty|max_length[30]',
    ];

    protected $validationMessages = [
        'nombre_planta' => [
            'required'   => 'El nombre o denominación de la planta es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            'max_length' => 'El nombre no puede superar los 100 caracteres.',
        ],
        'direccion' => [
            'required'   => 'La dirección o ruta del predio es obligatoria.',
            'min_length' => 'La dirección debe tener al menos 3 caracteres.',
        ],
        'localidad' => [
            'required'   => 'La ciudad o localidad es obligatoria.',
            'min_length' => 'La localidad debe tener al menos 2 caracteres.',
        ],
        'provincia' => [
            'required' => 'La provincia es obligatoria.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Retorna las plantas asociadas a una empresa específica
     */
    public function getPlantasPorEmpresa(int $empresaId, bool $soloActivas = false): array
    {
        $builder = $this->where('empresa_id', $empresaId);
        if ($soloActivas) {
            $builder->where('activa', 1);
        }
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Verifica si una planta pertenece a una empresa dada (medida de seguridad)
     */
    public function perteneceAEmpresa(int $plantaId, int $empresaId): bool
    {
        $planta = $this->where('id', $plantaId)
                       ->where('empresa_id', $empresaId)
                       ->first();
        return $planta !== null;
    }
}
