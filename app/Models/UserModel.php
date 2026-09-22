<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'email',
        'password',
        'cuit',
        'telefono',
        'rubro',
        'ciudad',
        'provincia',
        'direccion',
        'rol',
        'estado',
        'ultimo_login',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules
    protected $validationRules = [
        'nombre'    => 'required|min_length[3]|max_length[100]',
        'email'     => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
        'password'  => 'permit_empty|min_length[6]',
        'cuit'      => 'required|min_length[10]|max_length[20]',
        'telefono'  => 'required|min_length[6]|max_length[30]',
        'rubro'     => 'permit_empty|max_length[100]',
        'ciudad'    => 'permit_empty|max_length[100]',
        'provincia' => 'permit_empty|max_length[100]',
        'direccion' => 'permit_empty|max_length[150]',
        'estado'    => 'in_list[pendiente,activo,inactivo,rechazado]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required'   => 'El nombre o razón social de la empresa es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            'max_length' => 'El nombre no puede superar los 100 caracteres.',
        ],
        'email' => [
            'required'    => 'El correo electrónico corporativo es obligatorio.',
            'valid_email' => 'Por favor ingresa un correo electrónico corporativo válido.',
            'is_unique'   => 'Este correo electrónico ya se encuentra registrado en la red MateriaX.',
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria.',
            'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
        ],
        'cuit' => [
            'required'   => 'El CUIT de la empresa es obligatorio.',
            'min_length' => 'El CUIT debe tener al menos 10 caracteres (ej: 30-XXXXXXXX-X).',
        ],
        'telefono' => [
            'required'   => 'El teléfono de contacto institucional es obligatorio.',
            'min_length' => 'El teléfono debe tener al menos 6 caracteres.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Buscar usuario por email corporativo
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /**
     * Registrar fecha y hora del último acceso exitoso
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'ultimo_login' => date('Y-m-d H:i:s'),
        ]);
    }
}
