<?php

namespace App\Models;

use CodeIgniter\Model;

class LocalidadModel extends Model
{
    protected $table = 'localidades';

    private static ?array $provinciasCache = null;
    private static ?array $todasCache      = null;

    public static function clearCache(): void
    {
        self::$provinciasCache = null;
        self::$todasCache      = null;
    }

    public static function foldText(string $text): string
    {
        $clean = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
        $map   = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'Á' => 'a', 'À' => 'a', 'Ä' => 'a', 'Â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'É' => 'e', 'È' => 'e', 'Ë' => 'e', 'Ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'Í' => 'i', 'Ì' => 'i', 'Ï' => 'i', 'Î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'Ó' => 'o', 'Ò' => 'o', 'Ö' => 'o', 'Ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'Ú' => 'u', 'Ù' => 'u', 'Ü' => 'u', 'Û' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
        ];
        return mb_strtolower(strtr($clean, $map), 'UTF-8');
    }

    public function getProvincias(): array
    {
        if (self::$provinciasCache !== null) {
            return self::$provinciasCache;
        }
        try {
            $res = \Config\Database::connect()->table('provincias')->orderBy('provincia', 'ASC')->get()->getResultArray();
            if (!empty($res)) {
                return self::$provinciasCache = $res;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::getProvincias: ' . $e->getMessage());
        }
        return self::$provinciasCache = $this->parseSqlFallback()['provincias'];
    }

    public function getTodasConProvincia(): array
    {
        if (self::$todasCache !== null) {
            return self::$todasCache;
        }
        try {
            $res = \Config\Database::connect()->table('localidades l')
                ->select('l.id, l.id_privincia, l.localidad, p.provincia')
                ->join('provincias p', 'p.id = l.id_privincia', 'left')
                ->orderBy('l.localidad', 'ASC')
                ->get()->getResultArray();
            if (!empty($res)) {
                return self::$todasCache = $this->limpiarLocalidades($res);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error en LocalidadModel::getTodasConProvincia: ' . $e->getMessage());
        }
        return self::$todasCache = $this->limpiarLocalidades($this->parseSqlFallback()['localidades']);
    }

    public function provinciaExiste(string $provincia): ?string
    {
        $target = self::foldText($provincia);
        if ($target === '') {
            return null;
        }
        foreach ($this->getProvincias() as $row) {
            $name = (string) ($row['provincia'] ?? '');
            if ($name !== '' && self::foldText($name) === $target) {
                return $name;
            }
        }
        return null;
    }

    public function resolverEnProvincia(string $provincia, string $ciudad): ?array
    {
        $provCanonical = $this->provinciaExiste($provincia);
        $targetCiudad  = self::foldText($ciudad);
        if ($provCanonical === null || $targetCiudad === '') {
            return null;
        }
        $targetProv = self::foldText($provCanonical);
        foreach ($this->getTodasConProvincia() as $loc) {
            if (self::foldText((string) ($loc['provincia'] ?? '')) !== $targetProv) {
                continue;
            }
            $locNombre = (string) ($loc['localidad'] ?? '');
            if (self::foldText($locNombre) === $targetCiudad) {
                return ['provincia' => $provCanonical, 'ciudad' => $locNombre];
            }
        }
        return null;
    }

    public function buscar(?string $search = null, ?int $provinciaId = null, ?string $provinciaNombre = null, int $limit = 0): array
    {
        $foldedSearch = $search !== null ? self::foldText($search) : '';
        $foldedProv   = $provinciaNombre !== null ? self::foldText($provinciaNombre) : '';

        $filtered = array_filter($this->getTodasConProvincia(), static function (array $loc) use ($foldedSearch, $provinciaId, $foldedProv): bool {
            if ($provinciaId !== null && $provinciaId > 0 && (int) $loc['id_privincia'] !== $provinciaId) {
                return false;
            }
            if (($provinciaId === null || $provinciaId <= 0) && $foldedProv !== '' && self::foldText((string) ($loc['provincia'] ?? '')) !== $foldedProv) {
                return false;
            }
            return $foldedSearch === '' || str_contains(self::foldText((string) ($loc['localidad'] ?? '')), $foldedSearch);
        });

        $values = array_values($filtered);
        return $limit > 0 ? array_slice($values, 0, $limit) : $values;
    }

    private function limpiarLocalidades(array $rows): array
    {
        $clean = [];
        $seen  = [];
        foreach ($rows as $r) {
            $loc  = trim((string) ($r['localidad'] ?? ''));
            $prov = trim((string) ($r['provincia'] ?? ''));
            if ($loc === '' || $prov === '' || preg_match('/^\d+$/', $loc)) {
                continue;
            }
            $key = self::foldText($prov) . '|' . self::foldText($loc);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key]       = true;
            $r['localidad']   = $loc;
            $r['provincia']   = $prov;
            $clean[]          = $r;
        }
        return $clean;
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
