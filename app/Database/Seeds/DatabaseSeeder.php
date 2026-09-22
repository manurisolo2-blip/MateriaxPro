<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Insertar usuario demo
        $userModel = new \App\Models\UserModel();
        
        $demoUser = $userModel->where('email', 'admin@materiax.com')->first();
        if (!$demoUser) {
            $userId = $userModel->insert([
                'nombre'   => 'Petroquímica Río Tercero S.A.',
                'email'    => 'admin@materiax.com',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'cuit'     => '30-50284912-8',
                'telefono' => '+54 3571 42-1500',
                'rol'      => 'empresa',
            ]);
        } else {
            $userId = $demoUser['id'];
        }

        // 1.1 Insertar Administrador General exclusivo
        $adminUser = $userModel->where('email', 'myadminpro@gmail.com')->first();
        if (!$adminUser) {
            $userModel->insert([
                'nombre'       => 'Administrador General MateriaX',
                'email'        => 'myadminpro@gmail.com',
                'password'     => password_hash('Bautiprouwu123', PASSWORD_BCRYPT),
                'cuit'         => '20-00000000-0',
                'telefono'     => '+54 3571 00-0000',
                'rubro'        => 'Administración Central',
                'ciudad'       => 'Río Tercero',
                'provincia'    => 'Córdoba',
                'direccion'    => 'Sede Central MateriaX',
                'rol'          => 'admin',
                'estado'       => 'activo',
                'ultimo_login' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $userModel->update($adminUser['id'], [
                'password' => password_hash('Bautiprouwu123', PASSWORD_BCRYPT),
                'rol'      => 'admin',
                'estado'   => 'activo',
            ]);
        }

        // 2. Insertar productos iniciales asociados al usuario demo
        $productoModel = new \App\Models\ProductoModel();

        if ($productoModel->countAllResults() === 0) {
            $productoModel->insert([
                'user_id'         => $userId,
                'nombre'          => 'Pellet Polietileno Alta Densidad (HDPE)',
                'tipo_polimero'   => 'Polietileno (PE)',
                'cantidad_kg'     => 2500.00,
                'precio_unitario' => 1850.50,
                'ubicacion'       => 'Río Tercero, Córdoba',
                'descripcion'     => 'Pellet virgen recuperado de purga de soplado. Color natural, índice de fluidez 0.35 g/10min. Envasado en big bags de 1000 kg con control de humedad.',
                'estado'          => 'Disponible',
            ]);

            $productoModel->insert([
                'user_id'         => $userId,
                'nombre'          => 'Scrap Molido de Polipropileno Homopolímero (PP)',
                'tipo_polimero'   => 'Polipropileno (PP)',
                'cantidad_kg'     => 1800.00,
                'precio_unitario' => 1420.00,
                'ubicacion'       => 'Río Tercero, Córdoba',
                'descripcion'     => 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.',
                'estado'          => 'Disponible',
            ]);

            $productoModel->insert([
                'user_id'         => $userId,
                'nombre'          => 'Merma de Bobinas PVC Cristal Flexible',
                'tipo_polimero'   => 'PVC',
                'cantidad_kg'     => 950.00,
                'precio_unitario' => 2100.00,
                'ubicacion'       => 'Almafuerte, Córdoba',
                'descripcion'     => 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.',
                'estado'          => 'Disponible',
            ]);
        }
    }
}
