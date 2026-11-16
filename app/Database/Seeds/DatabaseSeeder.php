<?php

namespace App\Database\Seeds;

use App\Models\{ProductoModel, UserModel};
use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $demoUser = $userModel->where('email', 'admin@materiax.com')->first();
        $userId   = $demoUser ? $demoUser['id'] : $userModel->insert([
            'nombre'   => 'Petroquímica Río Tercero S.A.',
            'email'    => 'admin@materiax.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'cuit'     => '30-50284912-8',
            'telefono' => '+54 3571 42-1500',
            'rol'      => 'empresa',
        ]);

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

        $productoModel = new ProductoModel();
        if ($productoModel->countAllResults() === 0) {
            foreach ([
                ['Pellet Polietileno Alta Densidad (HDPE)', 'Polietileno (PE)', 2500.00, 1850.50, 'Río Tercero, Córdoba', 'Pellet virgen recuperado de purga de soplado. Color natural, índice de fluidez 0.35 g/10min. Envasado en big bags de 1000 kg con control de humedad.'],
                ['Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800.00, 1420.00, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.'],
                ['Merma de Bobinas PVC Cristal Flexible', 'PVC', 950.00, 2100.00, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.'],
            ] as [$nombre, $tipo, $kg, $precio, $ubic, $desc]) {
                $productoModel->insert([
                    'user_id'         => $userId,
                    'nombre'          => $nombre,
                    'tipo_polimero'   => $tipo,
                    'cantidad_kg'     => $kg,
                    'precio_unitario' => $precio,
                    'ubicacion'       => $ubic,
                    'descripcion'     => $desc,
                    'estado'          => 'Disponible',
                ]);
            }
        }
    }
}
