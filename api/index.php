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

// Inicializar base de datos SQLite en directorio temporal
$dbPath = $targetDir . DIRECTORY_SEPARATOR . 'materiax_db.sqlite';
$seedDb = __DIR__ . '/../writable/materiax_db.sqlite';

if (!file_exists($dbPath) || filesize($dbPath) === 0) {
    if (file_exists($seedDb) && filesize($seedDb) > 0) {
        @copy($seedDb, $dbPath);
    }
}

// Garantizar que todas las tablas y columnas necesarias existan (Hito 1 y Hito 2)
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

    // Datos semilla de usuarios si está vacía
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

    // Datos semilla de productos si está vacía
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

    // Plantas por defecto si está vacía
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

    // Categorías de polímero si está vacía
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
} catch (Throwable $e) {
    error_log('Database initialization error: ' . $e->getMessage());
}

// Redirigir la ejecución al controlador de CodeIgniter 4
require __DIR__ . '/../public/index.php';
