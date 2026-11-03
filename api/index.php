<?php

// Punto de entrada para despliegue en Vercel
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// Configurar entorno en modo desarrollo
if (!getenv('CI_ENVIRONMENT')) {
    putenv('CI_ENVIRONMENT=development');
    $_ENV['CI_ENVIRONMENT'] = 'development';
    $_SERVER['CI_ENVIRONMENT'] = 'development';
}

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../public');
if (empty($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Crear directorios temporales de escritura
$tmp = sys_get_temp_dir();
$baseWritable = (!empty($tmp) && is_dir($tmp)) ? $tmp : '/tmp';
$targetDir = rtrim($baseWritable, '\\/') . DIRECTORY_SEPARATOR . 'materiax_writable';
if (!is_dir($targetDir)) {
    @mkdir($targetDir, 0777, true);
}
foreach (['cache', 'logs', 'session', 'uploads', 'debugbar'] as $sub) {
    $subDir = $targetDir . DIRECTORY_SEPARATOR . $sub;
    if (!is_dir($subDir)) {
        @mkdir($subDir, 0777, true);
    }
}

// Inicializar base de datos SQLite en directorio temporal (solo se usa si no hay Supabase ni MySQL)
$dbPath = $targetDir . DIRECTORY_SEPARATOR . 'materiax_db.sqlite';
$seedDb = __DIR__ . '/../writable/materiax_db.sqlite';

if (!file_exists($dbPath) || filesize($dbPath) === 0) {
    if (file_exists($seedDb) && filesize($seedDb) > 0) {
        @copy($seedDb, $dbPath);
    }
}

// Helper para resolver variables de entorno en cualquier contexto (getenv, $_ENV, $_SERVER)
$resolveEnv = static function (array $keys): ?string {
    foreach ($keys as $key) {
        $val = getenv($key);
        if ($val !== false && $val !== null && trim((string)$val) !== '') {
            return trim((string)$val);
        }
        if (!empty($_ENV[$key]) && trim((string)$_ENV[$key]) !== '') {
            return trim((string)$_ENV[$key]);
        }
        if (!empty($_SERVER[$key]) && trim((string)$_SERVER[$key]) !== '') {
            return trim((string)$_SERVER[$key]);
        }
    }
    return null;
};

// 1. Auto-aprovisionamiento de Supabase / PostgreSQL si está configurado
$pgUrl  = $resolveEnv(['POSTGRES_URL_NON_POOLING', 'POSTGRES_URL', 'SUPABASE_DB_URL', 'DATABASE_URL']);
$pgHost = $resolveEnv(['POSTGRES_HOST', 'SUPABASE_HOST', 'PGHOST']);
$pgUser = $resolveEnv(['POSTGRES_USER', 'SUPABASE_USER', 'PGUSER']);
$pgPass = $resolveEnv(['POSTGRES_PASSWORD', 'SUPABASE_PASSWORD', 'PGPASSWORD']);
$pgName = $resolveEnv(['POSTGRES_DATABASE', 'SUPABASE_DATABASE', 'PGDATABASE']);
$pgPort = (int) ($resolveEnv(['POSTGRES_PORT', 'SUPABASE_PORT', 'PGPORT']) ?: 0);

$isPostgresUrl = !empty($pgUrl) && (
    str_starts_with($pgUrl, 'postgres://')
    || str_starts_with($pgUrl, 'postgresql://')
    || str_starts_with($pgUrl, 'pgsql://')
);

if ($isPostgresUrl) {
    $parsed = parse_url($pgUrl);
    $pgHost = $pgHost ?: ($parsed['host'] ?? null);
    $pgUser = $pgUser ?: (isset($parsed['user']) ? urldecode($parsed['user']) : null);
    $pgPass = $pgPass ?: (isset($parsed['pass']) ? urldecode($parsed['pass']) : null);
    $pgPort = $pgPort ?: (int) ($parsed['port'] ?? 5432);
    $pgName = $pgName ?: (!empty($parsed['path']) ? ltrim($parsed['path'], '/') : 'postgres');
}

if (!empty($pgHost)) {
    if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/i', $pgHost, $m)) {
        $projectRef = $m[1];
        $pgHost     = $resolveEnv(['SUPABASE_POOLER_HOST']) ?: 'aws-1-sa-east-1.pooler.supabase.com';
        $pgPort     = 5432;
        if (empty($pgUser) || $pgUser === 'postgres') {
            $pgUser = 'postgres.' . $projectRef;
        }
    } elseif (str_contains($pgHost, 'pooler.supabase.com') && $pgPort === 6543) {
        $pgPort = 5432;
    }
}

$pgInitMarker = !empty($pgHost) ? ($targetDir . DIRECTORY_SEPARATOR . '.pg_init_' . md5($pgHost . ($pgName ?? 'postgres'))) : '';
if (!empty($pgHost) && !file_exists($pgInitMarker)) {
    try {
        $pgUser = $pgUser ?: 'postgres';
        $pgPass = $pgPass ?? '';
        $pgName = $pgName ?: 'postgres';
        $pgPort = $pgPort ?: 5432;

        $pgPdo = new PDO(
            "pgsql:host={$pgHost};port={$pgPort};dbname={$pgName};sslmode=require",
            $pgUser,
            $pgPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]
        );

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            cuit VARCHAR(20) NOT NULL,
            telefono VARCHAR(30) NOT NULL,
            rubro VARCHAR(100) DEFAULT NULL,
            ciudad VARCHAR(100) DEFAULT NULL,
            provincia VARCHAR(100) DEFAULT NULL,
            direccion VARCHAR(150) DEFAULT NULL,
            rol VARCHAR(50) NOT NULL DEFAULT 'empresa',
            estado VARCHAR(20) NOT NULL DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ultimo_login TIMESTAMP DEFAULT NULL
        );");

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS plantas_industriales (
            id SERIAL PRIMARY KEY,
            empresa_id INTEGER NOT NULL,
            nombre_planta VARCHAR(100) NOT NULL,
            direccion VARCHAR(150) NOT NULL,
            localidad VARCHAR(100) NOT NULL,
            provincia VARCHAR(100) NOT NULL DEFAULT 'Córdoba',
            coordenadas_gps VARCHAR(50) DEFAULT NULL,
            posee_bascula SMALLINT NOT NULL DEFAULT 1,
            capacidad_carga_camiones VARCHAR(50) DEFAULT 'Hasta 45 Ton',
            contacto_responsable VARCHAR(100) DEFAULT NULL,
            telefono_planta VARCHAR(30) DEFAULT NULL,
            activa SMALLINT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS productos (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            planta_id INTEGER DEFAULT NULL,
            categoria_id INTEGER DEFAULT NULL,
            nombre VARCHAR(150) NOT NULL,
            tipo_polimero VARCHAR(50) NOT NULL,
            cantidad_kg NUMERIC(10,2) NOT NULL,
            cantidad_disp_kg NUMERIC(10,2) DEFAULT NULL,
            precio_unitario NUMERIC(10,2) NOT NULL,
            ubicacion VARCHAR(100) NOT NULL,
            descripcion TEXT DEFAULT NULL,
            estado VARCHAR(20) NOT NULL DEFAULT 'Disponible',
            presentacion VARCHAR(50) DEFAULT 'scrap_molido',
            color VARCHAR(50) DEFAULT 'Negro',
            fluidez_mfi NUMERIC(6,2) DEFAULT NULL,
            contaminacion_pct NUMERIC(5,2) DEFAULT 0.00,
            pedido_minimo_kg NUMERIC(10,2) DEFAULT 100.00,
            moneda VARCHAR(10) DEFAULT 'ARS',
            acondicionamiento VARCHAR(100) DEFAULT 'Big Bags de 1.000 kg',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS transacciones (
            id SERIAL PRIMARY KEY,
            codigo_operacion VARCHAR(20) NOT NULL UNIQUE,
            producto_id INTEGER NOT NULL,
            comprador_id INTEGER NOT NULL,
            vendedor_id INTEGER NOT NULL,
            planta_origen_id INTEGER DEFAULT NULL,
            cantidad_kg NUMERIC(10,2) NOT NULL,
            precio_unitario_pactado NUMERIC(10,2) NOT NULL,
            subtotal NUMERIC(12,2) NOT NULL,
            costo_flete NUMERIC(10,2) NOT NULL DEFAULT 0.00,
            monto_total NUMERIC(12,2) NOT NULL,
            modalidad_retiro VARCHAR(50) NOT NULL DEFAULT 'retiro_comprador_planta',
            estado VARCHAR(50) NOT NULL DEFAULT 'solicitada',
            ticket_balanza_kg NUMERIC(10,2) DEFAULT NULL,
            numero_remito VARCHAR(50) DEFAULT NULL,
            observaciones TEXT DEFAULT NULL,
            fecha_solicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_acuerdo TIMESTAMP DEFAULT NULL,
            fecha_despacho TIMESTAMP DEFAULT NULL,
            fecha_cierre TIMESTAMP DEFAULT NULL
        );");

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS certificados_ambientales (
            id SERIAL PRIMARY KEY,
            codigo_certificado VARCHAR(40) NOT NULL UNIQUE,
            transaccion_id INTEGER NOT NULL UNIQUE,
            generadora_id INTEGER NOT NULL,
            revalorizadora_id INTEGER NOT NULL,
            kg_recuperados NUMERIC(10,2) NOT NULL,
            co2_evitado_kg NUMERIC(10,2) NOT NULL,
            mwh_ahorrado NUMERIC(10,2) NOT NULL DEFAULT 0.00,
            norma_referencia VARCHAR(100) NOT NULL DEFAULT 'Economía Circular / Protocolo GHG Alcance 3',
            fecha_emision TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            firmado_digitalmente SMALLINT NOT NULL DEFAULT 1
        );");

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS mensajes_negociacion (
            id SERIAL PRIMARY KEY,
            producto_id INTEGER NOT NULL,
            transaccion_id INTEGER DEFAULT NULL,
            emisor_id INTEGER NOT NULL,
            receptor_id INTEGER NOT NULL,
            mensaje TEXT NOT NULL,
            leido SMALLINT NOT NULL DEFAULT 0,
            fecha_lectura TIMESTAMP DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pgPdo->exec("CREATE TABLE IF NOT EXISTS categorias_polimero (
            id SERIAL PRIMARY KEY,
            codigo_spi INTEGER NOT NULL,
            sigla VARCHAR(10) NOT NULL UNIQUE,
            nombre_tecnico VARCHAR(100) NOT NULL,
            densidad_g_cm3 NUMERIC(5,3) NOT NULL,
            temp_fusion_c INTEGER DEFAULT NULL,
            factor_co2_kg NUMERIC(4,2) NOT NULL DEFAULT 1.85,
            reciclabilidad VARCHAR(20) NOT NULL DEFAULT 'alta',
            descripcion_tecnica TEXT DEFAULT NULL,
            activo SMALLINT NOT NULL DEFAULT 1
        );");

        $uCount = (int) $pgPdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($uCount === 0) {
            $uStmt = $pgPdo->prepare("INSERT INTO usuarios (id, nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, created_at, updated_at, ultimo_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (email) DO NOTHING");
            $initialUsers = [
                [1, 'Petroquímica Río Tercero S.A.', 'admin@materiax.com', '$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6', '30-50284912-8', '+54 3571 42-1500', 'Petroquímica & Polímeros', 'Río Tercero', 'Córdoba', 'Parque Industrial Química Sur, Lote 12', 'empresa', 'activo', '2026-09-16 16:38:41', '2026-09-22 16:41:26', null],
                [2, 'Industrias Plásticas Río III', 'rio3@plastico.com', '$2y$10$tN23Ye.DfVln6.k/m7mGnOiVn141EczoDwIre6tndR62c0wZV/1YC', '30-71123456-9', '+54 3571 430000', null, null, null, null, 'empresa', 'activo', '2026-09-16 19:45:44', '2026-09-16 19:45:44', null],
                [3, 'Plásticos del Valle Renovado S.A.', 'plastec4708@argentina.com', '$2y$10$6ssHN5X0hsCZ/PsxRmHHZ.e5j2HHp1YzLfxLQ79f/7D4HQmd..dPi', '30-71884708-2', '+54 3571 888888', 'Reciclado & Granza', 'Almafuerte', 'Córdoba', 'Ruta 36 Km 115', 'empresa', 'activo', '2026-09-22 19:46:58', '2026-09-22 19:47:00', '2026-09-22 19:47:00'],
                [4, 'Administrador General MateriaX', 'myadminpro@gmail.com', '$2y$12$Z3IsqEqgyyjuMraeBb/./enYfY3KaHNmyznQNv7LdqoW3aVA/0Nfa', '20-00000000-0', '+54 3571 00-0000', 'Administración Central', 'Río Tercero', 'Córdoba', 'Sede Central MateriaX', 'admin', 'activo', '2026-09-22 20:31:35', '2026-09-22 22:02:31', '2026-09-22 22:02:31'],
                [5, 'plasticoss', 'manurisolo2@gmail.com', '$2y$10$VoEZdsFNVtoQcDOr6q52FOhfe0RmDuLR3ns8KkcngfowtFPc0T3MK', '23492887269', '3571435390', 'Soplado de Cuerpos Huecos', 'riotercrereo', 'Córdoba', 'mm', 'empresa', 'activo', '2026-09-22 20:56:09', '2026-09-22 20:56:09', '2026-09-22 20:56:09'],
                [6, 'Industrias pro', 'boongraxd@gmail.com', '$2y$10$D186hUo7jNvi4rHde.6JfOFRlIupy95DRLVsjYJVCpQGBuVTJbz3e', '23492887269', '3571345390', 'Compuestos & Masterbatch', 'rio tercero', 'Córdoba', 'aca en la 2 de arbil', 'empresa', 'activo', '2026-09-22 21:54:26', '2026-09-22 21:54:26', '2026-09-22 21:54:26']
            ];
            foreach ($initialUsers as $u) {
                $uStmt->execute($u);
            }

            $plantStmt = $pgPdo->prepare("INSERT INTO plantas_industriales (id, empresa_id, nombre_planta, direccion, localidad, provincia, coordenadas_gps, posee_bascula, capacidad_carga_camiones, contacto_responsable, telefono_planta, activa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING");
            $plants = [
                [1, 1, 'Sede Operativa y Fiscal Central', 'Av. San Martín 450', 'Río Tercero', 'Córdoba', '-32.1732,-64.1141', 1, 'Hasta 45 Ton', 'Ing. Carlos Rossi', '+54 3571 42-1501', 1],
                [2, 2, 'Centro de Molienda Río III', 'Parque Industrial Química Sur, Lote 4', 'Río Tercero', 'Córdoba', '-32.1800,-64.1200', 1, 'Hasta 30 Ton', 'Téc. Lucas Benítez', '+54 3571 43-0001', 1],
                [3, 3, 'Planta Almafuerte Reciclados', 'Ruta 36 Km 115', 'Almafuerte', 'Córdoba', '-32.1950,-64.2500', 1, 'Hasta 45 Ton', 'Mariana Gómez', '+54 3571 88-8889', 1]
            ];
            foreach ($plants as $pl) {
                $plantStmt->execute($pl);
            }

            $pStmt = $pgPdo->prepare("INSERT INTO productos (id, user_id, planta_id, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING");
            $products = [
                [2, 1, 1, 'Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800, 1420, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
                [3, 1, 1, 'Merma de Bobinas PVC Cristal Flexible', 'PVC', 950, 2100, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
                [4, 1, 1, 'ABS Granulado Color Gris Reciclado', 'ABS', 750, 2300.5, 'Córdoba Capital, Parque Industrial Ferreyra', 'Granulado ABS de descarte limpio de electrodomésticos. Fluidez media, apto inyección.', 'Disponible', '2026-09-16 19:44:22', '2026-09-16 19:44:22'],
                [5, 1, 1, 'PET Cristal Molido Botella', 'PET', 3200, 980, 'Río Tercero, Córdoba', 'Flakes de botellas PET transparentes, lavado en caliente con sosa cáustica.', 'Disponible', '2026-09-16 19:45:26', '2026-09-16 19:45:26']
            ];
            foreach ($products as $p) {
                $pStmt->execute($p);
            }

            $catStmt = $pgPdo->prepare("INSERT INTO categorias_polimero (id, codigo_spi, sigla, nombre_tecnico, densidad_g_cm3, temp_fusion_c, factor_co2_kg, reciclabilidad, descripcion_tecnica, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (sigla) DO NOTHING");
            $categories = [
                [1, 1, 'PET', 'Polietileno Tereftalato', 1.380, 260, 2.15, 'alta', 'Envases cristal, botellas y film soplado.', 1],
                [2, 2, 'PEAD', 'Polietileno de Alta Densidad (HDPE)', 0.955, 130, 1.90, 'alta', 'Bidones químicos, tuberías, cajones y tapas.', 1],
                [3, 3, 'PVC', 'Policloruro de Vinilo', 1.400, 180, 1.70, 'media', 'Cañerías, perfiles para aberturas y aislamiento de cables.', 1],
                [4, 4, 'PEBD', 'Polietileno de Baja Densidad (LDPE)', 0.920, 110, 1.80, 'alta', 'Film termocontraíble, bolsas y soplado.', 1],
                [5, 5, 'PP', 'Polipropileno Homopolímero / Copolímero', 0.905, 165, 1.85, 'alta', 'Inyección automotriz, envases de pared delgada, big bags y fibras.', 1],
                [6, 6, 'PS', 'Poliestireno Alto Impacto (HIPS / EPS)', 1.050, 240, 1.75, 'media', 'Termoformado de bandejas, aislación térmica y bazar.', 1],
                [7, 7, 'ABS', 'Acrilonitrilo Butadieno Estireno', 1.060, 220, 2.50, 'especial', 'Polímero de ingeniería para carcasas electrodomésticas y automoción.', 1]
            ];
            foreach ($categories as $cat) {
                $catStmt->execute($cat);
            }
        }

        // Sincronizar secuencias SERIAL de PostgreSQL para que nuevos registros no colisionen con los IDs iniciales
        foreach (['usuarios', 'plantas_industriales', 'productos', 'transacciones', 'certificados_ambientales', 'mensajes_negociacion', 'categorias_polimero'] as $tbl) {
            $pgPdo->exec("SELECT setval(pg_get_serial_sequence('{$tbl}', 'id'), COALESCE((SELECT MAX(id) FROM {$tbl}), 1), true);");
        }

        @file_put_contents($pgInitMarker, (string) time());
    } catch (Throwable $e) {
        error_log('Supabase PostgreSQL initialization error: ' . $e->getMessage());
    }
}

// 2. Si se configuró base de datos MySQL en la nube (TiDB Cloud, Aiven, Clever Cloud, etc.), auto-provisionar tablas
$dbUrl     = $resolveEnv(['DATABASE_URL', 'TIDB_URL', 'MYSQL_URL', 'JAWSDB_URL', 'CLEARDB_DATABASE_URL']);
$cloudHost = $resolveEnv(['TIDB_HOST', 'DB_HOST', 'MYSQL_HOST', 'MYSQLHOST', 'DATABASE_HOST']);
$cloudUser = $resolveEnv(['TIDB_USER', 'DB_USER', 'MYSQL_USER', 'MYSQLUSER', 'DATABASE_USER']);
$cloudPass = $resolveEnv(['TIDB_PASSWORD', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQLPASSWORD', 'DATABASE_PASSWORD']);
$cloudName = $resolveEnv(['TIDB_DATABASE', 'DB_DATABASE', 'MYSQL_DATABASE', 'MYSQLDATABASE', 'DATABASE_NAME']);
$cloudPort = (int) ($resolveEnv(['TIDB_PORT', 'DB_PORT', 'MYSQL_PORT', 'MYSQLPORT']) ?: 0);

if (!empty($dbUrl) && (str_starts_with($dbUrl, 'mysql://') || str_starts_with($dbUrl, 'mysqli://'))) {
    $parsed = parse_url($dbUrl);
    $cloudHost = $cloudHost ?: ($parsed['host'] ?? null);
    $cloudUser = $cloudUser ?: (isset($parsed['user']) ? urldecode($parsed['user']) : null);
    $cloudPass = $cloudPass ?: (isset($parsed['pass']) ? urldecode($parsed['pass']) : null);
    $cloudPort = $cloudPort ?: (int) ($parsed['port'] ?? 4000);
    $cloudName = $cloudName ?: (!empty($parsed['path']) ? ltrim($parsed['path'], '/') : null);
}

$cloudInitMarker = (!empty($cloudHost) && empty($pgHost)) ? ($targetDir . DIRECTORY_SEPARATOR . '.cloud_init_' . md5($cloudHost . ($cloudName ?? ''))) : '';
if (!empty($cloudHost) && empty($pgHost) && !file_exists($cloudInitMarker)) {
    try {
        $cloudUser = $cloudUser ?: 'root';
        $cloudPass = $cloudPass ?: '';
        $cloudName = $cloudName ?: ($resolveEnv(['TIDB_HOST']) ? 'test' : 'materiax_db');
        $cloudPort = $cloudPort ?: 4000;

        $pdoOpts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ];
        $needsSsl = $resolveEnv(['DB_SSL']) || $resolveEnv(['TIDB_HOST']) || (!empty($dbUrl) && str_contains($dbUrl, 'tidbcloud.com')) || (!empty($cloudHost) && str_contains($cloudHost, 'tidbcloud.com'));
        if ($needsSsl) {
            $caFile = realpath(__DIR__ . '/../writable/cacert.pem');
            if ($caFile && file_exists($caFile)) {
                $pdoOpts[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
            }
            $pdoOpts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $cloudPdo = new PDO("mysql:host={$cloudHost};port={$cloudPort};dbname={$cloudName};charset=utf8mb4", $cloudUser, $cloudPass, $pdoOpts);

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            cuit VARCHAR(20) NOT NULL,
            telefono VARCHAR(30) NOT NULL,
            rubro VARCHAR(100) NULL,
            ciudad VARCHAR(100) NULL,
            provincia VARCHAR(100) NULL,
            direccion VARCHAR(150) NULL,
            rol VARCHAR(50) NOT NULL DEFAULT 'empresa',
            estado VARCHAR(20) NOT NULL DEFAULT 'activo',
            created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ultimo_login DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS plantas_industriales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            empresa_id INT NOT NULL,
            nombre_planta VARCHAR(100) NOT NULL,
            direccion VARCHAR(150) NOT NULL,
            localidad VARCHAR(100) NOT NULL,
            provincia VARCHAR(100) NOT NULL DEFAULT 'Córdoba',
            coordenadas_gps VARCHAR(50) DEFAULT NULL,
            posee_bascula TINYINT(1) NOT NULL DEFAULT 1,
            capacidad_carga_camiones VARCHAR(50) DEFAULT 'Hasta 45 Ton',
            contacto_responsable VARCHAR(100) DEFAULT NULL,
            telefono_planta VARCHAR(30) DEFAULT NULL,
            activa TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS productos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            planta_id INT DEFAULT NULL,
            categoria_id INT DEFAULT NULL,
            nombre VARCHAR(150) NOT NULL,
            tipo_polimero VARCHAR(50) NOT NULL,
            cantidad_kg DECIMAL(10,2) NOT NULL,
            cantidad_disp_kg DECIMAL(10,2) DEFAULT NULL,
            precio_unitario DECIMAL(10,2) NOT NULL,
            ubicacion VARCHAR(100) NOT NULL,
            descripcion TEXT NULL,
            estado VARCHAR(20) NOT NULL DEFAULT 'Disponible',
            presentacion VARCHAR(50) DEFAULT 'scrap_molido',
            color VARCHAR(50) DEFAULT 'Negro',
            fluidez_mfi DECIMAL(6,2) DEFAULT NULL,
            contaminacion_pct DECIMAL(5,2) DEFAULT 0.00,
            pedido_minimo_kg DECIMAL(10,2) DEFAULT 100.00,
            moneda VARCHAR(10) DEFAULT 'ARS',
            acondicionamiento VARCHAR(100) DEFAULT 'Big Bags de 1.000 kg',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS transacciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_operacion VARCHAR(20) NOT NULL UNIQUE,
            producto_id INT NOT NULL,
            comprador_id INT NOT NULL,
            vendedor_id INT NOT NULL,
            planta_origen_id INT DEFAULT NULL,
            cantidad_kg DECIMAL(10,2) NOT NULL,
            precio_unitario_pactado DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(12,2) NOT NULL,
            costo_flete DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            monto_total DECIMAL(12,2) NOT NULL,
            modalidad_retiro VARCHAR(50) NOT NULL DEFAULT 'retiro_comprador_planta',
            estado VARCHAR(50) NOT NULL DEFAULT 'solicitada',
            ticket_balanza_kg DECIMAL(10,2) DEFAULT NULL,
            numero_remito VARCHAR(50) DEFAULT NULL,
            observaciones TEXT DEFAULT NULL,
            fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_acuerdo DATETIME DEFAULT NULL,
            fecha_despacho DATETIME DEFAULT NULL,
            fecha_cierre DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS certificados_ambientales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_certificado VARCHAR(40) NOT NULL UNIQUE,
            transaccion_id INT NOT NULL UNIQUE,
            generadora_id INT NOT NULL,
            revalorizadora_id INT NOT NULL,
            kg_recuperados DECIMAL(10,2) NOT NULL,
            co2_evitado_kg DECIMAL(10,2) NOT NULL,
            mwh_ahorrado DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            norma_referencia VARCHAR(100) NOT NULL DEFAULT 'Economía Circular / Protocolo GHG Alcance 3',
            fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            firmado_digitalmente TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS mensajes_negociacion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            producto_id INT NOT NULL,
            transaccion_id INT DEFAULT NULL,
            emisor_id INT NOT NULL,
            receptor_id INT NOT NULL,
            mensaje TEXT NOT NULL,
            leido TINYINT(1) NOT NULL DEFAULT 0,
            fecha_lectura DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $cloudPdo->exec("CREATE TABLE IF NOT EXISTS categorias_polimero (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_spi INT NOT NULL,
            sigla VARCHAR(10) NOT NULL UNIQUE,
            nombre_tecnico VARCHAR(100) NOT NULL,
            densidad_g_cm3 DECIMAL(5,3) NOT NULL,
            temp_fusion_c INT DEFAULT NULL,
            factor_co2_kg DECIMAL(4,2) NOT NULL DEFAULT 1.85,
            reciclabilidad VARCHAR(20) NOT NULL DEFAULT 'alta',
            descripcion_tecnica TEXT DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $uCount = (int) $cloudPdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($uCount === 0) {
            $uStmt = $cloudPdo->prepare("INSERT IGNORE INTO usuarios (id, nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, created_at, updated_at, ultimo_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $initialUsers = [
                [1, 'Petroquímica Río Tercero S.A.', 'admin@materiax.com', '$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6', '30-50284912-8', '+54 3571 42-1500', 'Petroquímica & Polímeros', 'Río Tercero', 'Córdoba', 'Parque Industrial Química Sur, Lote 12', 'empresa', 'activo', '2026-09-16 16:38:41', '2026-09-22 16:41:26', null],
                [2, 'Industrias Plásticas Río III', 'rio3@plastico.com', '$2y$10$tN23Ye.DfVln6.k/m7mGnOiVn141EczoDwIre6tndR62c0wZV/1YC', '30-71123456-9', '+54 3571 430000', null, null, null, null, 'empresa', 'activo', '2026-09-16 19:45:44', '2026-09-16 19:45:44', null],
                [3, 'Plásticos del Valle Renovado S.A.', 'plastec4708@argentina.com', '$2y$10$6ssHN5X0hsCZ/PsxRmHHZ.e5j2HHp1YzLfxLQ79f/7D4HQmd..dPi', '30-71884708-2', '+54 3571 888888', 'Reciclado & Granza', 'Almafuerte', 'Córdoba', 'Ruta 36 Km 115', 'empresa', 'activo', '2026-09-22 19:46:58', '2026-09-22 19:47:00', '2026-09-22 19:47:00'],
                [4, 'Administrador General MateriaX', 'myadminpro@gmail.com', '$2y$12$Z3IsqEqgyyjuMraeBb/./enYfY3KaHNmyznQNv7LdqoW3aVA/0Nfa', '20-00000000-0', '+54 3571 00-0000', 'Administración Central', 'Río Tercero', 'Córdoba', 'Sede Central MateriaX', 'admin', 'activo', '2026-09-22 20:31:35', '2026-09-22 22:02:31', '2026-09-22 22:02:31'],
                [5, 'plasticoss', 'manurisolo2@gmail.com', '$2y$10$VoEZdsFNVtoQcDOr6q52FOhfe0RmDuLR3ns8KkcngfowtFPc0T3MK', '23492887269', '3571435390', 'Soplado de Cuerpos Huecos', 'riotercrereo', 'Córdoba', 'mm', 'empresa', 'activo', '2026-09-22 20:56:09', '2026-09-22 20:56:09', '2026-09-22 20:56:09'],
                [6, 'Industrias pro', 'boongraxd@gmail.com', '$2y$10$D186hUo7jNvi4rHde.6JfOFRlIupy95DRLVsjYJVCpQGBuVTJbz3e', '23492887269', '3571345390', 'Compuestos & Masterbatch', 'rio tercero', 'Córdoba', 'aca en la 2 de arbil', 'empresa', 'activo', '2026-09-22 21:54:26', '2026-09-22 21:54:26', '2026-09-22 21:54:26']
            ];
            foreach ($initialUsers as $u) {
                $uStmt->execute($u);
            }
        }
        @file_put_contents($cloudInitMarker, (string) time());
    } catch (Throwable $e) {
        error_log('Cloud database initialization error: ' . $e->getMessage());
    }
}

// 3. Garantizar que todas las tablas y columnas necesarias existan en SQLite (Fallback si no hay Supabase ni MySQL)
$sqliteInitMarker = $targetDir . DIRECTORY_SEPARATOR . '.sqlite_init_v2';
if (empty($pgHost) && empty($cloudHost) && (!file_exists($sqliteInitMarker) || !file_exists($dbPath) || filesize($dbPath) === 0)) {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            cuit VARCHAR(20) NOT NULL,
            telefono VARCHAR(30) NOT NULL,
            rubro VARCHAR(100),
            ciudad VARCHAR(100),
            provincia VARCHAR(100),
            direccion VARCHAR(150),
            rol VARCHAR(50) DEFAULT 'empresa',
            estado VARCHAR(20) DEFAULT 'pendiente',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ultimo_login DATETIME
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS plantas_industriales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            empresa_id INTEGER NOT NULL,
            nombre_planta VARCHAR(100) NOT NULL,
            direccion VARCHAR(150) NOT NULL,
            localidad VARCHAR(100) NOT NULL,
            provincia VARCHAR(100) NOT NULL DEFAULT 'Córdoba',
            coordenadas_gps VARCHAR(50) DEFAULT NULL,
            posee_bascula INTEGER NOT NULL DEFAULT 1,
            capacidad_carga_camiones VARCHAR(50) DEFAULT 'Hasta 45 Ton',
            contacto_responsable VARCHAR(100) DEFAULT NULL,
            telefono_planta VARCHAR(30) DEFAULT NULL,
            activa INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            planta_id INTEGER DEFAULT NULL,
            categoria_id INTEGER DEFAULT NULL,
            nombre VARCHAR(150) NOT NULL,
            tipo_polimero VARCHAR(50) NOT NULL,
            cantidad_kg REAL NOT NULL,
            cantidad_disp_kg REAL DEFAULT NULL,
            precio_unitario REAL NOT NULL,
            ubicacion VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado VARCHAR(20) DEFAULT 'Disponible',
            presentacion VARCHAR(50) DEFAULT 'scrap_molido',
            color VARCHAR(50) DEFAULT 'Negro',
            fluidez_mfi REAL DEFAULT NULL,
            contaminacion_pct REAL DEFAULT 0.00,
            pedido_minimo_kg REAL DEFAULT 100.00,
            moneda VARCHAR(10) DEFAULT 'ARS',
            acondicionamiento VARCHAR(100) DEFAULT 'Big Bags de 1.000 kg',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        try {
            $db->query("SELECT planta_id FROM productos LIMIT 1");
        } catch (Throwable $e) {
            $db->exec("ALTER TABLE productos ADD COLUMN planta_id INTEGER DEFAULT NULL");
        }

        $db->exec("CREATE TABLE IF NOT EXISTS transacciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_operacion VARCHAR(20) NOT NULL UNIQUE,
            producto_id INTEGER NOT NULL,
            comprador_id INTEGER NOT NULL,
            vendedor_id INTEGER NOT NULL,
            planta_origen_id INTEGER DEFAULT NULL,
            cantidad_kg REAL NOT NULL,
            precio_unitario_pactado REAL NOT NULL,
            subtotal REAL NOT NULL,
            costo_flete REAL NOT NULL DEFAULT 0.00,
            monto_total REAL NOT NULL,
            modalidad_retiro VARCHAR(50) NOT NULL DEFAULT 'retiro_comprador_planta',
            estado VARCHAR(50) NOT NULL DEFAULT 'solicitada',
            ticket_balanza_kg REAL DEFAULT NULL,
            numero_remito VARCHAR(50) DEFAULT NULL,
            observaciones TEXT DEFAULT NULL,
            fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_acuerdo DATETIME DEFAULT NULL,
            fecha_despacho DATETIME DEFAULT NULL,
            fecha_cierre DATETIME DEFAULT NULL
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS certificados_ambientales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_certificado VARCHAR(40) NOT NULL UNIQUE,
            transaccion_id INTEGER NOT NULL UNIQUE,
            generadora_id INTEGER NOT NULL,
            revalorizadora_id INTEGER NOT NULL,
            kg_recuperados REAL NOT NULL,
            co2_evitado_kg REAL NOT NULL,
            mwh_ahorrado REAL NOT NULL DEFAULT 0.00,
            norma_referencia VARCHAR(100) NOT NULL DEFAULT 'Economía Circular / Protocolo GHG Alcance 3',
            fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            firmado_digitalmente INTEGER NOT NULL DEFAULT 1
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS categorias_polimero (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_spi INTEGER NOT NULL,
            sigla VARCHAR(10) NOT NULL UNIQUE,
            nombre_tecnico VARCHAR(100) NOT NULL,
            densidad_g_cm3 REAL NOT NULL,
            temp_fusion_c INTEGER DEFAULT NULL,
            factor_co2_kg REAL NOT NULL DEFAULT 1.85,
            reciclabilidad VARCHAR(20) NOT NULL DEFAULT 'alta',
            descripcion_tecnica TEXT DEFAULT NULL,
            activo INTEGER NOT NULL DEFAULT 1
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS provincias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            provincia VARCHAR(255) NOT NULL
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS localidades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_privincia INTEGER NOT NULL,
            localidad VARCHAR(255) NOT NULL
        );");

        $db->exec("CREATE TABLE IF NOT EXISTS mensajes_negociacion (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            producto_id INTEGER NOT NULL,
            transaccion_id INTEGER DEFAULT NULL,
            emisor_id INTEGER NOT NULL,
            receptor_id INTEGER NOT NULL,
            mensaje TEXT NOT NULL,
            leido INTEGER NOT NULL DEFAULT 0,
            fecha_lectura DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $userCount = (int) $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($userCount === 0) {
            $uStmt = $db->prepare("INSERT OR IGNORE INTO usuarios (id, nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, created_at, updated_at, ultimo_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $users = [
                [1, 'Petroquímica Río Tercero S.A.', 'admin@materiax.com', '$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6', '30-50284912-8', '+54 3571 42-1500', 'Petroquímica & Polímeros', 'Río Tercero', 'Córdoba', 'Parque Industrial Química Sur, Lote 12', 'empresa', 'activo', '2026-09-16 16:38:41', '2026-09-22 16:41:26', null],
                [2, 'Industrias Plásticas Río III', 'rio3@plastico.com', '$2y$10$tN23Ye.DfVln6.k/m7mGnOiVn141EczoDwIre6tndR62c0wZV/1YC', '30-71123456-9', '+54 3571 430000', null, null, null, null, 'empresa', 'activo', '2026-09-16 19:45:44', '2026-09-16 19:45:44', null],
                [3, 'Plásticos del Valle Renovado S.A.', 'plastec4708@argentina.com', '$2y$10$6ssHN5X0hsCZ/PsxRmHHZ.e5j2HHp1YzLfxLQ79f/7D4HQmd..dPi', '30-71884708-2', '+54 3571 888888', 'Reciclado & Granza', 'Almafuerte', 'Córdoba', 'Ruta 36 Km 115', 'empresa', 'activo', '2026-09-22 19:46:58', '2026-09-22 19:47:00', '2026-09-22 19:47:00'],
                [4, 'Administrador General MateriaX', 'myadminpro@gmail.com', '$2y$12$Z3IsqEqgyyjuMraeBb/./enYfY3KaHNmyznQNv7LdqoW3aVA/0Nfa', '20-00000000-0', '+54 3571 00-0000', 'Administración Central', 'Río Tercero', 'Córdoba', 'Sede Central MateriaX', 'admin', 'activo', '2026-09-22 20:31:35', '2026-09-22 22:02:31', '2026-09-22 22:02:31'],
                [5, 'plasticoss', 'manurisolo2@gmail.com', '$2y$10$VoEZdsFNVtoQcDOr6q52FOhfe0RmDuLR3ns8KkcngfowtFPc0T3MK', '23492887269', '3571435390', 'Soplado de Cuerpos Huecos', 'riotercrereo', 'Córdoba', 'mm', 'empresa', 'activo', '2026-09-22 20:56:09', '2026-09-22 20:56:09', '2026-09-22 20:56:09'],
                [6, 'Industrias pro', 'boongraxd@gmail.com', '$2y$10$D186hUo7jNvi4rHde.6JfOFRlIupy95DRLVsjYJVCpQGBuVTJbz3e', '23492887269', '3571345390', 'Compuestos & Masterbatch', 'rio tercero', 'Córdoba', 'aca en la 2 de arbil', 'empresa', 'activo', '2026-09-22 21:54:26', '2026-09-22 21:54:26', '2026-09-22 21:54:26']
            ];
            foreach ($users as $u) {
                $uStmt->execute($u);
            }
        }

        $prodCount = (int) $db->query("SELECT COUNT(*) FROM productos")->fetchColumn();
        if ($prodCount === 0) {
            $pStmt = $db->prepare("INSERT OR IGNORE INTO productos (id, user_id, planta_id, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $products = [
                [2, 1, 1, 'Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800, 1420, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
                [3, 1, 1, 'Merma de Bobinas PVC Cristal Flexible', 'PVC', 950, 2100, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
                [4, 1, 1, 'ABS Granulado Color Gris Reciclado', 'ABS', 750, 2300.5, 'Córdoba Capital, Parque Industrial Ferreyra', 'Granulado ABS de descarte limpio de electrodomésticos. Fluidez media, apto inyección.', 'Disponible', '2026-09-16 19:44:22', '2026-09-16 19:44:22'],
                [5, 1, 1, 'PET Cristal Molido Botella', 'PET', 3200, 980, 'Río Tercero, Córdoba', 'Flakes de botellas PET transparentes, lavado en caliente con sosa cáustica.', 'Disponible', '2026-09-16 19:45:26', '2026-09-16 19:45:26']
            ];
            foreach ($products as $p) {
                $pStmt->execute($p);
            }
        }

        $plantaCount = (int) $db->query("SELECT COUNT(*) FROM plantas_industriales")->fetchColumn();
        if ($plantaCount === 0) {
            $plantStmt = $db->prepare("INSERT OR IGNORE INTO plantas_industriales (id, empresa_id, nombre_planta, direccion, localidad, provincia, coordenadas_gps, posee_bascula, capacidad_carga_camiones, contacto_responsable, telefono_planta, activa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $plants = [
                [1, 1, 'Sede Operativa y Fiscal Central', 'Av. San Martín 450', 'Río Tercero', 'Córdoba', '-32.1732,-64.1141', 1, 'Hasta 45 Ton', 'Ing. Carlos Rossi', '+54 3571 42-1501', 1],
                [2, 2, 'Centro de Molienda Río III', 'Parque Industrial Química Sur, Lote 4', 'Río Tercero', 'Córdoba', '-32.1800,-64.1200', 1, 'Hasta 30 Ton', 'Téc. Lucas Benítez', '+54 3571 43-0001', 1],
                [3, 3, 'Planta Almafuerte Reciclados', 'Ruta 36 Km 115', 'Almafuerte', 'Córdoba', '-32.1950,-64.2500', 1, 'Hasta 45 Ton', 'Mariana Gómez', '+54 3571 88-8889', 1]
            ];
            foreach ($plants as $pl) {
                $plantStmt->execute($pl);
            }
        }

        $catCount = (int) $db->query("SELECT COUNT(*) FROM categorias_polimero")->fetchColumn();
        if ($catCount === 0) {
            $catStmt = $db->prepare("INSERT OR IGNORE INTO categorias_polimero (id, codigo_spi, sigla, nombre_tecnico, densidad_g_cm3, temp_fusion_c, factor_co2_kg, reciclabilidad, descripcion_tecnica, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $categories = [
                [1, 1, 'PET', 'Polietileno Tereftalato', 1.380, 260, 2.15, 'alta', 'Envases cristal, botellas y film soplado.', 1],
                [2, 2, 'PEAD', 'Polietileno de Alta Densidad (HDPE)', 0.955, 130, 1.90, 'alta', 'Bidones químicos, tuberías, cajones y tapas.', 1],
                [3, 3, 'PVC', 'Policloruro de Vinilo', 1.400, 180, 1.70, 'media', 'Cañerías, perfiles para aberturas y aislamiento de cables.', 1],
                [4, 4, 'PEBD', 'Polietileno de Baja Densidad (LDPE)', 0.920, 110, 1.80, 'alta', 'Film termocontraíble, bolsas y soplado.', 1],
                [5, 5, 'PP', 'Polipropileno Homopolímero / Copolímero', 0.905, 165, 1.85, 'alta', 'Inyección automotriz, envases de pared delgada, big bags y fibras.', 1],
                [6, 6, 'PS', 'Poliestireno Alto Impacto (HIPS / EPS)', 1.050, 240, 1.75, 'media', 'Termoformado de bandejas, aislación térmica y bazar.', 1],
                [7, 7, 'ABS', 'Acrilonitrilo Butadieno Estireno', 1.060, 220, 2.50, 'especial', 'Polímero de ingeniería para carcasas electrodomésticas y automoción.', 1]
            ];
            foreach ($categories as $cat) {
                $catStmt->execute($cat);
            }
        }
        @file_put_contents($sqliteInitMarker, (string) time());
    } catch (Throwable $e) {
        error_log('Database initialization error: ' . $e->getMessage());
    }
}

// Redirigir la ejecución al controlador de CodeIgniter 4
require __DIR__ . '/../public/index.php';
