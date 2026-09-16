-- ==============================================================================
-- INSTITUTO TÉCNICO RÍO TERCERO - 6° B
-- PROYECTO PÁGINA WEB: MATERIAX (Hito 1 - Primeros Pasos)
-- SCRIPT DE BASE DE DATOS: MySQL / MariaDB (XAMPP)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `materiax_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `materiax_db`;

-- ------------------------------------------------------------------------------
-- 1. TABLA: usuarios
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre completo o razón social de la empresa',
  `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'Correo electrónico para login',
  `password` VARCHAR(255) NOT NULL COMMENT 'Contraseña cifrada con bcrypt (password_hash)',
  `cuit` VARCHAR(20) NULL COMMENT 'CUIT de la empresa',
  `telefono` VARCHAR(30) NULL COMMENT 'Teléfono de contacto institucional',
  `rol` VARCHAR(50) NOT NULL DEFAULT 'empresa' COMMENT 'Rol del usuario (empresa, admin, etc.)',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TABLA: productos (Entidad Secundaria - Excedentes de Polímeros Industriales)
-- ------------------------------------------------------------------------------
CREATE TABLE `productos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL COMMENT 'Clave foránea que referencia al usuario que publicó el lote',
  `nombre` VARCHAR(150) NOT NULL COMMENT 'Denominación del material o lote',
  `tipo_polimero` VARCHAR(50) NOT NULL COMMENT 'Tipo de polímero: PE, PP, PVC, ABS, Nylon (PA), PET, etc.',
  `cantidad_kg` DECIMAL(10,2) NOT NULL COMMENT 'Cantidad disponible en kilogramos',
  `precio_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Precio por kilogramo en moneda local',
  `ubicacion` VARCHAR(100) NOT NULL COMMENT 'Localidad / Planta industrial de origen',
  `descripcion` TEXT NULL COMMENT 'Ficha técnica, color, grado de pureza o proceso de origen',
  `estado` ENUM('Disponible', 'Reservado', 'Vendido') NOT NULL DEFAULT 'Disponible' COMMENT 'Estado comercial del lote',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_productos_usuarios` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. DATOS DE PRUEBA (SEEDING INICIAL)
-- ------------------------------------------------------------------------------

-- Usuario Demo (Contraseña: admin123)
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `cuit`, `telefono`, `rol`, `created_at`, `updated_at`) 
VALUES (
  1, 
  'Petroquímica Río Tercero S.A.', 
  'admin@materiax.com', 
  '$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6', 
  '30-50284912-8', 
  '+54 3571 42-1500', 
  'empresa', 
  NOW(), 
  NOW()
);

-- Productos Demo asociados al usuario 1
INSERT INTO `productos` (`id`, `user_id`, `nombre`, `tipo_polimero`, `cantidad_kg`, `precio_unitario`, `ubicacion`, `descripcion`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pellet Polietileno Alta Densidad (HDPE)', 'Polietileno (PE)', 2500.00, 1850.50, 'Río Tercero, Córdoba', 'Pellet virgen recuperado de purga de soplado. Color natural, índice de fluidez 0.35 g/10min. Envasado en big bags de 1000 kg con control de humedad.', 'Disponible', NOW(), NOW()),
(2, 1, 'Scrap Molido de Polipropileno Homopolímero (PP)', 'Polipropileno (PP)', 1800.00, 1420.00, 'Río Tercero, Córdoba', 'Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyección automotriz. Malla 8mm.', 'Disponible', NOW(), NOW()),
(3, 1, 'Merma de Bobinas PVC Cristal Flexible', 'PVC', 950.00, 2100.00, 'Almafuerte, Córdoba', 'Recortes laterales de calandrado de película transparente flexible. Excelente elasticidad y transparencia.', 'Disponible', NOW(), NOW());
