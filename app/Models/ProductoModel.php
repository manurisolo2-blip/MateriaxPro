<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table         = 'productos';
    protected $allowedFields = [
        'user_id', 'planta_id', 'nombre', 'tipo_polimero',
        'cantidad_kg', 'precio_unitario', 'ubicacion', 'descripcion', 'estado',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'nombre'          => 'required|min_length[3]|max_length[150]',
        'tipo_polimero'   => 'required|max_length[50]',
        'cantidad_kg'     => 'required|numeric|greater_than[0]',
        'precio_unitario' => 'required|numeric|greater_than_equal_to[0]',
        'ubicacion'       => 'required|min_length[2]|max_length[100]',
        'estado'          => 'required|in_list[Disponible,Reservado,Vendido]',
        'planta_id'       => 'permit_empty|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'nombre'          => [
            'required'   => 'El nombre del material es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            'max_length' => 'El nombre no puede superar los 150 caracteres.',
        ],
        'tipo_polimero'   => ['required' => 'Debe seleccionar un tipo de polímero válido.'],
        'cantidad_kg'     => [
            'required'     => 'La cantidad en kilogramos es obligatoria.',
            'numeric'      => 'La cantidad debe ser un valor numérico.',
            'greater_than' => 'La cantidad debe ser mayor a 0 kg.',
        ],
        'precio_unitario' => [
            'required'              => 'El precio unitario es obligatorio.',
            'numeric'               => 'El precio debe ser un valor numérico.',
            'greater_than_equal_to' => 'El precio debe ser mayor o igual a 0.',
        ],
        'ubicacion'       => [
            'required'   => 'La ubicación geográfica de origen es obligatoria.',
            'min_length' => 'La ubicación debe tener al menos 2 caracteres.',
        ],
        'estado'          => [
            'required' => 'El estado comercial es obligatorio.',
            'in_list'  => 'El estado debe ser Disponible, Reservado o Vendido.',
        ],
    ];

    public function getProductosConUsuario($id = null)
    {
        $builder = $this->select(
            'productos.*, usuarios.nombre as empresa_nombre, usuarios.email as empresa_email,
             usuarios.telefono as empresa_telefono, usuarios.cuit as empresa_cuit,
             plantas_industriales.nombre_planta, plantas_industriales.direccion as planta_direccion,
             plantas_industriales.localidad as planta_localidad, plantas_industriales.provincia as planta_provincia,
             plantas_industriales.posee_bascula as planta_posee_bascula'
        )
            ->join('usuarios', 'usuarios.id = productos.user_id', 'left')
            ->join('plantas_industriales', 'plantas_industriales.id = productos.planta_id', 'left');

        return $id !== null
            ? $builder->where('productos.id', $id)->first()
            : $builder->orderBy('productos.created_at', 'DESC')->findAll();
    }

    public function getTotalKg(): float
    {
        $row = $this->selectSum('cantidad_kg', 'total_kg')->first();
        return (float) ($row['total_kg'] ?? 0);
    }

    public function getCountsByUser(): array
    {
        return array_column(
            $this->select('user_id, COUNT(*) as total')->groupBy('user_id')->findAll(),
            'total',
            'user_id'
        );
    }
}
