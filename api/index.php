<?php

// Punto de entrada para despliegue serverless en Vercel
foreach (['VERCEL' => '1', 'CI_ENVIRONMENT' => getenv('CI_ENVIRONMENT') ?: 'development'] as $k => $v) {
    putenv("{$k}={$v}");
    $_ENV[$k] = $_SERVER[$k] = $v;
}

@ini_set('session.gc_maxlifetime', '31536000');
@ini_set('session.cookie_lifetime', '31536000');

$setEnv = static function (string $key, string $val): void {
    putenv("{$key}={$val}");
    $_ENV[$key] = $_SERVER[$key] = $val;
};

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../public');
$_SERVER['SCRIPT_NAME']   = '/index.php';

$tmp       = sys_get_temp_dir();
$targetDir = rtrim((!empty($tmp) && is_dir($tmp)) ? $tmp : '/tmp', '\\/') . DIRECTORY_SEPARATOR . 'materiax_writable';
foreach (['', 'cache', 'logs', 'session', 'uploads', 'debugbar'] as $sub) {
    $dir = $sub === '' ? $targetDir : $targetDir . DIRECTORY_SEPARATOR . $sub;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$dbPath = $targetDir . DIRECTORY_SEPARATOR . 'materiax_db.sqlite';
$seedDb = __DIR__ . '/../writable/materiax_db.sqlite';
if ((!file_exists($dbPath) || filesize($dbPath) === 0) && file_exists($seedDb) && filesize($seedDb) > 0) {
    @copy($seedDb, $dbPath);
}

$resolveEnv = static function (array $keys): ?string {
    foreach ($keys as $key) {
        foreach ([getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null] as $val) {
            if ($val !== false && $val !== null && trim((string) $val) !== '') {
                return trim((string) $val);
            }
        }
    }
    return null;
};

$ensureSchemaAndSeed = static function (PDO $pdo, string $driver): void {
    $d = [
        'pgsql'  => ['pk' => 'id SERIAL PRIMARY KEY', 'int' => 'INTEGER', 'tiny' => 'SMALLINT', 'dec' => 'NUMERIC', 'ts' => 'TIMESTAMP', 'tsU' => '', 'opt' => '', 'ins' => 'INSERT', 'cU' => ' ON CONFLICT (email) DO NOTHING', 'cI' => ' ON CONFLICT (id) DO NOTHING', 'cC' => ' ON CONFLICT (sigla) DO NOTHING'],
        'mysql'  => ['pk' => 'id INT AUTO_INCREMENT PRIMARY KEY', 'int' => 'INT', 'tiny' => 'TINYINT(1)', 'dec' => 'DECIMAL', 'ts' => 'DATETIME', 'tsU' => ' ON UPDATE CURRENT_TIMESTAMP', 'opt' => ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', 'ins' => 'INSERT IGNORE', 'cU' => '', 'cI' => '', 'cC' => ''],
        'sqlite' => ['pk' => 'id INTEGER PRIMARY KEY AUTOINCREMENT', 'int' => 'INTEGER', 'tiny' => 'INTEGER', 'dec' => 'REAL', 'ts' => 'DATETIME', 'tsU' => '', 'opt' => '', 'ins' => 'INSERT OR IGNORE', 'cU' => '', 'cI' => '', 'cC' => ''],
    ][$driver];

    $tables = [
        "usuarios ({$d['pk']}, nombre VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, cuit VARCHAR(20) NOT NULL, telefono VARCHAR(30) NOT NULL, rubro VARCHAR(100) DEFAULT NULL, ciudad VARCHAR(100) DEFAULT NULL, provincia VARCHAR(100) DEFAULT NULL, direccion VARCHAR(150) DEFAULT NULL, rol VARCHAR(50) NOT NULL DEFAULT 'empresa', estado VARCHAR(20) NOT NULL DEFAULT 'activo', created_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP, updated_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP{$d['tsU']}, ultimo_login {$d['ts']} DEFAULT NULL)",
        "plantas_industriales ({$d['pk']}, empresa_id {$d['int']} NOT NULL, nombre_planta VARCHAR(100) NOT NULL, direccion VARCHAR(150) NOT NULL, localidad VARCHAR(100) NOT NULL, provincia VARCHAR(100) NOT NULL DEFAULT 'Córdoba', coordenadas_gps VARCHAR(50) DEFAULT NULL, posee_bascula {$d['tiny']} NOT NULL DEFAULT 1, capacidad_carga_camiones VARCHAR(50) DEFAULT 'Hasta 45 Ton', contacto_responsable VARCHAR(100) DEFAULT NULL, telefono_planta VARCHAR(30) DEFAULT NULL, activa {$d['tiny']} NOT NULL DEFAULT 1, created_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP, updated_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP{$d['tsU']})",
        "productos ({$d['pk']}, user_id {$d['int']} NOT NULL, planta_id {$d['int']} DEFAULT NULL, categoria_id {$d['int']} DEFAULT NULL, nombre VARCHAR(150) NOT NULL, tipo_polimero VARCHAR(50) NOT NULL, cantidad_kg {$d['dec']}(10,2) NOT NULL, cantidad_disp_kg {$d['dec']}(10,2) DEFAULT NULL, precio_unitario {$d['dec']}(10,2) NOT NULL, ubicacion VARCHAR(100) NOT NULL, descripcion TEXT DEFAULT NULL, estado VARCHAR(20) NOT NULL DEFAULT 'Disponible', presentacion VARCHAR(50) DEFAULT 'scrap_molido', color VARCHAR(50) DEFAULT 'Negro', fluidez_mfi {$d['dec']}(6,2) DEFAULT NULL, contaminacion_pct {$d['dec']}(5,2) DEFAULT 0.00, pedido_minimo_kg {$d['dec']}(10,2) DEFAULT 100.00, moneda VARCHAR(10) DEFAULT 'ARS', acondicionamiento VARCHAR(100) DEFAULT 'Big Bags de 1.000 kg', created_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP, updated_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP{$d['tsU']})",
        "transacciones ({$d['pk']}, codigo_operacion VARCHAR(20) NOT NULL UNIQUE, producto_id {$d['int']} NOT NULL, comprador_id {$d['int']} NOT NULL, vendedor_id {$d['int']} NOT NULL, planta_origen_id {$d['int']} DEFAULT NULL, cantidad_kg {$d['dec']}(10,2) NOT NULL, precio_unitario_pactado {$d['dec']}(10,2) NOT NULL, subtotal {$d['dec']}(12,2) NOT NULL, costo_flete {$d['dec']}(10,2) NOT NULL DEFAULT 0.00, monto_total {$d['dec']}(12,2) NOT NULL, modalidad_retiro VARCHAR(50) NOT NULL DEFAULT 'retiro_comprador_planta', estado VARCHAR(50) NOT NULL DEFAULT 'solicitada', ticket_balanza_kg {$d['dec']}(10,2) DEFAULT NULL, numero_remito VARCHAR(50) DEFAULT NULL, observaciones TEXT DEFAULT NULL, fecha_solicitud {$d['ts']} NOT NULL DEFAULT CURRENT_TIMESTAMP, fecha_acuerdo {$d['ts']} DEFAULT NULL, fecha_despacho {$d['ts']} DEFAULT NULL, fecha_cierre {$d['ts']} DEFAULT NULL)",
        "certificados_ambientales ({$d['pk']}, codigo_certificado VARCHAR(40) NOT NULL UNIQUE, transaccion_id {$d['int']} NOT NULL UNIQUE, generadora_id {$d['int']} NOT NULL, revalorizadora_id {$d['int']} NOT NULL, kg_recuperados {$d['dec']}(10,2) NOT NULL, co2_evitado_kg {$d['dec']}(10,2) NOT NULL, mwh_ahorrado {$d['dec']}(10,2) NOT NULL DEFAULT 0.00, norma_referencia VARCHAR(100) NOT NULL DEFAULT 'Economía Circular / Protocolo GHG Alcance 3', fecha_emision {$d['ts']} NOT NULL DEFAULT CURRENT_TIMESTAMP, firmado_digitalmente {$d['tiny']} NOT NULL DEFAULT 1)",
        "mensajes_negociacion ({$d['pk']}, producto_id {$d['int']} NOT NULL, transaccion_id {$d['int']} DEFAULT NULL, emisor_id {$d['int']} NOT NULL, receptor_id {$d['int']} NOT NULL, mensaje TEXT NOT NULL, leido {$d['tiny']} NOT NULL DEFAULT 0, fecha_lectura {$d['ts']} DEFAULT NULL, created_at {$d['ts']} DEFAULT CURRENT_TIMESTAMP)",
        "categorias_polimero ({$d['pk']}, codigo_spi {$d['int']} NOT NULL, sigla VARCHAR(10) NOT NULL UNIQUE, nombre_tecnico VARCHAR(100) NOT NULL, densidad_g_cm3 {$d['dec']}(5,3) NOT NULL, temp_fusion_c {$d['int']} DEFAULT NULL, factor_co2_kg {$d['dec']}(4,2) NOT NULL DEFAULT 1.85, reciclabilidad VARCHAR(20) NOT NULL DEFAULT 'alta', descripcion_tecnica TEXT DEFAULT NULL, activo {$d['tiny']} NOT NULL DEFAULT 1)",
    ];
    if ($driver === 'sqlite') {
        $tables[] = "provincias ({$d['pk']}, provincia VARCHAR(255) NOT NULL)";
        $tables[] = "localidades ({$d['pk']}, id_privincia INTEGER NOT NULL, localidad VARCHAR(255) NOT NULL)";
    }
    foreach ($tables as $ddl) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$ddl}{$d['opt']};");
    }
    if ($driver === 'sqlite') {
        try {
            $pdo->query('SELECT planta_id FROM productos LIMIT 1');
        } catch (Throwable) {
            $pdo->exec('ALTER TABLE productos ADD COLUMN planta_id INTEGER DEFAULT NULL');
        }
    }

    $seeds = [
        'usuarios' => [
            "{$d['ins']} INTO usuarios (id, nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, created_at, updated_at, ultimo_login) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?){$d['cU']}",
            [
                [1, 'Petroquímica Río Tercero S.A.', 'admin@materiax.com', '$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6', '30-50284912-8', '+54 3571 42-1500', 'Petroquímica & Polímeros', 'Río Tercero', 'Córdoba', 'Parque Industrial Química Sur, Lote 12', 'empresa', 'activo', '2026-09-16 16:38:41', '2026-09-22 16:41:26', null],
                [2, 'Industrias Plásticas Río III', 'rio3@plastico.com', '$2y$10$tN23Ye.DfVln6.k/m7mGnOiVn141EczoDwIre6tndR62c0wZV/1YC', '30-71123456-9', '+54 3571 430000', null, null, null, null, 'empresa', 'activo', '2026-09-16 19:45:44', '2026-09-16 19:45:44', null],
                [3, 'Plásticos del Valle Renovado S.A.', 'plastec4708@argentina.com', '$2y$10$6ssHN5X0hsCZ/PsxRmHHZ.e5j2HHp1YzLfxLQ79f/7D4HQmd..dPi', '30-71884708-2', '+54 3571 888888', 'Reciclado & Granza', 'Almafuerte', 'Córdoba', 'Ruta 36 Km 115', 'empresa', 'activo', '2026-09-22 19:46:58', '2026-09-22 19:47:00', '2026-09-22 19:47:00'],
                [4, 'Administrador General MateriaX', 'myadminpro@gmail.com', '$2y$12$Z3IsqEqgyyjuMraeBb/./enYfY3KaHNmyznQNv7LdqoW3aVA/0Nfa', '20-00000000-0', '+54 3571 00-0000', 'Administración Central', 'Río Tercero', 'Córdoba', 'Sede Central MateriaX', 'admin', 'activo', '2026-09-22 20:31:35', '2026-09-22 22:02:31', '2026-09-22 22:02:31'],
                [5, 'plasticoss', 'manurisolo2@gmail.com', '$2y$10$VoEZdsFNVtoQcDOr6q52FOhfe0RmDuLR3ns8KkcngfowtFPc0T3MK', '23492887269', '3571435390', 'Soplado de Cuerpos Huecos', 'riotercrereo', 'Córdoba', 'mm', 'empresa', 'activo', '2026-09-22 20:56:09', '2026-09-22 20:56:09', '2026-09-22 20:56:09'],
                [6, 'Industrias pro', 'boongraxd@gmail.com', '$2y$10$D186hUo7jNvi4rHde.6JfOFRlIupy95DRLVsjYJVCpQGBuVTJbz3e', '23492887269', '3571345390', 'Compuestos & Masterbatch', 'rio tercero', 'Córdoba', 'aca en la 2 de arbil', 'empresa', 'activo', '2026-09-22 21:54:26', '2026-09-22 21:54:26', '2026-09-22 21:54:26'],
            ],
        ],
        'plantas_industriales' => [
            "{$d['ins']} INTO plantas_industriales (id, empresa_id, nombre_planta, direccion, localidad, provincia, coordenadas_gps, posee_bascula, capacidad_carga_camiones, contacto_responsable, telefono_planta, activa) VALUES (?,?,?,?,?,?,?,?,?,?,?,?){$d['cI']}",
            [
                [1, 1, 'Sede Operativa y Fiscal Central', 'Av. San Martín 450', 'Río Tercero', 'Córdoba', '-32.1732,-64.1141', 1, 'Hasta 45 Ton', 'Ing. Carlos Rossi', '+54 3571 42-1501', 1],
                [2, 2, 'Centro de Molienda Río III', 'Parque Industrial Química Sur, Lote 4', 'Río Tercero', 'Córdoba', '-32.1800,-64.1200', 1, 'Hasta 30 Ton', 'Téc. Lucas Benítez', '+54 3571 43-0001', 1],
                [3, 3, 'Planta Almafuerte Reciclados', 'Ruta 36 Km 115', 'Almafuerte', 'Córdoba', '-32.1950,-64.2500', 1, 'Hasta 45 Ton', 'Mariana Gómez', '+54 3571 88-8889', 1],
            ],
        ],
        'productos' => [
            "{$d['ins']} INTO productos (id, user_id, planta_id, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?){$d['cI']}",
            [
                [2, 1, 1, 'Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800, 1420, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
                [3, 1, 1, 'Merma de Bobinas PVC Cristal Flexible', 'PVC', 950, 2100, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
                [4, 1, 1, 'ABS Granulado Color Gris Reciclado', 'ABS', 750, 2300.5, 'Córdoba Capital, Parque Industrial Ferreyra', 'Granulado ABS de descarte limpio de electrodomésticos. Fluidez media, apto inyección.', 'Disponible', '2026-09-16 19:44:22', '2026-09-16 19:44:22'],
                [5, 1, 1, 'PET Cristal Molido Botella', 'PET', 3200, 980, 'Río Tercero, Córdoba', 'Flakes de botellas PET transparentes, lavado en caliente con sosa cáustica.', 'Disponible', '2026-09-16 19:45:26', '2026-09-16 19:45:26'],
            ],
        ],
        'categorias_polimero' => [
            "{$d['ins']} INTO categorias_polimero (id, codigo_spi, sigla, nombre_tecnico, densidad_g_cm3, temp_fusion_c, factor_co2_kg, reciclabilidad, descripcion_tecnica, activo) VALUES (?,?,?,?,?,?,?,?,?,?){$d['cC']}",
            [
                [1, 1, 'PET', 'Polietileno Tereftalato', 1.380, 260, 2.15, 'alta', 'Envases cristal, botellas y film soplado.', 1],
                [2, 2, 'PEAD', 'Polietileno de Alta Densidad (HDPE)', 0.955, 130, 1.90, 'alta', 'Bidones químicos, tuberías, cajones y tapas.', 1],
                [3, 3, 'PVC', 'Policloruro de Vinilo', 1.400, 180, 1.70, 'media', 'Cañerías, perfiles para aberturas y aislamiento de cables.', 1],
                [4, 4, 'PEBD', 'Polietileno de Baja Densidad (LDPE)', 0.920, 110, 1.80, 'alta', 'Film termocontraíble, bolsas y soplado.', 1],
                [5, 5, 'PP', 'Polipropileno Homopolímero / Copolímero', 0.905, 165, 1.85, 'alta', 'Inyección automotriz, envases de pared delgada, big bags y fibras.', 1],
                [6, 6, 'PS', 'Poliestireno Alto Impacto (HIPS / EPS)', 1.050, 240, 1.75, 'media', 'Termoformado de bandejas, aislación térmica y bazar.', 1],
                [7, 7, 'ABS', 'Acrilonitrilo Butadieno Estireno', 1.060, 220, 2.50, 'especial', 'Polímero de ingeniería para carcasas electrodomésticas y automoción.', 1],
            ],
        ],
    ];

    foreach ($seeds as $table => [$sql, $rows]) {
        if ((int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn() === 0) {
            $stmt = $pdo->prepare($sql);
            foreach ($rows as $row) {
                $stmt->execute($row);
            }
        }
    }

    if ($driver === 'pgsql') {
        foreach (['usuarios', 'plantas_industriales', 'productos', 'transacciones', 'certificados_ambientales', 'mensajes_negociacion', 'categorias_polimero'] as $tbl) {
            $pdo->exec("SELECT setval(pg_get_serial_sequence('{$tbl}', 'id'), COALESCE((SELECT MAX(id) FROM {$tbl}), 1), true);");
        }
    }
};

// 1. Supabase / PostgreSQL
$pgUrl  = $resolveEnv(['POSTGRES_URL_NON_POOLING', 'POSTGRES_URL', 'SUPABASE_DB_URL', 'DATABASE_URL']);
$pgHost = $resolveEnv(['POSTGRES_HOST', 'SUPABASE_HOST', 'PGHOST']);
$pgUser = $resolveEnv(['POSTGRES_USER', 'SUPABASE_USER', 'PGUSER']);
$pgPass = $resolveEnv(['POSTGRES_PASSWORD', 'SUPABASE_PASSWORD', 'PGPASSWORD']);
$pgName = $resolveEnv(['POSTGRES_DATABASE', 'SUPABASE_DATABASE', 'PGDATABASE']);
$pgPort = (int) ($resolveEnv(['POSTGRES_PORT', 'SUPABASE_PORT', 'PGPORT']) ?: 0);

if (!empty($pgUrl) && preg_match('/^postgre(s|sql):\/\/|^pgsql:\/\//i', $pgUrl) && ($p = parse_url($pgUrl))) {
    $pgHost = $pgHost ?: ($p['host'] ?? null);
    $pgUser = $pgUser ?: (isset($p['user']) ? urldecode($p['user']) : null);
    $pgPass = $pgPass ?: (isset($p['pass']) ? urldecode($p['pass']) : null);
    $pgPort = $pgPort ?: (int) ($p['port'] ?? 5432);
    $pgName = $pgName ?: (!empty($p['path']) ? ltrim($p['path'], '/') : 'postgres');
}
if (!empty($pgHost)) {
    if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/i', $pgHost, $m)) {
        $pgHost = $resolveEnv(['SUPABASE_POOLER_HOST']) ?: 'aws-1-sa-east-1.pooler.supabase.com';
        $pgPort = 5432;
        $pgUser = (empty($pgUser) || $pgUser === 'postgres') ? 'postgres.' . $m[1] : $pgUser;
    } elseif (str_contains($pgHost, 'pooler.supabase.com') && $pgPort === 6543) {
        $pgPort = 5432;
    }
}

if (!empty($pgHost)) {
    $pgDown   = $targetDir . DIRECTORY_SEPARATOR . '.pg_down_' . md5($pgHost . ($pgUser ?? '') . ($pgName ?? 'postgres'));
    $pgMarker = $targetDir . DIRECTORY_SEPARATOR . '.pg_init_' . md5($pgHost . ($pgName ?? 'postgres'));
    if (is_file($pgDown) && (time() - (int) @filemtime($pgDown)) < 60) {
        $setEnv('MATERIAX_PG_UNAVAILABLE', '1');
        $setEnv('MATERIAX_PG_ERROR', trim((string) @file_get_contents($pgDown)) ?: 'PostgreSQL no disponible temporalmente.');
        $pgHost = null;
    } else {
        try {
            $pdo = new PDO(
                'pgsql:host=' . $pgHost . ';port=' . ($pgPort ?: 5432) . ';dbname=' . ($pgName ?: 'postgres') . ';sslmode=require',
                $pgUser ?: 'postgres',
                $pgPass ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
            if (!file_exists($pgMarker)) {
                $ensureSchemaAndSeed($pdo, 'pgsql');
                @file_put_contents($pgMarker, (string) time());
            }
            if (is_file($pgDown)) {
                @unlink($pgDown);
            }
            $setEnv('MATERIAX_PG_VERIFIED', '1');
        } catch (Throwable $e) {
            error_log('Supabase PostgreSQL initialization error: ' . $e->getMessage());
            @file_put_contents($pgDown, $e->getMessage());
            if (is_file($pgMarker)) {
                @unlink($pgMarker);
            }
            $setEnv('MATERIAX_PG_UNAVAILABLE', '1');
            $setEnv('MATERIAX_PG_ERROR', $e->getMessage());
            $pgHost = null;
        }
    }
}

// 2. Cloud MySQL / TiDB
$dbUrl     = $resolveEnv(['DATABASE_URL', 'TIDB_URL', 'MYSQL_URL', 'JAWSDB_URL', 'CLEARDB_DATABASE_URL']);
$cloudHost = $resolveEnv(['TIDB_HOST', 'DB_HOST', 'MYSQL_HOST', 'MYSQLHOST', 'DATABASE_HOST']);
$cloudUser = $resolveEnv(['TIDB_USER', 'DB_USER', 'MYSQL_USER', 'MYSQLUSER', 'DATABASE_USER']);
$cloudPass = $resolveEnv(['TIDB_PASSWORD', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQLPASSWORD', 'DATABASE_PASSWORD']);
$cloudName = $resolveEnv(['TIDB_DATABASE', 'DB_DATABASE', 'MYSQL_DATABASE', 'MYSQLDATABASE', 'DATABASE_NAME']);
$cloudPort = (int) ($resolveEnv(['TIDB_PORT', 'DB_PORT', 'MYSQL_PORT', 'MYSQLPORT']) ?: 0);

if (!empty($dbUrl) && preg_match('/^mysqli?:\/\//i', $dbUrl) && ($p = parse_url($dbUrl))) {
    $cloudHost = $cloudHost ?: ($p['host'] ?? null);
    $cloudUser = $cloudUser ?: (isset($p['user']) ? urldecode($p['user']) : null);
    $cloudPass = $cloudPass ?: (isset($p['pass']) ? urldecode($p['pass']) : null);
    $cloudPort = $cloudPort ?: (int) ($p['port'] ?? 4000);
    $cloudName = $cloudName ?: (!empty($p['path']) ? ltrim($p['path'], '/') : null);
}

if (!empty($cloudHost) && empty($pgHost)) {
    $cloudDown   = $targetDir . DIRECTORY_SEPARATOR . '.cloud_down_' . md5($cloudHost . ($cloudUser ?? '') . ($cloudName ?? ''));
    $cloudMarker = $targetDir . DIRECTORY_SEPARATOR . '.cloud_init_' . md5($cloudHost . ($cloudName ?? ''));
    if (is_file($cloudDown) && (time() - (int) @filemtime($cloudDown)) < 60) {
        $setEnv('MATERIAX_CLOUD_UNAVAILABLE', '1');
        $setEnv('MATERIAX_CLOUD_ERROR', trim((string) @file_get_contents($cloudDown)) ?: 'MySQL en la nube no disponible temporalmente.');
        $cloudHost = null;
    } else {
        try {
            $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3];
            if ($resolveEnv(['DB_SSL']) || $resolveEnv(['TIDB_HOST']) || str_contains(($dbUrl ?? '') . $cloudHost, 'tidbcloud.com')) {
                $ca = realpath(__DIR__ . '/../writable/cacert.pem');
                if ($ca && file_exists($ca)) {
                    $opts[PDO::MYSQL_ATTR_SSL_CA] = $ca;
                }
                $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            $pdo = new PDO(
                'mysql:host=' . $cloudHost . ';port=' . ($cloudPort ?: 4000) . ';dbname=' . ($cloudName ?: ($resolveEnv(['TIDB_HOST']) ? 'test' : 'materiax_db')) . ';charset=utf8mb4',
                $cloudUser ?: 'root',
                $cloudPass ?: '',
                $opts
            );
            if (!file_exists($cloudMarker)) {
                $ensureSchemaAndSeed($pdo, 'mysql');
                @file_put_contents($cloudMarker, (string) time());
            }
            if (is_file($cloudDown)) {
                @unlink($cloudDown);
            }
            $setEnv('MATERIAX_CLOUD_VERIFIED', '1');
        } catch (Throwable $e) {
            error_log('Cloud database initialization error: ' . $e->getMessage());
            @file_put_contents($cloudDown, $e->getMessage());
            if (is_file($cloudMarker)) {
                @unlink($cloudMarker);
            }
            $setEnv('MATERIAX_CLOUD_UNAVAILABLE', '1');
            $setEnv('MATERIAX_CLOUD_ERROR', $e->getMessage());
            $cloudHost = null;
        }
    }
}

// 3. Fallback SQLite
$sqliteMarker = $targetDir . DIRECTORY_SEPARATOR . '.sqlite_init_v2';
if (empty($pgHost) && empty($cloudHost) && (!file_exists($sqliteMarker) || !file_exists($dbPath) || filesize($dbPath) === 0)) {
    try {
        $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        @$pdo->exec('PRAGMA journal_mode = WAL;');
        @$pdo->exec('PRAGMA busy_timeout = 5000;');
        $ensureSchemaAndSeed($pdo, 'sqlite');
        @file_put_contents($sqliteMarker, (string) time());
    } catch (Throwable $e) {
        error_log('Database initialization error: ' . $e->getMessage());
    }
}

require __DIR__ . '/../public/index.php';
