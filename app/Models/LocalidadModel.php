<?php

namespace App\Models;

use CodeIgniter\Model;

class LocalidadModel extends Model
{
    protected $table = 'localidades';

    public function getProvincias(): array
    {
        try {
            $res = \Config\Database::connect()->table('provincias')->orderBy('provincia', 'ASC')->get()->getResultArray();
            if (!empty($res)) {
                return $res;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::getProvincias: ' . $e->getMessage());
        }
        return $this->parseSqlFallback()['provincias'];
    }

    public function getTodasConProvincia(): array
    {
        try {
            $res = \Config\Database::connect()->table('localidades l')
                ->select('l.id, l.id_privincia, l.localidad, p.provincia')
                ->join('provincias p', 'p.id = l.id_privincia', 'left')
                ->orderBy('l.localidad', 'ASC')
                ->get()->getResultArray();
            if (!empty($res)) {
                return $res;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::getTodasConProvincia: ' . $e->getMessage());
        }
        return $this->parseSqlFallback()['localidades'];
    }

    public function buscar(?string $search = null, ?int $provinciaId = null, ?string $provinciaNombre = null, int $limit = 50): array
    {
        try {
            $b = \Config\Database::connect()->table('localidades l')
                ->select('l.id, l.id_privincia, l.localidad, p.provincia')
                ->join('provincias p', 'p.id = l.id_privincia', 'left');

            if ($provinciaId !== null && $provinciaId > 0) {
                $b->where('l.id_privincia', $provinciaId);
            } elseif (!empty($provinciaNombre)) {
                $b->where('p.provincia', $provinciaNombre);
            }
            if (!empty($search)) {
                $b->like('l.localidad', $search);
            }
            if ($limit > 0) {
                $b->limit($limit);
            }
            $res = $b->orderBy('l.localidad', 'ASC')->get()->getResultArray();
            if (!empty($res)) {
                return $res;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::buscar: ' . $e->getMessage());
        }

        // Fallback en memoria si la BD no tiene tablas de provincias/localidades cargadas
        $filtered = array_filter($this->getTodasConProvincia(), static function (array $loc) use ($search, $provinciaId, $provinciaNombre): bool {
            if ($provinciaId !== null && $provinciaId > 0 && (int) $loc['id_privincia'] !== $provinciaId) {
                return false;
            }
            if (($provinciaId === null || $provinciaId <= 0) && !empty($provinciaNombre) && strcasecmp($loc['provincia'], $provinciaNombre) !== 0) {
                return false;
            }
            return empty($search) || mb_stripos($loc['localidad'], $search, 0, 'UTF-8') !== false;
        });

        return $limit > 0 ? array_slice(array_values($filtered), 0, $limit) : array_values($filtered);
    }

    /** Fallback único leyendo AllCitys/Argentina_Provincias_Ciudades.sql */
    private function parseSqlFallback(): array
    {
        $path = ROOTPATH . 'AllCitys/Argentina_Provincias_Ciudades.sql';
        if (!is_file($path)) {
            return ['provincias' => [], 'localidades' => []];
        }
        $content    = (string) file_get_contents($path);
        $provincias = $provMap = $localidades = [];

        if (preg_match_all("/\((\d+),\s*'([^']+)'\)/", $content, $pm, PREG_SET_ORDER)) {
            foreach ($pm as $m) {
                if ((int) $m[1] <= 25) {
                    $provincias[]       = ['id' => (int) $m[1], 'provincia' => $m[2]];
                    $provMap[(int) $m[1]] = $m[2];
                }
            }
            usort($provincias, static fn ($a, $b) => strcmp($a['provincia'], $b['provincia']));
        }

        if (preg_match_all("/\((\d+),\s*(\d+),\s*'([^']+)'\)/", $content, $lm, PREG_SET_ORDER)) {
            foreach ($lm as $m) {
                $pId           = (int) $m[2];
                $localidades[] = [
                    'id'           => (int) $m[1],
                    'id_privincia' => $pId,
                    'localidad'    => $m[3],
                    'provincia'    => $provMap[$pId] ?? '',
                ];
            }
            usort($localidades, static fn ($a, $b) => strcmp($a['localidad'], $b['localidad']));
        }

        return ['provincias' => $provincias, 'localidades' => $localidades];
    }
}
