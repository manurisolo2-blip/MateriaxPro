-- =============================================================================
-- MateriaX Pro — Esquema Oficial para Supabase (PostgreSQL)
-- Nota: El sistema también crea y puebla estas tablas de forma automática
-- al detectar POSTGRES_URL o DATABASE_URL en Vercel.
-- =============================================================================

CREATE TABLE IF NOT EXISTS usuarios (
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
);

CREATE TABLE IF NOT EXISTS plantas_industriales (
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
);

CREATE TABLE IF NOT EXISTS productos (
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
);

CREATE TABLE IF NOT EXISTS transacciones (
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
);

CREATE TABLE IF NOT EXISTS certificados_ambientales (
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
);

CREATE TABLE IF NOT EXISTS mensajes_negociacion (
    id SERIAL PRIMARY KEY,
    producto_id INTEGER NOT NULL,
    transaccion_id INTEGER DEFAULT NULL,
    emisor_id INTEGER NOT NULL,
    receptor_id INTEGER NOT NULL,
    mensaje TEXT NOT NULL,
    leido SMALLINT NOT NULL DEFAULT 0,
    fecha_lectura TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias_polimero (
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
);

-- Usuarios iniciales
INSERT INTO usuarios (id, nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, created_at, updated_at, ultimo_login) VALUES
(1, 'Petroquímica Río Tercero S.A.', 'admin@materiax.com', '$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6', '30-50284912-8', '+54 3571 42-1500', 'Petroquímica & Polímeros', 'Río Tercero', 'Córdoba', 'Parque Industrial Química Sur, Lote 12', 'empresa', 'activo', '2026-09-16 16:38:41', '2026-09-22 16:41:26', NULL),
(2, 'Industrias Plásticas Río III', 'rio3@plastico.com', '$2y$10$tN23Ye.DfVln6.k/m7mGnOiVn141EczoDwIre6tndR62c0wZV/1YC', '30-71123456-9', '+54 3571 430000', NULL, NULL, NULL, NULL, 'empresa', 'activo', '2026-09-16 19:45:44', '2026-09-16 19:45:44', NULL),
(3, 'Plásticos del Valle Renovado S.A.', 'plastec4708@argentina.com', '$2y$10$6ssHN5X0hsCZ/PsxRmHHZ.e5j2HHp1YzLfxLQ79f/7D4HQmd..dPi', '30-71884708-2', '+54 3571 888888', 'Reciclado & Granza', 'Almafuerte', 'Córdoba', 'Ruta 36 Km 115', 'empresa', 'activo', '2026-09-22 19:46:58', '2026-09-22 19:47:00', '2026-09-22 19:47:00'),
(4, 'Administrador General MateriaX', 'myadminpro@gmail.com', '$2y$12$Z3IsqEqgyyjuMraeBb/./enYfY3KaHNmyznQNv7LdqoW3aVA/0Nfa', '20-00000000-0', '+54 3571 00-0000', 'Administración Central', 'Río Tercero', 'Córdoba', 'Sede Central MateriaX', 'admin', 'activo', '2026-09-22 20:31:35', '2026-09-22 22:02:31', '2026-09-22 22:02:31'),
(5, 'plasticoss', 'manurisolo2@gmail.com', '$2y$10$VoEZdsFNVtoQcDOr6q52FOhfe0RmDuLR3ns8KkcngfowtFPc0T3MK', '23492887269', '3571435390', 'Soplado de Cuerpos Huecos', 'riotercrereo', 'Córdoba', 'mm', 'empresa', 'activo', '2026-09-22 20:56:09', '2026-09-22 20:56:09', '2026-09-22 20:56:09'),
(6, 'Industrias pro', 'boongraxd@gmail.com', '$2y$10$D186hUo7jNvi4rHde.6JfOFRlIupy95DRLVsjYJVCpQGBuVTJbz3e', '23492887269', '3571345390', 'Compuestos & Masterbatch', 'rio tercero', 'Córdoba', 'aca en la 2 de arbil', 'empresa', 'activo', '2026-09-22 21:54:26', '2026-09-22 21:54:26', '2026-09-22 21:54:26')
ON CONFLICT (email) DO NOTHING;

-- Plantas industriales iniciales
INSERT INTO plantas_industriales (id, empresa_id, nombre_planta, direccion, localidad, provincia, coordenadas_gps, posee_bascula, capacidad_carga_camiones, contacto_responsable, telefono_planta, activa) VALUES
(1, 1, 'Sede Operativa y Fiscal Central', 'Av. San Martín 450', 'Río Tercero', 'Córdoba', '-32.1732,-64.1141', 1, 'Hasta 45 Ton', 'Ing. Carlos Rossi', '+54 3571 42-1501', 1),
(2, 2, 'Centro de Molienda Río III', 'Parque Industrial Química Sur, Lote 4', 'Río Tercero', 'Córdoba', '-32.1800,-64.1200', 1, 'Hasta 30 Ton', 'Téc. Lucas Benítez', '+54 3571 43-0001', 1),
(3, 3, 'Planta Almafuerte Reciclados', 'Ruta 36 Km 115', 'Almafuerte', 'Córdoba', '-32.1950,-64.2500', 1, 'Hasta 45 Ton', 'Mariana Gómez', '+54 3571 88-8889', 1)
ON CONFLICT (id) DO NOTHING;

-- Lotes iniciales
INSERT INTO productos (id, user_id, planta_id, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at) VALUES
(2, 1, 1, 'Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800, 1420, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'),
(3, 1, 1, 'Merma de Bobinas PVC Cristal Flexible', 'PVC', 950, 2100, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.', 'Disponible', '2026-09-16 16:38:41', '2026-09-16 16:38:41'),
(4, 1, 1, 'ABS Granulado Color Gris Reciclado', 'ABS', 750, 2300.5, 'Córdoba Capital, Parque Industrial Ferreyra', 'Granulado ABS de descarte limpio de electrodomésticos. Fluidez media, apto inyección.', 'Disponible', '2026-09-16 19:44:22', '2026-09-16 19:44:22'),
(5, 1, 1, 'PET Cristal Molido Botella', 'PET', 3200, 980, 'Río Tercero, Córdoba', 'Flakes de botellas PET transparentes, lavado en caliente con sosa cáustica.', 'Disponible', '2026-09-16 19:45:26', '2026-09-16 19:45:26')
ON CONFLICT (id) DO NOTHING;

-- Sincronizar secuencias de IDs auto-incrementales
SELECT setval(pg_get_serial_sequence('usuarios', 'id'), COALESCE((SELECT MAX(id) FROM usuarios), 1), true);
SELECT setval(pg_get_serial_sequence('plantas_industriales', 'id'), COALESCE((SELECT MAX(id) FROM plantas_industriales), 1), true);
SELECT setval(pg_get_serial_sequence('productos', 'id'), COALESCE((SELECT MAX(id) FROM productos), 1), true);
SELECT setval(pg_get_serial_sequence('transacciones', 'id'), COALESCE((SELECT MAX(id) FROM transacciones), 1), true);
SELECT setval(pg_get_serial_sequence('certificados_ambientales', 'id'), COALESCE((SELECT MAX(id) FROM certificados_ambientales), 1), true);
SELECT setval(pg_get_serial_sequence('mensajes_negociacion', 'id'), COALESCE((SELECT MAX(id) FROM mensajes_negociacion), 1), true);
