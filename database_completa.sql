-- =======================================================================
-- MATERIAX PRO — BASE DE DATOS COMPLETA, REAL Y FUNCIONAL
-- Red Industrial de Reutilización Circular y Simbiosis de Polímeros
-- Instituto Técnico Río Tercero · Curso 6° B
-- Motor: MariaDB 10.4+ / MySQL 8.0+ InnoDB
-- =======================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-03:00";

-- -----------------------------------------------------------------------
-- 1. TABLA: usuarios (Empresas y Operadores de la Red)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Razón social o denominación legal',
  `email` VARCHAR(150) NOT NULL COMMENT 'Correo corporativo único',
  `password` VARCHAR(255) NOT NULL COMMENT 'Hash seguro bcrypt',
  `cuit` VARCHAR(20) DEFAULT NULL COMMENT 'Clave Única de Identificación Tributaria AFIP',
  `telefono` VARCHAR(30) DEFAULT NULL COMMENT 'Teléfono de contacto de planta',
  `rubro` VARCHAR(100) DEFAULT NULL COMMENT 'Sector: Inyección, Extrusión, Reciclado, etc.',
  `direccion` VARCHAR(150) DEFAULT NULL COMMENT 'Domicilio legal para facturación',
  `ciudad` VARCHAR(100) DEFAULT NULL COMMENT 'Ciudad o municipio de radicación',
  `provincia` VARCHAR(100) DEFAULT 'Córdoba' COMMENT 'Provincia de la sede legal',
  `rol` VARCHAR(50) NOT NULL DEFAULT 'empresa' COMMENT 'Perfil: empresa, admin, operador',
  `estado` ENUM('pendiente','activo','inactivo','rechazado') NOT NULL DEFAULT 'pendiente',
  `verificado_afip` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Homologado por AFIP/Admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ultimo_login` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  KEY `idx_usuarios_rol` (`rol`),
  KEY `idx_usuarios_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- 2. TABLA: plantas_industriales (Sedes Fabriles y Centros de Acopio)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plantas_industriales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` INT(11) NOT NULL,
  `nombre_planta` VARCHAR(100) NOT NULL COMMENT 'Ej: Planta Parque Industrial Río Tercero',
  `direccion` VARCHAR(150) NOT NULL COMMENT 'Calle, ruta o predio industrial',
  `localidad` VARCHAR(100) NOT NULL,
  `provincia` VARCHAR(100) NOT NULL DEFAULT 'Córdoba',
  `coordenadas_gps` VARCHAR(50) DEFAULT NULL COMMENT 'Latitud,Longitud para cálculo logístico',
  `posee_bascula` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Dispone de balanza de camiones homologada',
  `capacidad_carga_camiones` VARCHAR(50) DEFAULT 'Hasta 45 Ton' COMMENT 'Tipo de vehículo admitido',
  `contacto_responsable` VARCHAR(100) DEFAULT NULL,
  `telefono_planta` VARCHAR(30) DEFAULT NULL,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_plantas_empresa` (`empresa_id`),
  KEY `idx_plantas_prov_loc` (`provincia`, `localidad`),
  CONSTRAINT `fk_plantas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- 3. TABLA: categorias_polimero (Catálogo Técnico SPI / ASTM D7611)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias_polimero` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_spi` TINYINT(3) UNSIGNED NOT NULL COMMENT '1 al 7: Identificación de resina',
  `sigla` VARCHAR(10) NOT NULL COMMENT 'PP, PEAD, PEBD, PVC, PET, PS, ABS, PA',
  `nombre_tecnico` VARCHAR(100) NOT NULL COMMENT 'Nombre químico formal',
  `densidad_g_cm3` DECIMAL(4,3) NOT NULL COMMENT 'Densidad específica de referencia g/cm3',
  `temp_fusion_c` SMALLINT(6) DEFAULT NULL COMMENT 'Temperatura de fusión en °C',
  `factor_co2_kg` DECIMAL(5,2) NOT NULL DEFAULT 1.85 COMMENT 'kg CO2eq evitados por cada kg reciclado vs virgen',
  `reciclabilidad` ENUM('alta','media','baja','especial') NOT NULL DEFAULT 'alta',
  `descripcion_tecnica` TEXT DEFAULT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categorias_sigla` (`sigla`),
  KEY `idx_categorias_spi` (`codigo_spi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- 4. TABLA: productos (Lotes de Polímero Industrial Ofertados)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `productos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL COMMENT 'Empresa titular oferente',
  `planta_id` INT(11) DEFAULT NULL COMMENT 'Planta física de acopio',
  `categoria_id` INT(11) DEFAULT NULL COMMENT 'Familia química normalizada',
  `nombre` VARCHAR(150) NOT NULL COMMENT 'Título de la publicación',
  `tipo_polimero` VARCHAR(50) NOT NULL COMMENT 'Sigla de la resina para compatibilidad rápida',
  `presentacion` ENUM('scrap_molido','pellet_regranulado','purga_torta','film_fardos','descarte_piezas','virgen_fuera_esp') NOT NULL DEFAULT 'scrap_molido',
  `color` VARCHAR(50) NOT NULL DEFAULT 'Negro',
  `fluidez_mfi` DECIMAL(6,2) DEFAULT NULL COMMENT 'Melt Flow Index g/10min',
  `contaminacion_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje estimado de impurezas',
  `cantidad_kg` DECIMAL(10,2) NOT NULL COMMENT 'Peso neto total inicial publicado en kg',
  `cantidad_disp_kg` DECIMAL(10,2) DEFAULT NULL COMMENT 'Stock disponible en tiempo real en kg',
  `pedido_minimo_kg` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
  `precio_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Precio por kg en ARS/USD',
  `moneda` ENUM('ARS','USD') NOT NULL DEFAULT 'ARS',
  `ubicacion` VARCHAR(100) NOT NULL COMMENT 'Ciudad de retiro visible',
  `descripcion` TEXT DEFAULT NULL,
  `acondicionamiento` VARCHAR(100) DEFAULT 'Big Bags de 1.000 kg',
  `estado` ENUM('Disponible','Reservado','Vendido','Pausado','Retirado') NOT NULL DEFAULT 'Disponible',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_productos_user_id` (`user_id`),
  KEY `idx_productos_tipo_polimero` (`tipo_polimero`),
  KEY `idx_productos_estado` (`estado`),
  CONSTRAINT `fk_productos_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asegurar columnas extendidas si la tabla ya existía
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `planta_id` INT(11) DEFAULT NULL AFTER `user_id`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `categoria_id` INT(11) DEFAULT NULL AFTER `planta_id`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `presentacion` ENUM('scrap_molido','pellet_regranulado','purga_torta','film_fardos','descarte_piezas','virgen_fuera_esp') NOT NULL DEFAULT 'scrap_molido' AFTER `tipo_polimero`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `color` VARCHAR(50) NOT NULL DEFAULT 'Negro' AFTER `presentacion`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `fluidez_mfi` DECIMAL(6,2) DEFAULT NULL AFTER `color`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `contaminacion_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `fluidez_mfi`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `cantidad_disp_kg` DECIMAL(10,2) DEFAULT NULL AFTER `cantidad_kg`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `pedido_minimo_kg` DECIMAL(10,2) NOT NULL DEFAULT 100.00 AFTER `cantidad_disp_kg`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `moneda` ENUM('ARS','USD') NOT NULL DEFAULT 'ARS' AFTER `precio_unitario`;
ALTER TABLE `productos` ADD COLUMN IF NOT EXISTS `acondicionamiento` VARCHAR(100) DEFAULT 'Big Bags de 1.000 kg' AFTER `descripcion`;
UPDATE `productos` SET `cantidad_disp_kg` = `cantidad_kg` WHERE `cantidad_disp_kg` IS NULL;

-- -----------------------------------------------------------------------
-- 5. TABLA: transacciones (Operaciones y Órdenes de Retiro B2B)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transacciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_operacion` VARCHAR(20) NOT NULL COMMENT 'Ej: TRX-2026-0001',
  `producto_id` INT(11) NOT NULL,
  `comprador_id` INT(11) NOT NULL,
  `vendedor_id` INT(11) NOT NULL,
  `planta_origen_id` INT(11) DEFAULT NULL,
  `cantidad_kg` DECIMAL(10,2) NOT NULL COMMENT 'Volumen de material pactado',
  `precio_unitario_pactado` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `costo_flete` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_total` DECIMAL(12,2) NOT NULL,
  `modalidad_retiro` ENUM('retiro_comprador_planta','entrega_vendedor','flete_convenido') NOT NULL DEFAULT 'retiro_comprador_planta',
  `estado` ENUM('solicitada','en_evaluacion','aprobada','pesaje_balanza','en_transito','completada','rechazada','cancelada') NOT NULL DEFAULT 'solicitada',
  `ticket_balanza_kg` DECIMAL(10,2) DEFAULT NULL COMMENT 'Pesaje verificado en báscula',
  `numero_remito` VARCHAR(50) DEFAULT NULL COMMENT 'Remito oficial de traslado',
  `observaciones` TEXT DEFAULT NULL,
  `fecha_solicitud` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_acuerdo` DATETIME DEFAULT NULL,
  `fecha_despacho` DATETIME DEFAULT NULL,
  `fecha_cierre` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transacciones_codigo` (`codigo_operacion`),
  KEY `idx_transacciones_producto` (`producto_id`),
  KEY `idx_transacciones_comprador` (`comprador_id`),
  KEY `idx_transacciones_vendedor` (`vendedor_id`),
  KEY `idx_transacciones_estado` (`estado`),
  CONSTRAINT `fk_trx_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_trx_comprador` FOREIGN KEY (`comprador_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_trx_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_trx_planta` FOREIGN KEY (`planta_origen_id`) REFERENCES `plantas_industriales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- 6. TABLA: certificados_ambientales (Certificación de Impacto y CO2)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `certificados_ambientales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_certificado` VARCHAR(40) NOT NULL COMMENT 'UUID criptográfico para QR',
  `transaccion_id` INT(11) NOT NULL,
  `generadora_id` INT(11) NOT NULL,
  `revalorizadora_id` INT(11) NOT NULL,
  `kg_recuperados` DECIMAL(10,2) NOT NULL,
  `co2_evitado_kg` DECIMAL(10,2) NOT NULL COMMENT 'Calculado según factor de la resina',
  `mwh_ahorrado` DECIMAL(8,2) NOT NULL,
  `norma_referencia` VARCHAR(100) NOT NULL DEFAULT 'ISO 14044 / GHG Protocol Scope 3',
  `fecha_emision` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `firmado_digitalmente` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_certificados_codigo` (`codigo_certificado`),
  UNIQUE KEY `uk_certificados_trx` (`transaccion_id`),
  KEY `idx_certificados_generadora` (`generadora_id`),
  KEY `idx_certificados_revalorizadora` (`revalorizadora_id`),
  CONSTRAINT `fk_cert_trx` FOREIGN KEY (`transaccion_id`) REFERENCES `transacciones` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_cert_generadora` FOREIGN KEY (`generadora_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_cert_revalorizadora` FOREIGN KEY (`revalorizadora_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- 7. TABLA: mensajes_negociacion (Comunicación y Consultas Técnicas)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mensajes_negociacion` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `producto_id` INT(11) NOT NULL,
  `transaccion_id` INT(11) DEFAULT NULL,
  `emisor_id` INT(11) NOT NULL,
  `receptor_id` INT(11) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `leido` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_lectura` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mensajes_producto` (`producto_id`),
  KEY `idx_mensajes_transaccion` (`transaccion_id`),
  KEY `idx_mensajes_emisor` (`emisor_id`),
  KEY `idx_mensajes_receptor` (`receptor_id`),
  CONSTRAINT `fk_msg_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_transaccion` FOREIGN KEY (`transaccion_id`) REFERENCES `transacciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_emisor` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_receptor` FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- 8. TABLA: auditoria_estados_lote (Trazabilidad y Log de Eventos)
-- -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auditoria_estados_lote` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `producto_id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `estado_anterior` VARCHAR(30) NOT NULL,
  `estado_nuevo` VARCHAR(30) NOT NULL,
  `motivo` VARCHAR(255) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_auditoria_prod` (`producto_id`),
  KEY `idx_auditoria_user` (`usuario_id`),
  CONSTRAINT `fk_audit_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------
-- VISTAS DE NEGOCIO (Explotación Analítica)
-- -----------------------------------------------------------------------
CREATE OR REPLACE VIEW `v_catalogo_activo` AS
SELECT 
    p.id AS producto_id,
    p.nombre AS titulo_lote,
    p.tipo_polimero,
    c.codigo_spi,
    COALESCE(c.nombre_tecnico, p.tipo_polimero) AS familia_quimica,
    p.presentacion,
    p.color,
    p.fluidez_mfi,
    p.contaminacion_pct,
    COALESCE(p.cantidad_disp_kg, p.cantidad_kg) AS kilos_disponibles,
    p.precio_unitario,
    ROUND(COALESCE(p.cantidad_disp_kg, p.cantidad_kg) * p.precio_unitario, 2) AS valor_lote_estimado,
    u.id AS vendedor_id,
    u.nombre AS empresa_vendedora,
    COALESCE(pl.nombre_planta, 'Sede Central') AS planta_retiro,
    COALESCE(pl.localidad, u.ciudad, p.ubicacion) AS localidad_retiro,
    COALESCE(pl.provincia, u.provincia, 'Córdoba') AS provincia_retiro,
    COALESCE(pl.posee_bascula, 1) AS posee_bascula,
    p.created_at AS fecha_publicacion
FROM `productos` p
INNER JOIN `usuarios` u ON p.user_id = u.id
LEFT JOIN `categorias_polimero` c ON p.categoria_id = c.id
LEFT JOIN `plantas_industriales` pl ON p.planta_id = pl.id
WHERE p.estado = 'Disponible' 
  AND COALESCE(p.cantidad_disp_kg, p.cantidad_kg) > 0
  AND u.estado = 'activo';

CREATE OR REPLACE VIEW `v_balance_ambiental_co2` AS
SELECT 
    u.id AS empresa_id,
    u.nombre AS empresa,
    COALESCE(c.sigla, p.tipo_polimero) AS tipo_resina,
    COUNT(cert.id) AS certificados_emitidos,
    SUM(cert.kg_recuperados) AS total_kg_reincorporados,
    SUM(cert.co2_evitado_kg) AS total_kg_co2_evitados,
    SUM(cert.mwh_ahorrado) AS total_mwh_ahorrados
FROM `certificados_ambientales` cert
INNER JOIN `usuarios` u ON cert.generadora_id = u.id
INNER JOIN `transacciones` t ON cert.transaccion_id = t.id
INNER JOIN `productos` p ON t.producto_id = p.id
LEFT JOIN `categorias_polimero` c ON p.categoria_id = c.id
GROUP BY u.id, u.nombre, c.sigla;

-- -----------------------------------------------------------------------
-- DATOS SEMILLA (Seed Data)
-- -----------------------------------------------------------------------

-- 1. Catálogo de Polímeros (Norma SPI)
INSERT INTO `categorias_polimero` (`id`, `codigo_spi`, `sigla`, `nombre_tecnico`, `densidad_g_cm3`, `temp_fusion_c`, `factor_co2_kg`, `reciclabilidad`, `descripcion_tecnica`, `activo`) VALUES
(1, 1, 'PET', 'Polietileno Tereftalato', 1.380, 260, 2.15, 'alta', 'Envases cristal, botellas y film soplado.', 1),
(2, 2, 'PEAD', 'Polietileno de Alta Densidad (HDPE)', 0.955, 130, 1.90, 'alta', 'Bidones químicos, tuberías, cajones y tapas.', 1),
(3, 3, 'PVC', 'Policloruro de Vinilo', 1.400, 180, 1.70, 'media', 'Cañerías, perfiles para aberturas y aislamiento de cables.', 1),
(4, 4, 'PEBD', 'Polietileno de Baja Densidad (LDPE)', 0.920, 110, 1.80, 'alta', 'Film termocontraíble, bolsas y soplado.', 1),
(5, 5, 'PP', 'Polipropileno Homopolímero / Copolímero', 0.905, 165, 1.85, 'alta', 'Inyección automotriz, envases de pared delgada, big bags y fibras.', 1),
(6, 6, 'PS', 'Poliestireno Alto Impacto (HIPS / EPS)', 1.050, 240, 1.75, 'media', 'Termoformado de bandejas, aislación térmica y bazar.', 1),
(7, 7, 'ABS', 'Acrilonitrilo Butadieno Estireno', 1.060, 220, 2.50, 'especial', 'Polímero de ingeniería para carcasas electrodomésticas y automoción.', 1)
ON DUPLICATE KEY UPDATE `sigla` = VALUES(`sigla`);

-- 2. Sedes y Plantas Industriales
INSERT INTO `plantas_industriales` (`id`, `empresa_id`, `nombre_planta`, `direccion`, `localidad`, `provincia`, `coordenadas_gps`, `posee_bascula`) VALUES
(1, 1, 'Sede Operativa y Fiscal Central', 'Av. San Martín 450', 'Río Tercero', 'Córdoba', '-32.1732,-64.1141', 1)
ON DUPLICATE KEY UPDATE `nombre_planta` = VALUES(`nombre_planta`);

SET FOREIGN_KEY_CHECKS = 1;
