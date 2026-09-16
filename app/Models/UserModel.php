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
        'rol',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules
    protected $validationRules = [
        'nombre'   => 'required|min_length[3]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
        'password' => 'required|min_length[6]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required'   => 'El nombre o razón social es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            'max_length' => 'El nombre no puede superar los 100 caracteres.',
        ],
        'email' => [
            'required'    => 'El correo electrónico es obligatorio.',
            'valid_email' => 'Por favor ingresa un correo electrónico válido.',
            'is_unique'   => 'Este correo electrónico ya se encuentra registrado en el sistema.',
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria.',
            'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }
}
