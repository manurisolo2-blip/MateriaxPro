<?php

namespace App\Models;

use CodeIgniter\Model;

class LocalidadModel extends Model
{
    protected $table      = 'localidades';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Obtiene todas las provincias ordenadas alfabéticamente
     */
    public function getProvincias(): array
    {
        $db = \Config\Database::connect();
        try {
            $res = $db->table('provincias')->orderBy('provincia', 'ASC')->get()->getResultArray();
            if (!empty($res)) {
                return $res;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::getProvincias: ' . $e->getMessage());
        }

        return $this->parseProvinciasFromSql();
    }

    /**
     * Obtiene todas las localidades con el nombre de su provincia asociada
     */
    public function getTodasConProvincia(): array
    {
        $db = \Config\Database::connect();
        try {
            $builder = $db->table('localidades l')
                ->select('l.id, l.id_privincia, l.localidad, p.provincia')
                ->join('provincias p', 'p.id = l.id_privincia', 'left')
                ->orderBy('l.localidad', 'ASC');

            $res = $builder->get()->getResultArray();
            if (!empty($res)) {
                return $res;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::getTodasConProvincia: ' . $e->getMessage());
        }

        return $this->parseLocalidadesFromSql();
    }

    /**
     * Busca localidades por nombre y/o provincia
     */
    public function buscar(?string $search = null, ?int $provinciaId = null, ?string $provinciaNombre = null, int $limit = 50): array
    {
        $db = \Config\Database::connect();
        try {
            $builder = $db->table('localidades l')
                ->select('l.id, l.id_privincia, l.localidad, p.provincia')
                ->join('provincias p', 'p.id = l.id_privincia', 'left');

            if ($provinciaId !== null && $provinciaId > 0) {
                $builder->where('l.id_privincia', $provinciaId);
            } elseif (!empty($provinciaNombre)) {
                $builder->where('p.provincia', $provinciaNombre);
            }

            if (!empty($search)) {
                $builder->like('l.localidad', $search);
            }

            if ($limit > 0) {
                $builder->limit($limit);
            }

            return $builder->orderBy('l.localidad', 'ASC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::buscar: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fallback directo leyendo AllCitys/Argentina_Provincias_Ciudades.sql
     */
    protected function parseProvinciasFromSql(): array
    {
        $path = ROOTPATH . 'AllCitys/Argentina_Provincias_Ciudades.sql';
        if (!is_file($path)) return [];

        $content = file_get_contents($path);
        $provincias = [];
        if (preg_match_all("/\((\d+),\s*'([^']+)'\)/", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                // Las provincias van del id 1 al 25
                $id = (int)$m[1];
                if ($id <= 25) {
                    $provincias[] = ['id' => $id, 'provincia' => $m[2]];
                }
            }
        }

        usort($provincias, fn($a, $b) => strcmp($a['provincia'], $b['provincia']));
        return $provincias;
    }

    /**
     * Fallback directo leyendo localidades de AllCitys/Argentina_Provincias_Ciudades.sql
     */
    protected function parseLocalidadesFromSql(): array
    {
        $path = ROOTPATH . 'AllCitys/Argentina_Provincias_Ciudades.sql';
        if (!is_file($path)) return [];

        $content = file_get_contents($path);
        $provMap = [];
        foreach ($this->parseProvinciasFromSql() as $p) {
            $provMap[$p['id']] = $p['provincia'];
        }

        $localidades = [];
        if (preg_match_all("/\((\d+),\s*(\d+),\s*'([^']+)'\)/", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $pId = (int)$m[2];
                $localidades[] = [
                    'id'           => (int)$m[1],
                    'id_privincia' => $pId,
                    'localidad'    => $m[3],
                    'provincia'    => $provMap[$pId] ?? '',
                ];
            }
        }

        usort($localidades, fn($a, $b) => strcmp($a['localidad'], $b['localidad']));
        return $localidades;
    }
}
