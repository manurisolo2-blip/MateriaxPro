<?php

// Vercel Serverless Function Entry Point for CodeIgniter 4
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// Set CI_ENVIRONMENT to development so that any unexpected error provides full diagnostic trace instead of opaque screen
if (!getenv('CI_ENVIRONMENT')) {
    putenv('CI_ENVIRONMENT=development');
    $_ENV['CI_ENVIRONMENT'] = 'development';
    $_SERVER['CI_ENVIRONMENT'] = 'development';
}

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../public');
if (empty($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Ensure writable directories and SQLite database exist in the system temp directory
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

// Populate database in target directory
$dbPath = $targetDir . DIRECTORY_SEPARATOR . 'materiax_db.sqlite';
$seedDb = __DIR__ . '/../writable/materiax_db.sqlite';

if (!file_exists($dbPath) || filesize($dbPath) === 0) {
    if (file_exists($seedDb) && filesize($seedDb) > 0) {
        @copy($seedDb, $dbPath);
    }
}

// Bulletproof self-healing: if file does not exist or has no tables, create and seed via PDO directly
if (!file_exists($dbPath) || filesize($dbPath) === 0) {
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

        $db->exec("CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            nombre VARCHAR(150) NOT NULL,
            tipo_polimero VARCHAR(50) NOT NULL,
            cantidad_kg REAL NOT NULL,
            precio_unitario REAL NOT NULL,
            ubicacion VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado VARCHAR(20) DEFAULT 'Disponible',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

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

        $pStmt = $db->prepare("INSERT OR IGNORE INTO productos (id, user_id, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $products = [
            [2, 1, 'Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800, 1420, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
            [3, 1, 'Merma de Bobinas PVC Cristal Flexible', 'PVC', 950, 2100, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'],
            [4, 1, 'ABS Granulado Color Gris Reciclado', 'ABS', 750, 2300.5, 'Córdoba Capital, Parque Industrial Ferreyra', 'Granulado ABS de descarte limpio de electrodomésticos. Fluidez media, apto inyección.', 'Disponible', '2026-09-16 19:44:22', '2026-09-16 19:44:22'],
            [5, 1, 'PET Cristal Molido Botella', 'PET', 3200, 980, 'Río Tercero, Córdoba', 'Flakes de botellas PET transparentes, lavado en caliente con sosa cáustica.', 'Disponible', '2026-09-16 19:45:26', '2026-09-16 19:45:26']
        ];
        foreach ($products as $p) {
            $pStmt->execute($p);
        }
    } catch (Throwable $e) {
        error_log('Database initialization error: ' . $e->getMessage());
    }
}

// Forward execution to CodeIgniter 4 Front Controller
require __DIR__ . '/../public/index.php';
