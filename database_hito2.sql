-- ==============================================================================
-- INSTITUTO TÉCNICO RÍO TERCERO - 6° B
-- PROYECTO PÁGINA WEB: MATERIAX PRO (Hito 2 - Ampliación Funcional)
-- SCRIPT DE ACTUALIZACIÓN DE BASE DE DATOS: MySQL / MariaDB (XAMPP)
-- ==============================================================================

USE `materiax_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. TABLA: plantas_industriales (Puntos de Acopio, Retiro y Báscula)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plantas_industriales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` INT(11) NOT NULL COMMENT 'FK a usuarios.id (empresa propietaria de la planta)',
  `nombre_planta` VARCHAR(100) NOT NULL COMMENT 'Ej: Sede Central / Depósito Parque Industrial',
  `direccion` VARCHAR(150) NOT NULL COMMENT 'Dirección de la planta o centro de retiro',
  `localidad` VARCHAR(100) NOT NULL COMMENT 'Ciudad o municipio',
  `provincia` VARCHAR(100) NOT NULL DEFAULT 'Córdoba',
  `coordenadas_gps` VARCHAR(50) DEFAULT NULL,
  `posee_bascula` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Dispone de balanza fiscal de camiones',
  `capacidad_carga_camiones` VARCHAR(50) DEFAULT 'Hasta 45 Ton' COMMENT 'Capacidad admitida de transporte',
  `contacto_responsable` VARCHAR(100) DEFAULT NULL COMMENT 'Nombre del jefe de planta o logística',
  `telefono_planta` VARCHAR(30) DEFAULT NULL COMMENT 'Teléfono directo de despacho',
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_plantas_empresa` (`empresa_id`),
  KEY `idx_plantas_prov_loc` (`provincia`, `localidad`),
  CONSTRAINT `fk_plantas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. VINCULAR productos CON plantas_industriales
-- ------------------------------------------------------------------------------
-- Añadir columna planta_id si aún no existe
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'productos' 
    AND COLUMN_NAME = 'planta_id'
);

SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `productos` ADD COLUMN `planta_id` INT(11) NULL DEFAULT NULL AFTER `user_id`, ADD CONSTRAINT `fk_productos_planta` FOREIGN KEY (`planta_id`) REFERENCES `plantas_industriales` (`id`) ON DELETE SET NULL', 
  'SELECT "Column planta_id already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------------------
-- 3. TABLA: transacciones (Solicitudes de Compra y Acuerdos Comerciales)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transacciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_operacion` VARCHAR(20) NOT NULL COMMENT 'Ej: TRX-2026-0001',
  `producto_id` INT(11) NOT NULL,
  `comprador_id` INT(11) NOT NULL,
  `vendedor_id` INT(11) NOT NULL,
  `planta_origen_id` INT(11) DEFAULT NULL,
  `cantidad_kg` DECIMAL(10,2) NOT NULL COMMENT 'Kilos solicitados',
  `precio_unitario_pactado` DECIMAL(10,2) NOT NULL COMMENT 'Precio por kg acordado',
  `subtotal` DECIMAL(12,2) NOT NULL,
  `costo_flete` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_total` DECIMAL(12,2) NOT NULL,
  `modalidad_retiro` ENUM('retiro_comprador_planta','entrega_vendedor','flete_convenido') NOT NULL DEFAULT 'retiro_comprador_planta',
  `estado` ENUM('solicitada','en_evaluacion','aprobada','pesaje_balanza','en_transito','completada','rechazada','cancelada') NOT NULL DEFAULT 'solicitada',
  `ticket_balanza_kg` DECIMAL(10,2) DEFAULT NULL,
  `numero_remito` VARCHAR(50) DEFAULT NULL,
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

-- ------------------------------------------------------------------------------
-- 4. TABLA: certificados_ambientales (Huella Ecológica y Ahorro de CO2)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `certificados_ambientales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_certificado` VARCHAR(40) NOT NULL COMMENT 'UUID / Código verificable',
  `transaccion_id` INT(11) NOT NULL,
  `generadora_id` INT(11) NOT NULL COMMENT 'Empresa que aportó el excedente (vendedor)',
  `revalorizadora_id` INT(11) NOT NULL COMMENT 'Empresa que reutiliza/recicla (comprador)',
  `kg_recuperados` DECIMAL(10,2) NOT NULL,
  `co2_evitado_kg` DECIMAL(10,2) NOT NULL COMMENT 'Kg de CO2 eq evitados',
  `mwh_ahorrado` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `norma_referencia` VARCHAR(100) NOT NULL DEFAULT 'Economía Circular / Protocolo GHG Alcance 3',
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

SET FOREIGN_KEY_CHECKS = 1;
