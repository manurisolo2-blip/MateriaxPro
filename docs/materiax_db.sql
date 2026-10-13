-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: materiax_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auditoria_estados_lote`
--

DROP TABLE IF EXISTS `auditoria_estados_lote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria_estados_lote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado_anterior` varchar(30) NOT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_auditoria_prod` (`producto_id`),
  KEY `idx_auditoria_user` (`usuario_id`),
  CONSTRAINT `fk_audit_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_estados_lote`
--

LOCK TABLES `auditoria_estados_lote` WRITE;
/*!40000 ALTER TABLE `auditoria_estados_lote` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria_estados_lote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_polimero`
--

DROP TABLE IF EXISTS `categorias_polimero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias_polimero` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_spi` tinyint(3) unsigned NOT NULL COMMENT '1 al 7: Identificaci├│n de resina',
  `sigla` varchar(10) NOT NULL COMMENT 'PP, PEAD, PEBD, PVC, PET, PS, ABS, PA',
  `nombre_tecnico` varchar(100) NOT NULL COMMENT 'Nombre qu├¡mico formal',
  `densidad_g_cm3` decimal(4,3) NOT NULL COMMENT 'Densidad espec├¡fica de referencia g/cm3',
  `temp_fusion_c` smallint(6) DEFAULT NULL COMMENT 'Temperatura de fusi├│n en ┬░C',
  `factor_co2_kg` decimal(5,2) NOT NULL DEFAULT 1.85 COMMENT 'kg CO2eq evitados por cada kg reciclado vs virgen',
  `reciclabilidad` enum('alta','media','baja','especial') NOT NULL DEFAULT 'alta',
  `descripcion_tecnica` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categorias_sigla` (`sigla`),
  KEY `idx_categorias_spi` (`codigo_spi`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_polimero`
--

LOCK TABLES `categorias_polimero` WRITE;
/*!40000 ALTER TABLE `categorias_polimero` DISABLE KEYS */;
INSERT INTO `categorias_polimero` VALUES (1,1,'PET','Polietileno Tereftalato',1.380,260,2.15,'alta','Envases cristal, botellas y film soplado.',1),(2,2,'PEAD','Polietileno de Alta Densidad (HDPE)',0.955,130,1.90,'alta','Bidones qu├¡micos, tuber├¡as, cajones y tapas.',1),(3,3,'PVC','Policloruro de Vinilo',1.400,180,1.70,'media','Ca├▒er├¡as, perfiles para aberturas y aislamiento de cables.',1),(4,4,'PEBD','Polietileno de Baja Densidad (LDPE)',0.920,110,1.80,'alta','Film termocontra├¡ble, bolsas y soplado.',1),(5,5,'PP','Polipropileno Homopol├¡mero / Copol├¡mero',0.905,165,1.85,'alta','Inyecci├│n automotriz, envases de pared delgada, big bags y fibras.',1),(6,6,'PS','Poliestireno Alto Impacto (HIPS / EPS)',1.050,240,1.75,'media','Termoformado de bandejas, aislaci├│n t├®rmica y bazar.',1),(7,7,'ABS','Acrilonitrilo Butadieno Estireno',1.060,220,2.50,'especial','Pol├¡mero de ingenier├¡a para carcasas electrodom├®sticas y automoci├│n.',1);
/*!40000 ALTER TABLE `categorias_polimero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificados_ambientales`
--

DROP TABLE IF EXISTS `certificados_ambientales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificados_ambientales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_certificado` varchar(40) NOT NULL COMMENT 'UUID criptogr├ífico para QR',
  `transaccion_id` int(11) NOT NULL,
  `generadora_id` int(11) NOT NULL,
  `revalorizadora_id` int(11) NOT NULL,
  `kg_recuperados` decimal(10,2) NOT NULL,
  `co2_evitado_kg` decimal(10,2) NOT NULL COMMENT 'Calculado seg├║n factor de la resina',
  `mwh_ahorrado` decimal(8,2) NOT NULL,
  `norma_referencia` varchar(100) NOT NULL DEFAULT 'ISO 14044 / GHG Protocol Scope 3',
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `firmado_digitalmente` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_certificados_codigo` (`codigo_certificado`),
  UNIQUE KEY `uk_certificados_trx` (`transaccion_id`),
  KEY `idx_certificados_generadora` (`generadora_id`),
  KEY `idx_certificados_revalorizadora` (`revalorizadora_id`),
  CONSTRAINT `fk_cert_generadora` FOREIGN KEY (`generadora_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_cert_revalorizadora` FOREIGN KEY (`revalorizadora_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_cert_trx` FOREIGN KEY (`transaccion_id`) REFERENCES `transacciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificados_ambientales`
--

LOCK TABLES `certificados_ambientales` WRITE;
/*!40000 ALTER TABLE `certificados_ambientales` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificados_ambientales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes_negociacion`
--

DROP TABLE IF EXISTS `mensajes_negociacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajes_negociacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `transaccion_id` int(11) DEFAULT NULL,
  `emisor_id` int(11) NOT NULL,
  `receptor_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_lectura` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mensajes_producto` (`producto_id`),
  KEY `idx_mensajes_transaccion` (`transaccion_id`),
  KEY `idx_mensajes_emisor` (`emisor_id`),
  KEY `idx_mensajes_receptor` (`receptor_id`),
  CONSTRAINT `fk_msg_emisor` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_receptor` FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_transaccion` FOREIGN KEY (`transaccion_id`) REFERENCES `transacciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes_negociacion`
--

LOCK TABLES `mensajes_negociacion` WRITE;
/*!40000 ALTER TABLE `mensajes_negociacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensajes_negociacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-09-16-000001','App\\Database\\Migrations\\CreateUsuariosTable','default','App',1791923055,1),(2,'2026-09-16-000002','App\\Database\\Migrations\\CreateProductosTable','default','App',1791923055,1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plantas_industriales`
--

DROP TABLE IF EXISTS `plantas_industriales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plantas_industriales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `nombre_planta` varchar(100) NOT NULL COMMENT 'Ej: Planta Parque Industrial R├¡o Tercero',
  `direccion` varchar(150) NOT NULL COMMENT 'Calle, ruta o predio industrial',
  `localidad` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL DEFAULT 'C├│rdoba',
  `coordenadas_gps` varchar(50) DEFAULT NULL COMMENT 'Latitud,Longitud para c├ílculo log├¡stico',
  `posee_bascula` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Dispone de balanza de camiones homologada',
  `capacidad_carga_camiones` varchar(50) DEFAULT 'Hasta 45 Ton' COMMENT 'Tipo de veh├¡culo admitido',
  `contacto_responsable` varchar(100) DEFAULT NULL,
  `telefono_planta` varchar(30) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_plantas_empresa` (`empresa_id`),
  KEY `idx_plantas_prov_loc` (`provincia`,`localidad`),
  CONSTRAINT `fk_plantas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plantas_industriales`
--

LOCK TABLES `plantas_industriales` WRITE;
/*!40000 ALTER TABLE `plantas_industriales` DISABLE KEYS */;
INSERT INTO `plantas_industriales` VALUES (1,1,'Sede Operativa y Fiscal Central','Av. San Mart├¡n 450','R├¡o Tercero','C├│rdoba','-32.1732,-64.1141',1,'Hasta 45 Ton',NULL,NULL,1,'2026-10-09 14:20:16','2026-10-09 14:20:16');
/*!40000 ALTER TABLE `plantas_industriales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Clave for??nea que referencia al usuario que public?? el lote',
  `planta_id` int(11) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL COMMENT 'Denominaci??n del material o lote',
  `tipo_polimero` varchar(50) NOT NULL COMMENT 'Tipo de pol??mero: PE, PP, PVC, ABS, Nylon (PA), PET, etc.',
  `cantidad_kg` decimal(10,2) NOT NULL COMMENT 'Cantidad disponible en kilogramos',
  `cantidad_disp_kg` decimal(10,2) DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL COMMENT 'Precio por kilogramo en moneda local',
  `ubicacion` varchar(100) NOT NULL COMMENT 'Localidad / Planta industrial de origen',
  `descripcion` text DEFAULT NULL COMMENT 'Ficha t??cnica, color, grado de pureza o proceso de origen',
  `estado` enum('Disponible','Reservado','Vendido') NOT NULL DEFAULT 'Disponible' COMMENT 'Estado comercial del lote',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `presentacion` enum('scrap_molido','pellet_regranulado','purga_torta','film_fardos','descarte_piezas','virgen_fuera_esp') NOT NULL DEFAULT 'scrap_molido',
  `color` varchar(50) NOT NULL DEFAULT 'Negro',
  `fluidez_mfi` decimal(6,2) DEFAULT NULL,
  `contaminacion_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `pedido_minimo_kg` decimal(10,2) NOT NULL DEFAULT 100.00,
  `moneda` enum('ARS','USD') NOT NULL DEFAULT 'ARS',
  `acondicionamiento` varchar(100) DEFAULT 'Big Bags de 1.000 kg',
  PRIMARY KEY (`id`),
  KEY `fk_productos_usuarios` (`user_id`),
  KEY `idx_productos_tipo_polimero` (`tipo_polimero`),
  KEY `idx_productos_estado` (`estado`),
  CONSTRAINT `fk_productos_usuarios` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (2,1,NULL,NULL,'Scrap Molido de Polipropileno Homopol??mero (PP)','Polipropileno (PP)',1800.00,1800.00,1420.00,'R??o Tercero, C??rdoba','Molienda limpia libre de polvo y metales. Proveniente de piezas defectuosas de inyecci??n automotriz. Malla 8mm.','Disponible','2026-09-16 16:38:41','2026-10-09 14:20:16','scrap_molido','Negro',NULL,0.00,100.00,'ARS','Big Bags de 1.000 kg'),(3,1,NULL,NULL,'Merma de Bobinas PVC Cristal Flexible','PVC',950.00,950.00,2100.00,'Almafuerte, C??rdoba','Recortes laterales de calandrado de pel??cula transparente flexible. Excelente elasticidad y transparencia.','Disponible','2026-09-16 16:38:41','2026-10-09 14:20:16','scrap_molido','Negro',NULL,0.00,100.00,'ARS','Big Bags de 1.000 kg'),(4,1,NULL,NULL,'ABS Granulado Color Gris Reciclado','ABS',750.00,750.00,2300.50,'Córdoba Capital, Parque Industrial Ferreyra','Granulado ABS de descarte limpio de electrodomésticos. Fluidez media, apto inyección.','Disponible','2026-09-16 19:44:22','2026-10-09 14:20:16','scrap_molido','Negro',NULL,0.00,100.00,'ARS','Big Bags de 1.000 kg'),(5,1,NULL,NULL,'PET Cristal Molido Botella','PET',3200.00,3200.00,980.00,'Río Tercero, Córdoba','Flakes de botellas PET transparentes, lavado en caliente con sosa cáustica.','Disponible','2026-09-16 19:45:26','2026-10-09 14:20:16','scrap_molido','Negro',NULL,0.00,100.00,'ARS','Big Bags de 1.000 kg'),(6,1,NULL,NULL,'Lote Ajeno Test 1790120796','Polietileno (PE)',500.00,500.00,1200.00,'Río Tercero',NULL,'Disponible','2026-09-22 23:46:36','2026-10-09 14:20:16','scrap_molido','Negro',NULL,0.00,100.00,'ARS','Big Bags de 1.000 kg'),(7,1,NULL,NULL,'Lote Ajeno No Borrable 1790120796','Polipropileno (PP)',1000.00,1000.00,1500.00,'Córdoba',NULL,'Disponible','2026-09-22 23:46:36','2026-10-09 14:20:16','scrap_molido','Negro',NULL,0.00,100.00,'ARS','Big Bags de 1.000 kg');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transacciones`
--

DROP TABLE IF EXISTS `transacciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transacciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_operacion` varchar(20) NOT NULL COMMENT 'Ej: TRX-2026-0001',
  `producto_id` int(11) NOT NULL,
  `comprador_id` int(11) NOT NULL,
  `vendedor_id` int(11) NOT NULL,
  `planta_origen_id` int(11) DEFAULT NULL,
  `cantidad_kg` decimal(10,2) NOT NULL COMMENT 'Volumen de material pactado',
  `precio_unitario_pactado` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `costo_flete` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_total` decimal(12,2) NOT NULL,
  `modalidad_retiro` enum('retiro_comprador_planta','entrega_vendedor','flete_convenido') NOT NULL DEFAULT 'retiro_comprador_planta',
  `estado` enum('solicitada','en_evaluacion','aprobada','pesaje_balanza','en_transito','completada','rechazada','cancelada') NOT NULL DEFAULT 'solicitada',
  `ticket_balanza_kg` decimal(10,2) DEFAULT NULL COMMENT 'Pesaje verificado en b├íscula',
  `numero_remito` varchar(50) DEFAULT NULL COMMENT 'Remito oficial de traslado',
  `observaciones` text DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_acuerdo` datetime DEFAULT NULL,
  `fecha_despacho` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transacciones_codigo` (`codigo_operacion`),
  KEY `idx_transacciones_producto` (`producto_id`),
  KEY `idx_transacciones_comprador` (`comprador_id`),
  KEY `idx_transacciones_vendedor` (`vendedor_id`),
  KEY `idx_transacciones_estado` (`estado`),
  KEY `fk_trx_planta` (`planta_origen_id`),
  CONSTRAINT `fk_trx_comprador` FOREIGN KEY (`comprador_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_trx_planta` FOREIGN KEY (`planta_origen_id`) REFERENCES `plantas_industriales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trx_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `fk_trx_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transacciones`
--

LOCK TABLES `transacciones` WRITE;
/*!40000 ALTER TABLE `transacciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `transacciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre completo o raz??n social de la empresa',
  `email` varchar(150) NOT NULL COMMENT 'Correo electr??nico para login',
  `password` varchar(255) NOT NULL COMMENT 'Contrase??a cifrada con bcrypt (password_hash)',
  `cuit` varchar(20) DEFAULT NULL COMMENT 'CUIT de la empresa',
  `telefono` varchar(30) DEFAULT NULL COMMENT 'Tel??fono de contacto institucional',
  `rubro` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'empresa' COMMENT 'Rol del usuario (empresa, admin, etc.)',
  `estado` enum('pendiente','activo','inactivo','rechazado') NOT NULL DEFAULT 'pendiente',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_usuarios_estado` (`estado`),
  KEY `idx_usuarios_rol` (`rol`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Petroqu??mica R??o Tercero S.A.','admin@materiax.com','$2y$12$GSgxOxpOEIMf6piuA5Ss9uVNGSoyVARb7TgY7xr/RKiXf8lU9mUa6','30-50284912-8','+54 3571 42-1500','PetroquÝmica & PolÝmeros','RÝo Tercero','C¾rdoba','Parque Industrial QuÝmica Sur, Lote 12','empresa','activo','2026-09-16 16:38:41','2026-09-22 16:41:26',NULL),(2,'Industrias Plásticas Río III','rio3@plastico.com','$2y$10$tN23Ye.DfVln6.k/m7mGnOiVn141EczoDwIre6tndR62c0wZV/1YC','30-71123456-9','+54 3571 430000',NULL,NULL,NULL,NULL,'empresa','activo','2026-09-16 19:45:44','2026-09-16 19:45:44',NULL),(3,'Plásticos del Valle Renovado S.A.','plastec4708@argentina.com','$2y$10$6ssHN5X0hsCZ/PsxRmHHZ.e5j2HHp1YzLfxLQ79f/7D4HQmd..dPi','30-71884708-2','+54 3571 888888','Reciclado & Granza','Almafuerte','Córdoba','Ruta 36 Km 115','empresa','activo','2026-09-22 19:46:58','2026-09-22 19:47:00','2026-09-22 19:47:00'),(4,'Administrador General MateriaX','myadminpro@gmail.com','$2y$12$Z3IsqEqgyyjuMraeBb/./enYfY3KaHNmyznQNv7LdqoW3aVA/0Nfa','20-00000000-0','+54 3571 00-0000','Administración Central','Río Tercero','Córdoba','Sede Central MateriaX','admin','activo','2026-09-22 20:31:35','2026-09-22 22:02:31','2026-09-22 22:02:31'),(5,'plasticoss','manurisolo2@gmail.com','$2y$10$VoEZdsFNVtoQcDOr6q52FOhfe0RmDuLR3ns8KkcngfowtFPc0T3MK','23492887269','3571435390','Soplado de Cuerpos Huecos','riotercrereo','Córdoba','mm','empresa','activo','2026-09-22 20:56:09','2026-09-22 20:56:09','2026-09-22 20:56:09'),(6,'Industrias pro','boongraxd@gmail.com','$2y$10$D186hUo7jNvi4rHde.6JfOFRlIupy95DRLVsjYJVCpQGBuVTJbz3e','23492887269','3571345390','Compuestos & Masterbatch','rio tercero','Córdoba','aca en la 2 de arbil','empresa','activo','2026-09-22 21:54:26','2026-09-22 21:54:26','2026-09-22 21:54:26'),(20,'Empresa Panel Test S.A.','panel_empresa_1790120795@empresa.com','$2y$12$OWtbV.JPvoNyk3Sr5YbiL.IBeDUcjKmQRYLxyak2Xs2DoDm2VPz4u','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:46:36','2026-09-22 23:46:36',NULL),(24,'Empresa Panel Test S.A.','panel_empresa_1790120862@empresa.com','$2y$12$e7aJCJAiAUnhygKnlyeNTujU6GDnr/A0nucQN5RmhY8VJksLyr8Xu','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:47:42','2026-09-22 23:47:42',NULL),(25,'Empresa Panel Test S.A.','panel_empresa_1790120894@empresa.com','$2y$12$TSVQz9OJIXuedDDG6iQGAO/XAIhHCiiBpP28uZe8SFRLfiP3dQxa.','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:48:14','2026-09-22 23:48:14',NULL),(29,'Empresa Panel Test S.A.','panel_empresa_1790120918@empresa.com','$2y$12$V7GKUcvuKnxC/o2biUAPEuDHJDblSTwnXV1LqBg.GfmNVG630iazu','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:48:38','2026-09-22 23:48:38',NULL),(30,'Empresa Panel Test S.A.','panel_empresa_1790120954@empresa.com','$2y$12$LQvEwcc04ZL5fYvFqYYQpeGIKtJX1RYP5IbreWN51FRlquuYzVVtS','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:49:14','2026-09-22 23:49:14',NULL),(31,'Empresa Panel Test S.A.','panel_empresa_1790120979@empresa.com','$2y$12$kOZvMOaeq.UTP74Q4SnrGucz/8NbAjoqOBqEBDxpJlSKrawkZ5Oge','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:49:39','2026-09-22 23:49:39',NULL),(32,'Empresa Panel Test S.A.','panel_empresa_1790121019@empresa.com','$2y$12$WpGDnFLOuoE17xaG/XprjOk22YwMgZAkNK5Ubi.NkHrW2AN6BypkK','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:50:19','2026-09-22 23:50:19',NULL),(33,'Empresa Panel Test S.A.','panel_empresa_1790121053@empresa.com','$2y$12$Z3X5nhMOChVzOt5ftnY9FeZK6nIuXbWzAhQRQw77izR2fa4dJuTq2','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:50:54','2026-09-22 23:50:54',NULL),(34,'Empresa Panel Test S.A.','panel_empresa_1790121075@empresa.com','$2y$12$NhxrS76ILR2ahZnKAQkhl.Y1jewSdGAtDLdxp4tkSbtjYbjhDYjX6','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:51:15','2026-09-22 23:51:15',NULL),(35,'Empresa Panel Test S.A.','panel_empresa_1790121137@empresa.com','$2y$12$VZJmVXrYlZ.TA14tbW4e8OuBq31wkohM4m5tDRydRYHMikwUeaZS.','30-11223344-5','3571-444444','Extrusión','Río Tercero','Córdoba','Calle Falsa 123','empresa','activo','2026-09-22 23:52:17','2026-09-22 23:52:17',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_balance_ambiental_co2`
--

DROP TABLE IF EXISTS `v_balance_ambiental_co2`;
/*!50001 DROP VIEW IF EXISTS `v_balance_ambiental_co2`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_balance_ambiental_co2` AS SELECT
 1 AS `empresa_id`,
  1 AS `empresa`,
  1 AS `tipo_resina`,
  1 AS `certificados_emitidos`,
  1 AS `total_kg_reincorporados`,
  1 AS `total_kg_co2_evitados`,
  1 AS `total_mwh_ahorrados` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_catalogo_activo`
--

DROP TABLE IF EXISTS `v_catalogo_activo`;
/*!50001 DROP VIEW IF EXISTS `v_catalogo_activo`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_catalogo_activo` AS SELECT
 1 AS `producto_id`,
  1 AS `titulo_lote`,
  1 AS `tipo_polimero`,
  1 AS `codigo_spi`,
  1 AS `familia_quimica`,
  1 AS `presentacion`,
  1 AS `color`,
  1 AS `fluidez_mfi`,
  1 AS `contaminacion_pct`,
  1 AS `kilos_disponibles`,
  1 AS `precio_unitario`,
  1 AS `valor_lote_estimado`,
  1 AS `vendedor_id`,
  1 AS `empresa_vendedora`,
  1 AS `planta_retiro`,
  1 AS `localidad_retiro`,
  1 AS `provincia_retiro`,
  1 AS `posee_bascula`,
  1 AS `fecha_publicacion` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_balance_ambiental_co2`
--

/*!50001 DROP VIEW IF EXISTS `v_balance_ambiental_co2`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_balance_ambiental_co2` AS select `u`.`id` AS `empresa_id`,`u`.`nombre` AS `empresa`,coalesce(`c`.`sigla`,`p`.`tipo_polimero`) AS `tipo_resina`,count(`cert`.`id`) AS `certificados_emitidos`,sum(`cert`.`kg_recuperados`) AS `total_kg_reincorporados`,sum(`cert`.`co2_evitado_kg`) AS `total_kg_co2_evitados`,sum(`cert`.`mwh_ahorrado`) AS `total_mwh_ahorrados` from ((((`certificados_ambientales` `cert` join `usuarios` `u` on(`cert`.`generadora_id` = `u`.`id`)) join `transacciones` `t` on(`cert`.`transaccion_id` = `t`.`id`)) join `productos` `p` on(`t`.`producto_id` = `p`.`id`)) left join `categorias_polimero` `c` on(`p`.`categoria_id` = `c`.`id`)) group by `u`.`id`,`u`.`nombre`,`c`.`sigla` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_catalogo_activo`
--

/*!50001 DROP VIEW IF EXISTS `v_catalogo_activo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_catalogo_activo` AS select `p`.`id` AS `producto_id`,`p`.`nombre` AS `titulo_lote`,`p`.`tipo_polimero` AS `tipo_polimero`,`c`.`codigo_spi` AS `codigo_spi`,coalesce(`c`.`nombre_tecnico`,`p`.`tipo_polimero`) AS `familia_quimica`,`p`.`presentacion` AS `presentacion`,`p`.`color` AS `color`,`p`.`fluidez_mfi` AS `fluidez_mfi`,`p`.`contaminacion_pct` AS `contaminacion_pct`,coalesce(`p`.`cantidad_disp_kg`,`p`.`cantidad_kg`) AS `kilos_disponibles`,`p`.`precio_unitario` AS `precio_unitario`,round(coalesce(`p`.`cantidad_disp_kg`,`p`.`cantidad_kg`) * `p`.`precio_unitario`,2) AS `valor_lote_estimado`,`u`.`id` AS `vendedor_id`,`u`.`nombre` AS `empresa_vendedora`,coalesce(`pl`.`nombre_planta`,'Sede Central') AS `planta_retiro`,coalesce(`pl`.`localidad`,`u`.`ciudad`,`p`.`ubicacion`) AS `localidad_retiro`,coalesce(`pl`.`provincia`,`u`.`provincia`,'Córdoba') AS `provincia_retiro`,coalesce(`pl`.`posee_bascula`,1) AS `posee_bascula`,`p`.`created_at` AS `fecha_publicacion` from (((`productos` `p` join `usuarios` `u` on(`p`.`user_id` = `u`.`id`)) left join `categorias_polimero` `c` on(`p`.`categoria_id` = `c`.`id`)) left join `plantas_industriales` `pl` on(`p`.`planta_id` = `pl`.`id`)) where `p`.`estado` = 'Disponible' and coalesce(`p`.`cantidad_disp_kg`,`p`.`cantidad_kg`) > 0 and `u`.`estado` = 'activo' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13 17:38:17
