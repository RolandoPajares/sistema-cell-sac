-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: md_tecnologia_digital_cell
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
-- Table structure for table `almacenamiento_temporal`
--

DROP TABLE IF EXISTS `almacenamiento_temporal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `almacenamiento_temporal` (
  `clave` varchar(255) NOT NULL,
  `valor` mediumtext NOT NULL,
  `expira_en` bigint(20) NOT NULL,
  PRIMARY KEY (`clave`),
  KEY `ind_almacenamiento_temporal_vencimiento` (`expira_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `almacenamiento_temporal`
--

LOCK TABLES `almacenamiento_temporal` WRITE;
/*!40000 ALTER TABLE `almacenamiento_temporal` DISABLE KEYS */;
/*!40000 ALTER TABLE `almacenamiento_temporal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `almacenes`
--

DROP TABLE IF EXISTS `almacenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `almacenes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sede_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `tipo` varchar(30) NOT NULL DEFAULT 'general',
  `permite_venta` tinyint(1) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_almacenes_sede_codigo` (`sede_id`,`codigo`),
  UNIQUE KEY `un_almacenes_id_sede` (`id`,`sede_id`),
  KEY `ind_almacenes_sede_activo` (`sede_id`,`activo`),
  CONSTRAINT `cf_almacenes_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `almacenes`
--

LOCK TABLES `almacenes` WRITE;
/*!40000 ALTER TABLE `almacenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `almacenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aplicaciones_cobro`
--

DROP TABLE IF EXISTS `aplicaciones_cobro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aplicaciones_cobro` (
  `pago_id` bigint(20) unsigned NOT NULL,
  `cuenta_cobrar_id` bigint(20) unsigned NOT NULL,
  `importe_aplicado` decimal(15,2) NOT NULL,
  `aplicado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`pago_id`,`cuenta_cobrar_id`),
  KEY `ind_aplicaciones_cobro_cuenta` (`cuenta_cobrar_id`,`aplicado_en`),
  CONSTRAINT `cf_aplicaciones_cobro_cuenta` FOREIGN KEY (`cuenta_cobrar_id`) REFERENCES `cuentas_por_cobrar` (`id`),
  CONSTRAINT `cf_aplicaciones_cobro_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  CONSTRAINT `ver_aplicaciones_cobro_importe` CHECK (`importe_aplicado` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aplicaciones_cobro`
--

LOCK TABLES `aplicaciones_cobro` WRITE;
/*!40000 ALTER TABLE `aplicaciones_cobro` DISABLE KEYS */;
/*!40000 ALTER TABLE `aplicaciones_cobro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aplicaciones_pago`
--

DROP TABLE IF EXISTS `aplicaciones_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aplicaciones_pago` (
  `pago_id` bigint(20) unsigned NOT NULL,
  `cuenta_pagar_id` bigint(20) unsigned NOT NULL,
  `importe_aplicado` decimal(15,2) NOT NULL,
  `aplicado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`pago_id`,`cuenta_pagar_id`),
  KEY `ind_aplicaciones_pago_cuenta` (`cuenta_pagar_id`,`aplicado_en`),
  CONSTRAINT `cf_aplicaciones_pago_cuenta` FOREIGN KEY (`cuenta_pagar_id`) REFERENCES `cuentas_por_pagar` (`id`),
  CONSTRAINT `cf_aplicaciones_pago_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  CONSTRAINT `ver_aplicaciones_pago_importe` CHECK (`importe_aplicado` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aplicaciones_pago`
--

LOCK TABLES `aplicaciones_pago` WRITE;
/*!40000 ALTER TABLE `aplicaciones_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `aplicaciones_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archivos_documentales`
--

DROP TABLE IF EXISTS `archivos_documentales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archivos_documentales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `documento_electronico_id` bigint(20) unsigned DEFAULT NULL,
  `tipo_archivo` varchar(50) NOT NULL DEFAULT 'adjunto',
  `contrato_id` bigint(20) unsigned DEFAULT NULL,
  `devolucion_id` int(10) unsigned DEFAULT NULL,
  `guia_id` bigint(20) unsigned DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `ruta_almacenamiento` varchar(700) NOT NULL,
  `tipo_mime` varchar(150) DEFAULT NULL,
  `tamano_bytes` bigint(20) unsigned DEFAULT NULL,
  `sha256` char(64) DEFAULT NULL,
  `cargado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  KEY `ind_archivos_documento` (`documento_electronico_id`),
  KEY `ind_archivos_contrato` (`contrato_id`),
  KEY `ind_archivos_devolucion` (`devolucion_id`),
  KEY `ind_archivos_guia` (`guia_id`),
  KEY `cf_archivos_usuario` (`cargado_por_usuario_id`),
  CONSTRAINT `cf_archivos_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  CONSTRAINT `cf_archivos_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones_garantias` (`id`),
  CONSTRAINT `cf_archivos_documento` FOREIGN KEY (`documento_electronico_id`) REFERENCES `documentos_electronicos` (`id`),
  CONSTRAINT `cf_archivos_guia` FOREIGN KEY (`guia_id`) REFERENCES `guias_remision` (`id`),
  CONSTRAINT `cf_archivos_usuario` FOREIGN KEY (`cargado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_archivos_un_propietario` CHECK ((`documento_electronico_id` is not null) + (`contrato_id` is not null) + (`devolucion_id` is not null) + (`guia_id` is not null) = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archivos_documentales`
--

LOCK TABLES `archivos_documentales` WRITE;
/*!40000 ALTER TABLE `archivos_documentales` DISABLE KEYS */;
/*!40000 ALTER TABLE `archivos_documentales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asientos_contables`
--

DROP TABLE IF EXISTS `asientos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asientos_contables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `numero` varchar(60) NOT NULL,
  `fecha_asiento` date NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'borrador',
  `origen_modulo` varchar(80) DEFAULT NULL,
  `origen_tipo` varchar(80) DEFAULT NULL,
  `origen_id` varchar(100) DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_asientos_empresa_numero` (`empresa_id`,`numero`),
  KEY `ind_asientos_sede_fecha_estado` (`sede_id`,`fecha_asiento`,`estado`),
  KEY `ind_asientos_origen` (`origen_modulo`,`origen_tipo`,`origen_id`),
  KEY `cf_asientos_usuario` (`usuario_id`),
  KEY `cf_asientos_sede_empresa` (`sede_id`,`empresa_id`),
  CONSTRAINT `cf_asientos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_asientos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_asientos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_asientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asientos_contables`
--

LOCK TABLES `asientos_contables` WRITE;
/*!40000 ALTER TABLE `asientos_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `asientos_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bloqueos_almacenamiento_temporal`
--

DROP TABLE IF EXISTS `bloqueos_almacenamiento_temporal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bloqueos_almacenamiento_temporal` (
  `clave` varchar(255) NOT NULL,
  `propietario` varchar(255) NOT NULL,
  `expira_en` bigint(20) NOT NULL,
  PRIMARY KEY (`clave`),
  KEY `ind_bloqueos_almacenamiento_temporal_vencimiento` (`expira_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloqueos_almacenamiento_temporal`
--

LOCK TABLES `bloqueos_almacenamiento_temporal` WRITE;
/*!40000 ALTER TABLE `bloqueos_almacenamiento_temporal` DISABLE KEYS */;
/*!40000 ALTER TABLE `bloqueos_almacenamiento_temporal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cajas`
--

DROP TABLE IF EXISTS `cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cajas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sede_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_cajas_sede_codigo` (`sede_id`,`codigo`),
  CONSTRAINT `cf_cajas_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cajas`
--

LOCK TABLES `cajas` WRITE;
/*!40000 ALTER TABLE `cajas` DISABLE KEYS */;
/*!40000 ALTER TABLE `cajas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campanas_publicitarias`
--

DROP TABLE IF EXISTS `campanas_publicitarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campanas_publicitarias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `ubicacion` varchar(30) NOT NULL DEFAULT 'emergente',
  `titulo` varchar(160) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `texto_boton` varchar(80) NOT NULL DEFAULT 'Ver oferta',
  `url_boton` varchar(255) NOT NULL DEFAULT 'catalog',
  `url_imagen` varchar(255) DEFAULT NULL,
  `precio_anterior` decimal(10,2) DEFAULT NULL,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `inicia_en` datetime(6) NOT NULL,
  `finaliza_en` datetime(6) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `vistas` int(10) unsigned NOT NULL DEFAULT 0,
  `clics` int(10) unsigned NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime(6) DEFAULT NULL,
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_campanas_estado_fechas` (`activo`,`inicia_en`,`finaliza_en`),
  KEY `ind_campanas_ubicacion` (`ubicacion`),
  KEY `ind_campanas_empresa` (`empresa_id`),
  CONSTRAINT `cf_campanas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `ver_campanas_fechas` CHECK (`finaliza_en` > `inicia_en`),
  CONSTRAINT `ver_campanas_precios` CHECK (`precio_oferta` is null or `precio_anterior` is null or `precio_oferta` <= `precio_anterior`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campanas_publicitarias`
--

LOCK TABLES `campanas_publicitarias` WRITE;
/*!40000 ALTER TABLE `campanas_publicitarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `campanas_publicitarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campanas_segmentos`
--

DROP TABLE IF EXISTS `campanas_segmentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campanas_segmentos` (
  `campana_id` int(10) unsigned NOT NULL,
  `segmento_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`campana_id`,`segmento_id`),
  KEY `ind_campana_segmento_segmento` (`segmento_id`),
  CONSTRAINT `cf_campana_segmento_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas_publicitarias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cf_campana_segmento_segmento` FOREIGN KEY (`segmento_id`) REFERENCES `segmentos_clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campanas_segmentos`
--

LOCK TABLES `campanas_segmentos` WRITE;
/*!40000 ALTER TABLE `campanas_segmentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `campanas_segmentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caracteristicas_productos`
--

DROP TABLE IF EXISTS `caracteristicas_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caracteristicas_productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_producto_caracteristicas_producto_nombre` (`producto_id`,`nombre`),
  CONSTRAINT `cf_producto_caracteristicas_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caracteristicas_productos`
--

LOCK TABLES `caracteristicas_productos` WRITE;
/*!40000 ALTER TABLE `caracteristicas_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `caracteristicas_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carritos`
--

DROP TABLE IF EXISTS `carritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carritos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `resumen_token` char(64) DEFAULT NULL,
  `pedido_id` int(10) unsigned DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'activo',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `expira_en` datetime(6) DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_carritos_token_resumen` (`resumen_token`),
  UNIQUE KEY `un_carritos_pedido` (`pedido_id`),
  KEY `ind_carritos_usuario_estado` (`usuario_id`,`estado`,`actualizado_en`),
  KEY `ind_carritos_cliente_estado` (`cliente_id`,`estado`,`actualizado_en`),
  KEY `cf_carritos_empresa` (`empresa_id`),
  KEY `cf_carritos_sede_empresa` (`sede_id`,`empresa_id`),
  CONSTRAINT `cf_carritos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `cf_carritos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_carritos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `cf_carritos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_carritos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_carritos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ver_carritos_propietario` CHECK (`usuario_id` is not null or `cliente_id` is not null or `resumen_token` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carritos`
--

LOCK TABLES `carritos` WRITE;
/*!40000 ALTER TABLE `carritos` DISABLE KEYS */;
/*!40000 ALTER TABLE `carritos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `casos_soporte`
--

DROP TABLE IF EXISTS `casos_soporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `casos_soporte` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `asignado_a_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `codigo` varchar(50) NOT NULL,
  `asunto` varchar(180) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'abierto',
  `prioridad` varchar(20) NOT NULL DEFAULT 'normal',
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_casos_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `ind_casos_sede_estado_prioridad` (`sede_id`,`estado`,`prioridad`),
  KEY `ind_casos_asignado_estado` (`asignado_a_usuario_id`,`estado`),
  KEY `cf_casos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_casos_cliente` (`cliente_id`),
  CONSTRAINT `cf_casos_asignado` FOREIGN KEY (`asignado_a_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_casos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_casos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_casos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_casos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casos_soporte`
--

LOCK TABLES `casos_soporte` WRITE;
/*!40000 ALTER TABLE `casos_soporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `casos_soporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_categorias_nombre` (`nombre`),
  UNIQUE KEY `un_categorias_id_empresa` (`id`,`empresa_id`),
  KEY `ind_categorias_activo` (`activo`),
  KEY `ind_categorias_empresa` (`empresa_id`),
  CONSTRAINT `cf_categorias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'minorista',
  `documento` varchar(20) NOT NULL,
  `razon_social` varchar(160) DEFAULT NULL,
  `nombre_contacto` varchar(160) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_clientes_documento` (`documento`),
  UNIQUE KEY `un_clientes_id_empresa` (`id`,`empresa_id`),
  KEY `ind_clientes_usuario` (`usuario_id`),
  KEY `ind_clientes_tipo_activo` (`tipo`,`activo`),
  KEY `ind_clientes_correo` (`correo`),
  KEY `ind_clientes_empresa` (`empresa_id`),
  CONSTRAINT `cf_clientes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_clientes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_clientes_tipo` CHECK (`tipo` in ('minorista','mayorista'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(10) unsigned NOT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(30) NOT NULL DEFAULT 'Pendiente',
  `fecha_compra` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `numero_referencia` varchar(100) DEFAULT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `actualizado_en` datetime(6) DEFAULT NULL,
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `almacen_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_compras_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
  KEY `ind_compras_estado_fecha` (`estado`,`fecha_compra`),
  KEY `ind_compras_proveedor` (`proveedor_id`),
  KEY `ind_compras_creador` (`creado_por_usuario_id`),
  KEY `ind_compras_empresa_fecha` (`empresa_id`,`fecha_compra`),
  KEY `ind_compras_sede_fecha` (`sede_id`,`fecha_compra`),
  KEY `cf_compras_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_compras_almacen_sede` (`almacen_id`,`sede_id`),
  KEY `cf_compras_proveedor_empresa` (`proveedor_id`,`empresa_id`),
  CONSTRAINT `cf_compras_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_compras_almacen_sede` FOREIGN KEY (`almacen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_compras_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_compras_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_compras_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `cf_compras_proveedor_empresa` FOREIGN KEY (`proveedor_id`, `empresa_id`) REFERENCES `proveedores` (`id`, `empresa_id`),
  CONSTRAINT `cf_compras_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_compras_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `ver_compras_total` CHECK (`total` >= 0),
  CONSTRAINT `ver_compras_ambito` CHECK ((`sede_id` is null or `empresa_id` is not null) and (`almacen_id` is null or `sede_id` is not null))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contratos`
--

DROP TABLE IF EXISTS `contratos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contratos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `proveedor_id` int(10) unsigned DEFAULT NULL,
  `codigo` varchar(60) NOT NULL,
  `tipo` varchar(60) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `inicia_en` date DEFAULT NULL,
  `finaliza_en` date DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_contratos_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `ind_contratos_empresa_estado_fin` (`empresa_id`,`estado`,`finaliza_en`),
  KEY `cf_contratos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_contratos_cliente` (`cliente_id`),
  KEY `cf_contratos_proveedor` (`proveedor_id`),
  KEY `cf_contratos_usuario` (`creado_por_usuario_id`),
  CONSTRAINT `cf_contratos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `cf_contratos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_contratos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `cf_contratos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_contratos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_contratos_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_contratos_contraparte` CHECK (`cliente_id` is not null <> (`proveedor_id` is not null)),
  CONSTRAINT `ver_contratos_fechas` CHECK (`finaliza_en` is null or `inicia_en` is null or `finaliza_en` >= `inicia_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contratos`
--

LOCK TABLES `contratos` WRITE;
/*!40000 ALTER TABLE `contratos` DISABLE KEYS */;
/*!40000 ALTER TABLE `contratos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cotizaciones`
--

DROP TABLE IF EXISTS `cotizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cotizaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(30) NOT NULL DEFAULT 'Borrador',
  `notas` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_cotizaciones_estado_fecha` (`estado`,`creado_en`),
  KEY `ind_cotizaciones_cliente` (`cliente_id`),
  KEY `ind_cotizaciones_creador` (`creado_por_usuario_id`),
  KEY `ind_cotizaciones_empresa_fecha` (`empresa_id`,`creado_en`),
  KEY `cf_cotizaciones_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_cotizaciones_cliente_empresa` (`cliente_id`,`empresa_id`),
  CONSTRAINT `cf_cotizaciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `cf_cotizaciones_cliente_empresa` FOREIGN KEY (`cliente_id`, `empresa_id`) REFERENCES `clientes` (`id`, `empresa_id`),
  CONSTRAINT `cf_cotizaciones_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_cotizaciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_cotizaciones_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_cotizaciones_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `ver_cotizaciones_total` CHECK (`total` >= 0),
  CONSTRAINT `ver_cotizaciones_ambito` CHECK (`sede_id` is null or `empresa_id` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotizaciones`
--

LOCK TABLES `cotizaciones` WRITE;
/*!40000 ALTER TABLE `cotizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_bancarias`
--

DROP TABLE IF EXISTS `cuentas_bancarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuentas_bancarias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `banco` varchar(120) NOT NULL,
  `tipo_cuenta` varchar(40) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `cuenta_mascara` varchar(40) DEFAULT NULL,
  `referencia_secreto` varchar(255) DEFAULT NULL COMMENT 'Referencia externa; no almacenar el secreto',
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_cuentas_bancarias_codigo` (`empresa_id`,`codigo`),
  UNIQUE KEY `un_cuentas_bancarias_id_empresa` (`id`,`empresa_id`),
  CONSTRAINT `cf_cuentas_bancarias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_bancarias`
--

LOCK TABLES `cuentas_bancarias` WRITE;
/*!40000 ALTER TABLE `cuentas_bancarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_bancarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_por_cobrar`
--

DROP TABLE IF EXISTS `cuentas_por_cobrar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuentas_por_cobrar` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `documento_electronico_id` bigint(20) unsigned NOT NULL,
  `monto_original` decimal(15,2) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `vence_en` date NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_cuentas_cobrar_documento` (`documento_electronico_id`),
  KEY `ind_cuentas_cobrar_estado_vencimiento` (`estado`,`vence_en`),
  CONSTRAINT `cf_cuentas_cobrar_documento` FOREIGN KEY (`documento_electronico_id`) REFERENCES `documentos_electronicos` (`id`),
  CONSTRAINT `ver_cuentas_cobrar_monto` CHECK (`monto_original` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_por_cobrar`
--

LOCK TABLES `cuentas_por_cobrar` WRITE;
/*!40000 ALTER TABLE `cuentas_por_cobrar` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_por_cobrar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_por_pagar`
--

DROP TABLE IF EXISTS `cuentas_por_pagar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuentas_por_pagar` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` int(10) unsigned NOT NULL,
  `monto_original` decimal(15,2) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `vence_en` date NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_cuentas_pagar_compra` (`compra_id`),
  KEY `ind_cuentas_pagar_estado_vencimiento` (`estado`,`vence_en`),
  CONSTRAINT `cf_cuentas_pagar_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `ver_cuentas_pagar_monto` CHECK (`monto_original` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_por_pagar`
--

LOCK TABLES `cuentas_por_pagar` WRITE;
/*!40000 ALTER TABLE `cuentas_por_pagar` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_por_pagar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cupones`
--

DROP TABLE IF EXISTS `cupones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cupones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `tipo_descuento` varchar(20) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `minimo_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `limite_usos` int(10) unsigned DEFAULT NULL,
  `usos` int(10) unsigned NOT NULL DEFAULT 0,
  `inicia_en` datetime(6) NOT NULL,
  `finaliza_en` datetime(6) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_cupones_codigo` (`codigo`),
  KEY `ind_cupones_vigencia` (`activo`,`inicia_en`,`finaliza_en`),
  KEY `ind_cupones_activo` (`activo`),
  CONSTRAINT `ver_cupones_valor` CHECK (`valor` > 0 and `minimo_compra` >= 0 and (`limite_usos` is null or `limite_usos` > 0)),
  CONSTRAINT `ver_cupones_vigencia` CHECK (`finaliza_en` > `inicia_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cupones`
--

LOCK TABLES `cupones` WRITE;
/*!40000 ALTER TABLE `cupones` DISABLE KEYS */;
/*!40000 ALTER TABLE `cupones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_carritos`
--

DROP TABLE IF EXISTS `detalles_carritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_carritos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `carrito_id` bigint(20) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `variante_presente` tinyint(3) unsigned GENERATED ALWAYS AS (case when `variante_id` is null then 0 else 1 end) STORED,
  `variante_clave` int(10) unsigned GENERATED ALWAYS AS (ifnull(`variante_id`,0)) STORED,
  `cantidad` decimal(18,6) NOT NULL,
  `precio_mostrado_historico` decimal(15,2) DEFAULT NULL,
  `agregado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_carrito_detalles_variante` (`carrito_id`,`variante_id`),
  UNIQUE KEY `un_carrito_producto_modo` (`carrito_id`,`producto_id`,`variante_presente`,`variante_clave`),
  KEY `cf_carrito_detalles_producto` (`producto_id`),
  KEY `cf_carrito_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_carrito_detalles_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cf_carrito_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_carrito_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_carrito_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_carrito_detalles_importes` CHECK (`cantidad` > 0 and (`precio_mostrado_historico` is null or `precio_mostrado_historico` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_carritos`
--

LOCK TABLES `detalles_carritos` WRITE;
/*!40000 ALTER TABLE `detalles_carritos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_carritos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_compras`
--

DROP TABLE IF EXISTS `detalles_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_compras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_compra_detalles_compra` (`compra_id`),
  KEY `ind_compra_detalles_producto` (`producto_id`),
  KEY `ind_compra_detalles_variante` (`variante_id`),
  KEY `cf_compra_detalles_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_compra_detalles_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `cf_compra_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_compra_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_compra_detalles_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_compra_detalles_importes` CHECK (`cantidad` > 0 and `costo_unitario` >= 0 and `descuento` >= 0 and `impuesto` >= 0 and (`total_linea` is null or `total_linea` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_compras`
--

LOCK TABLES `detalles_compras` WRITE;
/*!40000 ALTER TABLE `detalles_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_compras_dispositivos`
--

DROP TABLE IF EXISTS `detalles_compras_dispositivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_compras_dispositivos` (
  `detalle_compra_id` bigint(20) unsigned NOT NULL,
  `dispositivo_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`detalle_compra_id`,`dispositivo_id`),
  UNIQUE KEY `un_compra_dispositivo` (`dispositivo_id`),
  CONSTRAINT `cf_compra_dispositivo_detalle` FOREIGN KEY (`detalle_compra_id`) REFERENCES `detalles_compras` (`id`),
  CONSTRAINT `cf_compra_dispositivo_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_compras_dispositivos`
--

LOCK TABLES `detalles_compras_dispositivos` WRITE;
/*!40000 ALTER TABLE `detalles_compras_dispositivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_compras_dispositivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_cotizaciones`
--

DROP TABLE IF EXISTS `detalles_cotizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_cotizaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cotizacion_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `descripcion_historica` varchar(255) DEFAULT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_cotizacion_detalles_cotizacion` (`cotizacion_id`),
  KEY `ind_cotizacion_detalles_producto` (`producto_id`),
  KEY `ind_cotizacion_detalles_variante` (`variante_id`),
  KEY `cf_cotizacion_detalles_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_cotizacion_detalles_cotizacion` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  CONSTRAINT `cf_cotizacion_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_cotizacion_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_cotizacion_detalles_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_cotizacion_detalles_importes` CHECK (`cantidad` > 0 and `precio_unitario` >= 0 and `descuento` >= 0 and `impuesto` >= 0 and (`total_linea` is null or `total_linea` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_cotizaciones`
--

LOCK TABLES `detalles_cotizaciones` WRITE;
/*!40000 ALTER TABLE `detalles_cotizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_devoluciones`
--

DROP TABLE IF EXISTS `detalles_devoluciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_devoluciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `devolucion_id` int(10) unsigned NOT NULL,
  `detalle_venta_id` bigint(20) unsigned DEFAULT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `dispositivo_id` bigint(20) unsigned DEFAULT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `estado_revision` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_devolucion_detalles_devolucion` (`devolucion_id`),
  KEY `ind_devolucion_detalles_dispositivo` (`dispositivo_id`),
  KEY `cf_devolucion_detalles_venta` (`detalle_venta_id`),
  KEY `cf_devolucion_detalles_producto` (`producto_id`),
  KEY `cf_devolucion_detalles_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_devolucion_detalles_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones_garantias` (`id`),
  CONSTRAINT `cf_devolucion_detalles_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`),
  CONSTRAINT `cf_devolucion_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_devolucion_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_devolucion_detalles_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `cf_devolucion_detalles_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`),
  CONSTRAINT `ver_devolucion_detalles_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_devoluciones`
--

LOCK TABLES `detalles_devoluciones` WRITE;
/*!40000 ALTER TABLE `detalles_devoluciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_devoluciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_documentos_electronicos`
--

DROP TABLE IF EXISTS `detalles_documentos_electronicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_documentos_electronicos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `documento_id` bigint(20) unsigned NOT NULL,
  `detalle_venta_id` bigint(20) unsigned DEFAULT NULL,
  `codigo_producto` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `unidad_medida` varchar(12) NOT NULL DEFAULT 'NIU',
  `valor_unitario` decimal(15,6) NOT NULL,
  `precio_unitario` decimal(15,6) NOT NULL,
  `tasa_impuesto` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_documento_detalles_documento` (`documento_id`),
  KEY `ind_documento_detalles_venta_linea` (`detalle_venta_id`),
  CONSTRAINT `cf_documento_detalle_documento` FOREIGN KEY (`documento_id`) REFERENCES `documentos_electronicos` (`id`),
  CONSTRAINT `cf_documento_detalle_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_documento_detalle_importes` CHECK (`cantidad` > 0 and `valor_unitario` >= 0 and `precio_unitario` >= 0 and `tasa_impuesto` >= 0 and `impuesto` >= 0 and `total_linea` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_documentos_electronicos`
--

LOCK TABLES `detalles_documentos_electronicos` WRITE;
/*!40000 ALTER TABLE `detalles_documentos_electronicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_documentos_electronicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_envios_pedidos`
--

DROP TABLE IF EXISTS `detalles_envios_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_envios_pedidos` (
  `envio_pedido_id` bigint(20) unsigned NOT NULL,
  `detalle_pedido_id` int(10) unsigned NOT NULL,
  `pedido_id` int(10) unsigned NOT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  PRIMARY KEY (`envio_pedido_id`,`detalle_pedido_id`),
  KEY `ind_envio_detalle_pedido` (`detalle_pedido_id`,`pedido_id`),
  KEY `cf_envio_detalles_envio` (`envio_pedido_id`,`pedido_id`),
  CONSTRAINT `cf_envio_detalles_envio` FOREIGN KEY (`envio_pedido_id`, `pedido_id`) REFERENCES `envios_pedidos` (`id`, `pedido_id`),
  CONSTRAINT `cf_envio_detalles_pedido_detalle` FOREIGN KEY (`detalle_pedido_id`, `pedido_id`) REFERENCES `detalles_pedidos` (`id`, `pedido_id`),
  CONSTRAINT `ver_envio_detalles_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_envios_pedidos`
--

LOCK TABLES `detalles_envios_pedidos` WRITE;
/*!40000 ALTER TABLE `detalles_envios_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_envios_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_guias_remision`
--

DROP TABLE IF EXISTS `detalles_guias_remision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_guias_remision` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guia_id` bigint(20) unsigned NOT NULL,
  `detalle_venta_id` bigint(20) unsigned DEFAULT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `unidad_medida` varchar(12) NOT NULL DEFAULT 'NIU',
  PRIMARY KEY (`id`),
  KEY `ind_guia_detalles_guia` (`guia_id`),
  KEY `cf_guia_detalles_venta` (`detalle_venta_id`),
  KEY `cf_guia_detalles_producto` (`producto_id`),
  KEY `cf_guia_remision_detalles_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_guia_detalles_guia` FOREIGN KEY (`guia_id`) REFERENCES `guias_remision` (`id`),
  CONSTRAINT `cf_guia_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_guia_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_guia_detalles_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_guia_remision_detalles_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_guia_detalles_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_guias_remision`
--

LOCK TABLES `detalles_guias_remision` WRITE;
/*!40000 ALTER TABLE `detalles_guias_remision` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_guias_remision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_pedidos`
--

DROP TABLE IF EXISTS `detalles_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_pedidos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `descripcion_historica` varchar(255) DEFAULT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_detalle_pedido_id_pedido` (`id`,`pedido_id`),
  KEY `ind_detalle_pedidos_pedido` (`pedido_id`),
  KEY `ind_detalle_pedidos_producto` (`producto_id`),
  KEY `ind_detalle_pedidos_variante` (`variante_id`),
  KEY `cf_detalle_pedidos_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_detalle_pedidos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `cf_detalle_pedidos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_detalle_pedidos_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_detalle_pedidos_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_detalle_pedidos_importes` CHECK (`cantidad` > 0 and `precio_unitario` >= 0 and `descuento` >= 0 and `impuesto` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pedidos`
--

LOCK TABLES `detalles_pedidos` WRITE;
/*!40000 ALTER TABLE `detalles_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_transferencias_existencias`
--

DROP TABLE IF EXISTS `detalles_transferencias_existencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_transferencias_existencias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transferencia_id` bigint(20) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `variante_presente` tinyint(3) unsigned GENERATED ALWAYS AS (case when `variante_id` is null then 0 else 1 end) STORED,
  `variante_clave` int(10) unsigned GENERATED ALWAYS AS (ifnull(`variante_id`,0)) STORED,
  `cantidad_solicitada` decimal(18,6) NOT NULL,
  `cantidad_enviada` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `cantidad_recibida` decimal(18,6) NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_transferencia_detalle_variante` (`transferencia_id`,`variante_id`),
  UNIQUE KEY `un_transferencia_producto_modo` (`transferencia_id`,`producto_id`,`variante_presente`,`variante_clave`),
  KEY `ind_transferencia_detalle_producto` (`producto_id`,`variante_id`),
  KEY `cf_transferencia_existencias_detalles_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_transferencia_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_transferencia_detalle_transferencia` FOREIGN KEY (`transferencia_id`) REFERENCES `transferencias_existencias` (`id`),
  CONSTRAINT `cf_transferencia_detalle_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_transferencia_existencias_detalles_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_transferencia_detalle_cantidades` CHECK (`cantidad_solicitada` > 0 and `cantidad_enviada` >= 0 and `cantidad_recibida` >= 0 and `cantidad_recibida` <= `cantidad_enviada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_transferencias_existencias`
--

LOCK TABLES `detalles_transferencias_existencias` WRITE;
/*!40000 ALTER TABLE `detalles_transferencias_existencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_transferencias_existencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_ventas`
--

DROP TABLE IF EXISTS `detalles_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_ventas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint(20) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `detalle_pedido_id` int(10) unsigned DEFAULT NULL,
  `descripcion_historica` varchar(255) NOT NULL,
  `sku_historico` varchar(80) DEFAULT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tasa_impuesto` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_venta_detalle_pedido_linea` (`detalle_pedido_id`),
  KEY `ind_venta_detalles_venta` (`venta_id`),
  KEY `ind_venta_detalles_producto_variante` (`producto_id`,`variante_id`),
  KEY `cf_venta_detalles_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_venta_detalles_pedido_detalle` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalles_pedidos` (`id`),
  CONSTRAINT `cf_venta_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_venta_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_venta_detalles_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `cf_venta_detalles_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `ver_venta_detalles_importes` CHECK (`cantidad` > 0 and `precio_unitario` >= 0 and `descuento` >= 0 and `tasa_impuesto` >= 0 and `impuesto` >= 0 and `total_linea` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_ventas`
--

LOCK TABLES `detalles_ventas` WRITE;
/*!40000 ALTER TABLE `detalles_ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_ventas_dispositivos`
--

DROP TABLE IF EXISTS `detalles_ventas_dispositivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_ventas_dispositivos` (
  `detalle_venta_id` bigint(20) unsigned NOT NULL,
  `dispositivo_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`detalle_venta_id`,`dispositivo_id`),
  UNIQUE KEY `un_venta_dispositivo` (`dispositivo_id`),
  CONSTRAINT `cf_venta_dispositivo_detalle` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`),
  CONSTRAINT `cf_venta_dispositivo_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_ventas_dispositivos`
--

LOCK TABLES `detalles_ventas_dispositivos` WRITE;
/*!40000 ALTER TABLE `detalles_ventas_dispositivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_ventas_dispositivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devoluciones_garantias`
--

DROP TABLE IF EXISTS `devoluciones_garantias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devoluciones_garantias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(40) NOT NULL,
  `pedido_id` int(10) unsigned NOT NULL,
  `cliente_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `tipo` varchar(30) NOT NULL,
  `motivo` text NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'Recibida',
  `fecha_solicitud` date NOT NULL,
  `fecha_limite` date DEFAULT NULL,
  `resolucion` text DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `venta_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_devoluciones_codigo` (`codigo`),
  KEY `ind_devoluciones_estado_fecha` (`estado`,`fecha_solicitud`),
  KEY `ind_devoluciones_tipo` (`tipo`),
  KEY `ind_devoluciones_fecha_limite` (`fecha_limite`),
  KEY `ind_devoluciones_activo` (`activo`),
  KEY `ind_devoluciones_creador` (`creado_por_usuario_id`),
  KEY `ind_devoluciones_pedido` (`pedido_id`),
  KEY `ind_devoluciones_cliente` (`cliente_id`),
  KEY `ind_devoluciones_producto` (`producto_id`),
  KEY `ind_devoluciones_venta` (`venta_id`),
  KEY `ind_devoluciones_empresa_fecha` (`empresa_id`,`fecha_solicitud`),
  KEY `cf_devoluciones_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `ind_devoluciones_fecha_solicitud` (`fecha_solicitud`),
  CONSTRAINT `cf_devoluciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `cf_devoluciones_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_devoluciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_devoluciones_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `cf_devoluciones_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_devoluciones_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_devoluciones_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_devoluciones_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `ver_devoluciones_fechas` CHECK (`fecha_limite` is null or `fecha_limite` >= `fecha_solicitud`),
  CONSTRAINT `ver_devoluciones_ambito` CHECK (`sede_id` is null or `empresa_id` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devoluciones_garantias`
--

LOCK TABLES `devoluciones_garantias` WRITE;
/*!40000 ALTER TABLE `devoluciones_garantias` DISABLE KEYS */;
/*!40000 ALTER TABLE `devoluciones_garantias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direcciones_cliente`
--

DROP TABLE IF EXISTS `direcciones_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direcciones_cliente` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `alias` varchar(80) NOT NULL DEFAULT 'Principal',
  `destinatario` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `pais` char(2) NOT NULL DEFAULT 'PE',
  `departamento` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `distrito` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `direccion_linea1` varchar(255) NOT NULL,
  `direccion_linea2` varchar(255) DEFAULT NULL,
  `referencia` varchar(500) DEFAULT NULL,
  `es_predeterminada` tinyint(1) NOT NULL DEFAULT 0,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_direcciones_cliente_id` (`cliente_id`,`id`),
  KEY `ind_direcciones_cliente_activa` (`cliente_id`,`activa`,`es_predeterminada`),
  CONSTRAINT `cf_direcciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direcciones_cliente`
--

LOCK TABLES `direcciones_cliente` WRITE;
/*!40000 ALTER TABLE `direcciones_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `direcciones_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dispositivos`
--

DROP TABLE IF EXISTS `dispositivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispositivos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `imei` varchar(20) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `detalle_compra_id` bigint(20) unsigned DEFAULT NULL,
  `almacen_actual_id` bigint(20) unsigned DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'disponible',
  `fecha_ingreso` datetime(6) DEFAULT NULL,
  `fecha_salida` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_dispositivos_imei` (`imei`),
  UNIQUE KEY `un_dispositivos_serie` (`numero_serie`),
  KEY `ind_dispositivos_variante_estado` (`variante_id`,`estado`),
  KEY `ind_dispositivos_almacen_estado` (`almacen_actual_id`,`estado`),
  KEY `cf_dispositivos_compra_detalle` (`detalle_compra_id`),
  KEY `cf_dispositivos_producto` (`producto_id`),
  KEY `cf_dispositivos_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_dispositivos_almacen` FOREIGN KEY (`almacen_actual_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_dispositivos_compra_detalle` FOREIGN KEY (`detalle_compra_id`) REFERENCES `detalles_compras` (`id`),
  CONSTRAINT `cf_dispositivos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_dispositivos_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_dispositivos_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_dispositivos_identificador` CHECK (`imei` is not null or `numero_serie` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dispositivos`
--

LOCK TABLES `dispositivos` WRITE;
/*!40000 ALTER TABLE `dispositivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `dispositivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos_electronicos`
--

DROP TABLE IF EXISTS `documentos_electronicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentos_electronicos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `serie_documento_id` bigint(20) unsigned NOT NULL,
  `venta_id` bigint(20) unsigned DEFAULT NULL,
  `documento_referencia_id` bigint(20) unsigned DEFAULT NULL,
  `tipo_documento_id` char(2) NOT NULL,
  `serie` varchar(8) NOT NULL,
  `correlativo` bigint(20) unsigned NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `emisor_historico` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`emisor_historico`)),
  `receptor_historico` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`receptor_historico`)),
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha_emision` datetime(6) NOT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_documento_numero` (`empresa_id`,`tipo_documento_id`,`serie`,`correlativo`),
  KEY `ind_documento_sede_estado_fecha` (`sede_id`,`estado`,`fecha_emision`),
  KEY `ind_documento_venta` (`venta_id`),
  KEY `ind_documento_referencia` (`documento_referencia_id`),
  KEY `cf_documentos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_documentos_tipo` (`tipo_documento_id`),
  KEY `cf_documentos_usuario` (`creado_por_usuario_id`),
  KEY `cf_documentos_serie_contexto` (`serie_documento_id`,`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`),
  KEY `cf_documentos_venta_ambito` (`venta_id`,`empresa_id`,`sede_id`),
  CONSTRAINT `cf_documentos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_documentos_referencia` FOREIGN KEY (`documento_referencia_id`) REFERENCES `documentos_electronicos` (`id`),
  CONSTRAINT `cf_documentos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_documentos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_documentos_serie` FOREIGN KEY (`serie_documento_id`) REFERENCES `series_documentos` (`id`),
  CONSTRAINT `cf_documentos_serie_contexto` FOREIGN KEY (`serie_documento_id`, `empresa_id`, `sede_id`, `tipo_documento_id`, `serie`) REFERENCES `series_documentos` (`id`, `empresa_id`, `sede_id`, `tipo_documento_id`, `serie`),
  CONSTRAINT `cf_documentos_tipo` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento_electronico` (`codigo`),
  CONSTRAINT `cf_documentos_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_documentos_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `cf_documentos_venta_ambito` FOREIGN KEY (`venta_id`, `empresa_id`, `sede_id`) REFERENCES `ventas` (`id`, `empresa_id`, `sede_id`),
  CONSTRAINT `ver_documentos_importes` CHECK (`subtotal` >= 0 and `impuestos` >= 0 and `total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos_electronicos`
--

LOCK TABLES `documentos_electronicos` WRITE;
/*!40000 ALTER TABLE `documentos_electronicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `documentos_electronicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elementos_menu`
--

DROP TABLE IF EXISTS `elementos_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elementos_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) unsigned NOT NULL,
  `padre_id` bigint(20) unsigned DEFAULT NULL,
  `pantalla_id` bigint(20) unsigned DEFAULT NULL,
  `permiso_id` bigint(20) unsigned DEFAULT NULL,
  `codigo` varchar(100) NOT NULL,
  `etiqueta` varchar(160) NOT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'pantalla',
  `orden` int(10) unsigned NOT NULL DEFAULT 0,
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_elementos_menu_codigo` (`menu_id`,`codigo`),
  UNIQUE KEY `un_elementos_menu_id_menu` (`id`,`menu_id`),
  KEY `ind_elementos_menu_padre` (`menu_id`,`padre_id`,`habilitado`,`orden`),
  KEY `ind_elementos_menu_pantalla` (`pantalla_id`),
  KEY `ind_elementos_menu_permiso` (`permiso_id`),
  KEY `cf_elementos_menu_padre_menu` (`padre_id`,`menu_id`),
  KEY `cf_elementos_menu_permiso_pantalla` (`permiso_id`,`pantalla_id`),
  CONSTRAINT `cf_elementos_menu_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  CONSTRAINT `cf_elementos_menu_padre` FOREIGN KEY (`padre_id`) REFERENCES `elementos_menu` (`id`),
  CONSTRAINT `cf_elementos_menu_padre_menu` FOREIGN KEY (`padre_id`, `menu_id`) REFERENCES `elementos_menu` (`id`, `menu_id`),
  CONSTRAINT `cf_elementos_menu_pantalla` FOREIGN KEY (`pantalla_id`) REFERENCES `pantallas` (`id`),
  CONSTRAINT `cf_elementos_menu_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`),
  CONSTRAINT `cf_elementos_menu_permiso_pantalla` FOREIGN KEY (`permiso_id`, `pantalla_id`) REFERENCES `permisos` (`id`, `pantalla_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elementos_menu`
--

LOCK TABLES `elementos_menu` WRITE;
/*!40000 ALTER TABLE `elementos_menu` DISABLE KEYS */;
/*!40000 ALTER TABLE `elementos_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `razon_social` varchar(180) NOT NULL,
  `nombre_comercial` varchar(180) DEFAULT NULL,
  `ruc` char(11) DEFAULT NULL,
  `moneda_base` char(3) NOT NULL DEFAULT 'PEN',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_empresas_codigo` (`codigo`),
  UNIQUE KEY `un_empresas_ruc` (`ruc`),
  KEY `ind_empresas_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `envios_pedidos`
--

DROP TABLE IF EXISTS `envios_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `envios_pedidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `pedido_id` int(10) unsigned NOT NULL,
  `almacen_id` bigint(20) unsigned NOT NULL,
  `guia_remision_id` bigint(20) unsigned DEFAULT NULL,
  `codigo` varchar(60) NOT NULL,
  `modalidad` varchar(30) NOT NULL,
  `transportista` varchar(120) DEFAULT NULL,
  `servicio` varchar(120) DEFAULT NULL,
  `numero_seguimiento` varchar(160) DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `costo_envio` decimal(15,2) NOT NULL DEFAULT 0.00,
  `destino_historico` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`destino_historico`)),
  `estimado_entrega_en` datetime(6) DEFAULT NULL,
  `despachado_en` datetime(6) DEFAULT NULL,
  `entregado_en` datetime(6) DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_envios_empresa_codigo` (`empresa_id`,`codigo`),
  UNIQUE KEY `un_envios_id_pedido` (`id`,`pedido_id`),
  UNIQUE KEY `un_envios_guia` (`guia_remision_id`),
  KEY `ind_envios_sede_estado_fecha` (`sede_id`,`estado`,`creado_en`),
  KEY `ind_envios_seguimiento` (`transportista`,`numero_seguimiento`),
  KEY `cf_envios_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_envios_almacen_sede` (`almacen_id`,`sede_id`),
  KEY `cf_envios_usuario` (`creado_por_usuario_id`),
  KEY `cf_envios_pedido_ambito` (`pedido_id`,`empresa_id`,`sede_id`),
  CONSTRAINT `cf_envios_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_envios_almacen_sede` FOREIGN KEY (`almacen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_envios_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_envios_guia` FOREIGN KEY (`guia_remision_id`) REFERENCES `guias_remision` (`id`),
  CONSTRAINT `cf_envios_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `cf_envios_pedido_ambito` FOREIGN KEY (`pedido_id`, `empresa_id`, `sede_id`) REFERENCES `pedidos` (`id`, `empresa_id`, `sede_id`),
  CONSTRAINT `cf_envios_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_envios_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_envios_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_envios_costos` CHECK (`costo_envio` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `envios_pedidos`
--

LOCK TABLES `envios_pedidos` WRITE;
/*!40000 ALTER TABLE `envios_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `envios_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `envios_sunat`
--

DROP TABLE IF EXISTS `envios_sunat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `envios_sunat` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `documento_id` bigint(20) unsigned NOT NULL,
  `numero_intento` smallint(5) unsigned NOT NULL,
  `estado` varchar(30) NOT NULL,
  `codigo_respuesta` varchar(40) DEFAULT NULL,
  `descripcion_respuesta` varchar(2000) DEFAULT NULL,
  `resumen_solicitud` char(64) DEFAULT NULL,
  `resumen_respuesta` char(64) DEFAULT NULL,
  `enviado_en` datetime(6) DEFAULT NULL,
  `respondido_en` datetime(6) DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_envios_sunat_intento` (`documento_id`,`numero_intento`),
  KEY `ind_envios_sunat_estado_fecha` (`estado`,`creado_en`),
  CONSTRAINT `cf_envios_sunat_documento` FOREIGN KEY (`documento_id`) REFERENCES `documentos_electronicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `envios_sunat`
--

LOCK TABLES `envios_sunat` WRITE;
/*!40000 ALTER TABLE `envios_sunat` DISABLE KEYS */;
/*!40000 ALTER TABLE `envios_sunat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_campana`
--

DROP TABLE IF EXISTS `eventos_campana`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos_campana` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campana_id` int(10) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` varchar(20) NOT NULL,
  `resumen_sesion` char(64) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `pedido_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_eventos_campana_usuario` (`usuario_id`),
  KEY `ind_eventos_campana_sesion` (`resumen_sesion`),
  KEY `ind_eventos_campana_tipo` (`tipo`),
  KEY `ind_eventos_campana_campana_tipo_fecha` (`campana_id`,`tipo`,`creado_en`),
  KEY `ind_eventos_campana_pedido` (`pedido_id`),
  CONSTRAINT `cf_eventos_campana_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas_publicitarias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cf_eventos_campana_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_eventos_campana_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_campana`
--

LOCK TABLES `eventos_campana` WRITE;
/*!40000 ALTER TABLE `eventos_campana` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_campana` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_casos_soporte`
--

DROP TABLE IF EXISTS `eventos_casos_soporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos_casos_soporte` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `caso_soporte_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` varchar(40) NOT NULL,
  `contenido` text DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  KEY `ind_caso_eventos_fecha` (`caso_soporte_id`,`creado_en`),
  KEY `cf_caso_eventos_usuario` (`usuario_id`),
  CONSTRAINT `cf_caso_eventos_caso` FOREIGN KEY (`caso_soporte_id`) REFERENCES `casos_soporte` (`id`),
  CONSTRAINT `cf_caso_eventos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_casos_soporte`
--

LOCK TABLES `eventos_casos_soporte` WRITE;
/*!40000 ALTER TABLE `eventos_casos_soporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_casos_soporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_envios_pedidos`
--

DROP TABLE IF EXISTS `eventos_envios_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos_envios_pedidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `envio_pedido_id` bigint(20) unsigned NOT NULL,
  `estado` varchar(30) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `fuente` varchar(30) NOT NULL DEFAULT 'interno',
  `identificador_externo` varchar(160) DEFAULT NULL,
  `ocurrido_en` datetime(6) NOT NULL,
  `registrado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `datos_seguros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_seguros`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_eventos_envio_externo` (`envio_pedido_id`,`fuente`,`identificador_externo`),
  KEY `ind_eventos_envio_fecha` (`envio_pedido_id`,`ocurrido_en`),
  CONSTRAINT `cf_eventos_envio` FOREIGN KEY (`envio_pedido_id`) REFERENCES `envios_pedidos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_envios_pedidos`
--

LOCK TABLES `eventos_envios_pedidos` WRITE;
/*!40000 ALTER TABLE `eventos_envios_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_envios_pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_integracion`
--

DROP TABLE IF EXISTS `eventos_integracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos_integracion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `integracion_id` bigint(20) unsigned NOT NULL,
  `correlacion_id` varchar(100) DEFAULT NULL,
  `direccion` enum('entrada','salida') NOT NULL,
  `estado` varchar(30) NOT NULL,
  `codigo_http` smallint(5) unsigned DEFAULT NULL,
  `resumen_solicitud` char(64) DEFAULT NULL,
  `resumen_respuesta` char(64) DEFAULT NULL,
  `detalle_seguro` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Solo datos saneados, sin secretos ni credenciales' CHECK (json_valid(`detalle_seguro`)),
  `intentos` smallint(5) unsigned NOT NULL DEFAULT 1,
  `iniciado_en` datetime(6) NOT NULL,
  `finalizado_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_eventos_integracion_estado_fecha` (`integracion_id`,`estado`,`iniciado_en`),
  KEY `ind_eventos_integracion_correlacion` (`correlacion_id`),
  CONSTRAINT `cf_eventos_integracion_integracion` FOREIGN KEY (`integracion_id`) REFERENCES `integraciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_integracion`
--

LOCK TABLES `eventos_integracion` WRITE;
/*!40000 ALTER TABLE `eventos_integracion` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_integracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `existencias_almacen`
--

DROP TABLE IF EXISTS `existencias_almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `existencias_almacen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `almacen_id` bigint(20) unsigned NOT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `variante_presente` tinyint(3) unsigned GENERATED ALWAYS AS (case when `variante_id` is null then 0 else 1 end) STORED,
  `variante_clave` int(10) unsigned GENERATED ALWAYS AS (ifnull(`variante_id`,0)) STORED,
  `cantidad_fisica` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `cantidad_reservada` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_existencias_almacen_variante` (`almacen_id`,`variante_id`),
  UNIQUE KEY `un_existencias_producto_modo` (`almacen_id`,`producto_id`,`variante_presente`,`variante_clave`),
  KEY `ind_existencias_variante_almacen` (`variante_id`,`almacen_id`),
  KEY `cf_existencias_almacen_sede` (`almacen_id`,`sede_id`),
  KEY `cf_existencias_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_existencias_variante_producto` (`variante_id`,`producto_id`),
  KEY `cf_existencias_producto_empresa` (`producto_id`,`empresa_id`),
  CONSTRAINT `cf_existencias_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_existencias_almacen_sede` FOREIGN KEY (`almacen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_existencias_producto_empresa` FOREIGN KEY (`producto_id`, `empresa_id`) REFERENCES `productos` (`id`, `empresa_id`),
  CONSTRAINT `cf_existencias_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_existencias_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `cf_existencias_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `ver_existencias_no_negativas` CHECK (`cantidad_fisica` >= 0 and `cantidad_reservada` >= 0 and `cantidad_reservada` <= `cantidad_fisica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `existencias_almacen`
--

LOCK TABLES `existencias_almacen` WRITE;
/*!40000 ALTER TABLE `existencias_almacen` DISABLE KEYS */;
/*!40000 ALTER TABLE `existencias_almacen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guias_remision`
--

DROP TABLE IF EXISTS `guias_remision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guias_remision` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `almacen_origen_id` bigint(20) unsigned NOT NULL,
  `venta_id` bigint(20) unsigned DEFAULT NULL,
  `serie` varchar(8) NOT NULL,
  `correlativo` bigint(20) unsigned NOT NULL,
  `motivo_traslado` varchar(80) NOT NULL,
  `modalidad_traslado` varchar(30) NOT NULL,
  `transportista_ruc` char(11) DEFAULT NULL,
  `transportista_nombre` varchar(180) DEFAULT NULL,
  `direccion_origen` varchar(255) NOT NULL,
  `direccion_destino` varchar(255) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `emitida_en` datetime(6) DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_guias_empresa_sede_numero` (`empresa_id`,`sede_id`,`serie`,`correlativo`),
  KEY `ind_guias_sede_estado_fecha` (`sede_id`,`estado`,`creado_en`),
  KEY `cf_guias_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_guias_almacen_sede` (`almacen_origen_id`,`sede_id`),
  KEY `cf_guias_usuario` (`creado_por_usuario_id`),
  KEY `cf_guias_venta_ambito` (`venta_id`,`empresa_id`,`sede_id`),
  CONSTRAINT `cf_guias_almacen` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_guias_almacen_sede` FOREIGN KEY (`almacen_origen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_guias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_guias_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_guias_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_guias_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_guias_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `cf_guias_venta_ambito` FOREIGN KEY (`venta_id`, `empresa_id`, `sede_id`) REFERENCES `ventas` (`id`, `empresa_id`, `sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guias_remision`
--

LOCK TABLES `guias_remision` WRITE;
/*!40000 ALTER TABLE `guias_remision` DISABLE KEYS */;
/*!40000 ALTER TABLE `guias_remision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_migraciones_origen`
--

DROP TABLE IF EXISTS `historial_migraciones_origen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial_migraciones_origen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `origen_tabla` enum('migraciones','migrations') NOT NULL,
  `origen_id` bigint(20) unsigned NOT NULL,
  `nombre_migracion` varchar(255) NOT NULL,
  `nombre_migracion_heredada` varchar(255) GENERATED ALWAYS AS (case when `origen_tabla` = 'migraciones' then `nombre_migracion` else NULL end) STORED,
  `lote` bigint(20) NOT NULL,
  `ejecutada_en` timestamp NULL DEFAULT NULL,
  `archivada_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_migracion_origen_id` (`origen_tabla`,`origen_id`),
  UNIQUE KEY `un_historial_migracion_heredada` (`nombre_migracion_heredada`),
  KEY `ind_migracion_origen_nombre` (`origen_tabla`,`nombre_migracion`),
  KEY `ind_migracion_nombre_lote` (`nombre_migracion`,`lote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_migraciones_origen`
--

LOCK TABLES `historial_migraciones_origen` WRITE;
/*!40000 ALTER TABLE `historial_migraciones_origen` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_migraciones_origen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagenes_variantes`
--

DROP TABLE IF EXISTS `imagenes_variantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imagenes_variantes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `variante_id` int(10) unsigned NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `imagen_principal` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ind_variante_imagenes_variante` (`variante_id`),
  CONSTRAINT `cf_variante_imagenes_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagenes_variantes`
--

LOCK TABLES `imagenes_variantes` WRITE;
/*!40000 ALTER TABLE `imagenes_variantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `imagenes_variantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integraciones`
--

DROP TABLE IF EXISTS `integraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `integraciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `proveedor` varchar(100) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 0,
  `referencia_secreto` varchar(255) DEFAULT NULL COMMENT 'Referencia a gestor externo; no guardar secretos ni tokens',
  `configuracion_publica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracion_publica`)),
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_integraciones_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `ind_integraciones_empresa_activa` (`empresa_id`,`activa`),
  CONSTRAINT `cf_integraciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integraciones`
--

LOCK TABLES `integraciones` WRITE;
/*!40000 ALTER TABLE `integraciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `integraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intentos_pago`
--

DROP TABLE IF EXISTS `intentos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `intentos_pago` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `pedido_id` int(10) unsigned NOT NULL,
  `medio_pago_id` bigint(20) unsigned NOT NULL,
  `pago_id` bigint(20) unsigned DEFAULT NULL,
  `proveedor` varchar(100) NOT NULL,
  `clave_idempotencia` char(64) NOT NULL,
  `referencia_externa` varchar(160) DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'iniciado',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `importe` decimal(15,2) NOT NULL,
  `codigo_respuesta` varchar(80) DEFAULT NULL,
  `detalle_seguro` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Solo contenido saneado; no PAN, CVV, tokens ni secretos' CHECK (json_valid(`detalle_seguro`)),
  `solicitado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `respondido_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_intentos_pago_idempotencia` (`empresa_id`,`clave_idempotencia`),
  UNIQUE KEY `un_intentos_pago_pago` (`pago_id`),
  KEY `ind_intentos_pago_pedido_estado` (`pedido_id`,`estado`,`solicitado_en`),
  KEY `ind_intentos_pago_proveedor_referencia` (`proveedor`,`referencia_externa`),
  KEY `cf_intentos_pago_medio` (`medio_pago_id`),
  CONSTRAINT `cf_intentos_pago_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_intentos_pago_medio` FOREIGN KEY (`medio_pago_id`) REFERENCES `medios_pago` (`id`),
  CONSTRAINT `cf_intentos_pago_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  CONSTRAINT `cf_intentos_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `ver_intentos_pago_importe` CHECK (`importe` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_pago`
--

LOCK TABLES `intentos_pago` WRITE;
/*!40000 ALTER TABLE `intentos_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `intentos_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lineas_asientos`
--

DROP TABLE IF EXISTS `lineas_asientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lineas_asientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asiento_id` bigint(20) unsigned NOT NULL,
  `cuenta_id` bigint(20) unsigned NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `debe` decimal(15,2) NOT NULL DEFAULT 0.00,
  `haber` decimal(15,2) NOT NULL DEFAULT 0.00,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  PRIMARY KEY (`id`),
  KEY `ind_asiento_lineas_asiento` (`asiento_id`),
  KEY `ind_asiento_lineas_cuenta` (`cuenta_id`,`asiento_id`),
  CONSTRAINT `cf_asiento_lineas_asiento` FOREIGN KEY (`asiento_id`) REFERENCES `asientos_contables` (`id`),
  CONSTRAINT `cf_asiento_lineas_cuenta` FOREIGN KEY (`cuenta_id`) REFERENCES `planes_cuentas` (`id`),
  CONSTRAINT `ver_asiento_lineas_debe_haber` CHECK (`debe` >= 0 and `haber` >= 0 and (`debe` <= 0 or `haber` <= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lineas_asientos`
--

LOCK TABLES `lineas_asientos` WRITE;
/*!40000 ALTER TABLE `lineas_asientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `lineas_asientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logistica_mayorista`
--

DROP TABLE IF EXISTS `logistica_mayorista`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logistica_mayorista` (
  `pedido_id` int(10) unsigned NOT NULL,
  `exportador` varchar(160) DEFAULT NULL,
  `direccion_exportador` varchar(255) DEFAULT NULL,
  `contacto_exportador` varchar(160) DEFAULT NULL,
  `direccion_consignatario` varchar(255) DEFAULT NULL,
  `contacto_consignatario` varchar(160) DEFAULT NULL,
  `factura_numero` varchar(80) DEFAULT NULL,
  `factura_fecha` date DEFAULT NULL,
  `orden_compra` varchar(80) DEFAULT NULL,
  `carta_credito` varchar(100) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `pais_origen` varchar(100) DEFAULT NULL,
  `lugar_carga` varchar(160) DEFAULT NULL,
  `puerto_descarga` varchar(160) DEFAULT NULL,
  `direccion_entrega` varchar(255) DEFAULT NULL,
  `descripcion_mercancia` text DEFAULT NULL,
  `codigo_hs` varchar(40) DEFAULT NULL,
  `cantidad_unidades` int(10) unsigned DEFAULT NULL,
  `bultos` varchar(160) DEFAULT NULL,
  `tipo_embalaje` varchar(100) DEFAULT NULL,
  `dimensiones` varchar(120) DEFAULT NULL,
  `volumen_m3` decimal(10,3) DEFAULT NULL,
  `peso_neto` decimal(10,2) DEFAULT NULL,
  `peso_bruto` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`pedido_id`),
  CONSTRAINT `cf_logistica_mayorista_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logistica_mayorista`
--

LOCK TABLES `logistica_mayorista` WRITE;
/*!40000 ALTER TABLE `logistica_mayorista` DISABLE KEYS */;
/*!40000 ALTER TABLE `logistica_mayorista` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes_trabajos`
--

DROP TABLE IF EXISTS `lotes_trabajos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lotes_trabajos` (
  `id` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cantidad_trabajos` int(11) NOT NULL,
  `cantidad_trabajos_pendientes` int(11) NOT NULL,
  `cantidad_trabajos_fallidos` int(11) NOT NULL,
  `identificadores_trabajos_fallidos` longtext NOT NULL,
  `opciones` mediumtext DEFAULT NULL,
  `cancelado_en` int(11) DEFAULT NULL,
  `creado_en` int(11) NOT NULL,
  `finalizado_en` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes_trabajos`
--

LOCK TABLES `lotes_trabajos` WRITE;
/*!40000 ALTER TABLE `lotes_trabajos` DISABLE KEYS */;
/*!40000 ALTER TABLE `lotes_trabajos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medios_pago`
--

DROP TABLE IF EXISTS `medios_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medios_pago` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `requiere_caja` tinyint(1) NOT NULL DEFAULT 0,
  `requiere_banco` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_medios_pago_codigo` (`codigo`),
  KEY `ind_medios_pago_activo` (`activo`,`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medios_pago`
--

LOCK TABLES `medios_pago` WRITE;
/*!40000 ALTER TABLE `medios_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `medios_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes_contacto`
--

DROP TABLE IF EXISTS `mensajes_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajes_contacto` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `contacto` varchar(160) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'Nuevo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ind_mensajes_contacto_estado_fecha` (`estado`,`creado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes_contacto`
--

LOCK TABLES `mensajes_contacto` WRITE;
/*!40000 ALTER TABLE `mensajes_contacto` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensajes_contacto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `contexto` varchar(40) NOT NULL DEFAULT 'sidebar',
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_menus_codigo` (`codigo`),
  KEY `ind_menus_contexto_habilitado` (`contexto`,`habilitado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migraciones_laravel`
--

DROP TABLE IF EXISTS `migraciones_laravel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migraciones_laravel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migracion` varchar(255) NOT NULL,
  `lote` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migraciones_laravel`
--

LOCK TABLES `migraciones_laravel` WRITE;
/*!40000 ALTER TABLE `migraciones_laravel` DISABLE KEYS */;
/*!40000 ALTER TABLE `migraciones_laravel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulos`
--

DROP TABLE IF EXISTS `modulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modulos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(10) unsigned NOT NULL DEFAULT 0,
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_modulos_codigo` (`codigo`),
  KEY `ind_modulos_habilitado_orden` (`habilitado`,`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulos`
--

LOCK TABLES `modulos` WRITE;
/*!40000 ALTER TABLE `modulos` DISABLE KEYS */;
/*!40000 ALTER TABLE `modulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_bancarios`
--

DROP TABLE IF EXISTS `movimientos_bancarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_bancarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cuenta_bancaria_id` bigint(20) unsigned NOT NULL,
  `pago_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` varchar(40) NOT NULL,
  `importe` decimal(15,2) NOT NULL,
  `referencia` varchar(120) DEFAULT NULL,
  `ocurrido_en` datetime(6) NOT NULL,
  `conciliado` tinyint(1) NOT NULL DEFAULT 0,
  `conciliado_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_movimientos_bancarios_cuenta_fecha` (`cuenta_bancaria_id`,`ocurrido_en`),
  KEY `ind_movimientos_bancarios_conciliacion` (`cuenta_bancaria_id`,`conciliado`,`ocurrido_en`),
  KEY `cf_movimientos_bancarios_pago` (`pago_id`),
  CONSTRAINT `cf_movimientos_bancarios_cuenta` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`),
  CONSTRAINT `cf_movimientos_bancarios_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  CONSTRAINT `ver_movimientos_bancarios_importe` CHECK (`importe` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_bancarios`
--

LOCK TABLES `movimientos_bancarios` WRITE;
/*!40000 ALTER TABLE `movimientos_bancarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_bancarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_caja`
--

DROP TABLE IF EXISTS `movimientos_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_caja` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sesion_caja_id` bigint(20) unsigned NOT NULL,
  `pago_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` varchar(40) NOT NULL,
  `direccion` enum('entrada','salida') NOT NULL,
  `importe` decimal(15,2) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  KEY `ind_movimientos_caja_sesion_fecha` (`sesion_caja_id`,`creado_en`),
  KEY `ind_movimientos_caja_pago` (`pago_id`),
  KEY `cf_movimientos_caja_usuario` (`creado_por_usuario_id`),
  CONSTRAINT `cf_movimientos_caja_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  CONSTRAINT `cf_movimientos_caja_sesion` FOREIGN KEY (`sesion_caja_id`) REFERENCES `sesiones_caja` (`id`),
  CONSTRAINT `cf_movimientos_caja_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_movimientos_caja_importe` CHECK (`importe` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_caja`
--

LOCK TABLES `movimientos_caja` WRITE;
/*!40000 ALTER TABLE `movimientos_caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_dispositivos`
--

DROP TABLE IF EXISTS `movimientos_dispositivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_dispositivos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dispositivo_id` bigint(20) unsigned NOT NULL,
  `movimiento_inventario_id` bigint(20) unsigned NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_dispositivo_movimiento` (`movimiento_inventario_id`,`dispositivo_id`),
  KEY `ind_dispositivo_movimientos_fecha` (`dispositivo_id`,`creado_en`),
  CONSTRAINT `cf_dispositivo_movimiento_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`),
  CONSTRAINT `cf_dispositivo_movimiento_existencias` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimientos_inventario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_dispositivos`
--

LOCK TABLES `movimientos_dispositivos` WRITE;
/*!40000 ALTER TABLE `movimientos_dispositivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_dispositivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_inventario` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `tipo_movimiento` varchar(30) NOT NULL,
  `cantidad` decimal(18,6) NOT NULL COMMENT 'Entrada y salida como magnitud; ajuste como delta firmado',
  `notas` varchar(500) NOT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `almacen_id` bigint(20) unsigned DEFAULT NULL,
  `variante_id` int(10) unsigned DEFAULT NULL,
  `documento_tipo` varchar(60) DEFAULT NULL,
  `documento_id` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_movimientos_producto_fecha` (`producto_id`,`creado_en`),
  KEY `ind_movimientos_usuario` (`usuario_id`),
  KEY `ind_movimientos_sede_fecha` (`sede_id`,`creado_en`),
  KEY `ind_movimientos_almacen_variante_fecha` (`almacen_id`,`variante_id`,`creado_en`),
  KEY `ind_movimientos_documento` (`documento_tipo`,`documento_id`),
  KEY `ind_movimientos_variante` (`variante_id`),
  KEY `cf_movimientos_empresa` (`empresa_id`),
  KEY `cf_movimientos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_movimientos_almacen_sede` (`almacen_id`,`sede_id`),
  KEY `cf_movimientos_producto_empresa` (`producto_id`,`empresa_id`),
  KEY `cf_movimientos_inventario_variante_producto` (`variante_id`,`producto_id`),
  CONSTRAINT `cf_movimientos_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_movimientos_almacen_sede` FOREIGN KEY (`almacen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_movimientos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_movimientos_inventario_variante_producto` FOREIGN KEY (`variante_id`, `producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  CONSTRAINT `cf_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `cf_movimientos_producto_empresa` FOREIGN KEY (`producto_id`, `empresa_id`) REFERENCES `productos` (`id`, `empresa_id`),
  CONSTRAINT `cf_movimientos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_movimientos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_movimientos_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  CONSTRAINT `ver_movimientos_ambito` CHECK ((`sede_id` is null or `empresa_id` is not null) and (`almacen_id` is null or `sede_id` is not null))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id` char(36) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `tipo_notificable` varchar(255) NOT NULL,
  `notificable_id` bigint(20) unsigned NOT NULL,
  `datos` text NOT NULL,
  `leido_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_notificaciones_notificable` (`tipo_notificable`,`notificable_id`),
  KEY `ind_notificaciones_lectura` (`tipo_notificable`,`notificable_id`,`leido_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(60) NOT NULL,
  `tipo` enum('cobro','pago','reembolso') NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'registrado',
  `medio_pago_id` bigint(20) unsigned NOT NULL,
  `sesion_caja_id` bigint(20) unsigned DEFAULT NULL,
  `cuenta_bancaria_id` bigint(20) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `pedido_id` int(10) unsigned DEFAULT NULL,
  `venta_id` bigint(20) unsigned DEFAULT NULL,
  `compra_id` int(10) unsigned DEFAULT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `importe` decimal(15,2) NOT NULL,
  `referencia` varchar(120) DEFAULT NULL,
  `fecha_pago` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_pagos_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `ind_pagos_sede_tipo_estado_fecha` (`sede_id`,`tipo`,`estado`,`fecha_pago`),
  KEY `ind_pagos_medio_fecha` (`medio_pago_id`,`fecha_pago`),
  KEY `ind_pagos_pedido_fecha` (`pedido_id`,`fecha_pago`),
  KEY `ind_pagos_venta_fecha` (`venta_id`,`fecha_pago`),
  KEY `ind_pagos_compra_fecha` (`compra_id`,`fecha_pago`),
  KEY `cf_pagos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_pagos_sesion_caja` (`sesion_caja_id`),
  KEY `cf_pagos_usuario` (`usuario_id`),
  KEY `cf_pagos_venta_ambito` (`venta_id`,`empresa_id`,`sede_id`),
  KEY `cf_pagos_pedido_ambito` (`pedido_id`,`empresa_id`,`sede_id`),
  KEY `cf_pagos_compra_ambito` (`compra_id`,`empresa_id`,`sede_id`),
  KEY `cf_pagos_banco_empresa` (`cuenta_bancaria_id`,`empresa_id`),
  CONSTRAINT `cf_pagos_banco_empresa` FOREIGN KEY (`cuenta_bancaria_id`, `empresa_id`) REFERENCES `cuentas_bancarias` (`id`, `empresa_id`),
  CONSTRAINT `cf_pagos_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `cf_pagos_compra_ambito` FOREIGN KEY (`compra_id`, `empresa_id`, `sede_id`) REFERENCES `compras` (`id`, `empresa_id`, `sede_id`),
  CONSTRAINT `cf_pagos_cuenta_bancaria` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`),
  CONSTRAINT `cf_pagos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_pagos_medio` FOREIGN KEY (`medio_pago_id`) REFERENCES `medios_pago` (`id`),
  CONSTRAINT `cf_pagos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `cf_pagos_pedido_ambito` FOREIGN KEY (`pedido_id`, `empresa_id`, `sede_id`) REFERENCES `pedidos` (`id`, `empresa_id`, `sede_id`),
  CONSTRAINT `cf_pagos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_pagos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_pagos_sesion_caja` FOREIGN KEY (`sesion_caja_id`) REFERENCES `sesiones_caja` (`id`),
  CONSTRAINT `cf_pagos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_pagos_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `cf_pagos_venta_ambito` FOREIGN KEY (`venta_id`, `empresa_id`, `sede_id`) REFERENCES `ventas` (`id`, `empresa_id`, `sede_id`),
  CONSTRAINT `ver_pagos_importe` CHECK (`importe` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pantallas`
--

DROP TABLE IF EXISTS `pantallas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pantallas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `modulo_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(120) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `ruta` varchar(160) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `habilitada` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_pantallas_codigo` (`codigo`),
  UNIQUE KEY `un_pantallas_id_modulo` (`id`,`modulo_id`),
  KEY `ind_pantallas_modulo_habilitada` (`modulo_id`,`habilitada`),
  CONSTRAINT `cf_pantallas_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pantallas`
--

LOCK TABLES `pantallas` WRITE;
/*!40000 ALTER TABLE `pantallas` DISABLE KEYS */;
/*!40000 ALTER TABLE `pantallas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cupon_codigo` varchar(50) DEFAULT NULL,
  `estado` varchar(40) NOT NULL DEFAULT 'Pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `cotizacion_id` int(10) unsigned DEFAULT NULL,
  `canal` varchar(30) DEFAULT NULL COMMENT 'Canal no confirmado cuando es NULL',
  `tipo_venta` varchar(30) DEFAULT NULL COMMENT 'Propuesta A21, pendiente validar semantica de canal',
  `observaciones` varchar(500) DEFAULT NULL COMMENT 'Propuesta A21, sin importacion historica implicita',
  `cupon_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_entrega_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_facturacion_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_entrega_historica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`direccion_entrega_historica`)),
  `direccion_facturacion_historica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`direccion_facturacion_historica`)),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `almacen_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_pedidos_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
  KEY `ind_pedidos_usuario_fecha` (`usuario_id`,`creado_en`),
  KEY `ind_pedidos_estado_fecha` (`estado`,`creado_en`),
  KEY `ind_pedidos_cliente_fecha` (`cliente_id`,`creado_en`),
  KEY `ind_pedidos_empresa_fecha` (`empresa_id`,`creado_en`),
  KEY `ind_pedidos_sede_estado_fecha` (`sede_id`,`estado`,`creado_en`),
  KEY `ind_pedidos_almacen` (`almacen_id`),
  KEY `cf_pedidos_cotizacion` (`cotizacion_id`),
  KEY `cf_pedidos_cupon` (`cupon_id`),
  KEY `cf_pedidos_direccion_entrega` (`cliente_id`,`direccion_entrega_id`),
  KEY `cf_pedidos_direccion_facturacion` (`cliente_id`,`direccion_facturacion_id`),
  KEY `cf_pedidos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_pedidos_almacen_sede` (`almacen_id`,`sede_id`),
  KEY `cf_pedidos_cliente_empresa` (`cliente_id`,`empresa_id`),
  CONSTRAINT `cf_pedidos_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_pedidos_almacen_sede` FOREIGN KEY (`almacen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_pedidos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `cf_pedidos_cliente_empresa` FOREIGN KEY (`cliente_id`, `empresa_id`) REFERENCES `clientes` (`id`, `empresa_id`),
  CONSTRAINT `cf_pedidos_cotizacion` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  CONSTRAINT `cf_pedidos_cupon` FOREIGN KEY (`cupon_id`) REFERENCES `cupones` (`id`),
  CONSTRAINT `cf_pedidos_direccion_entrega` FOREIGN KEY (`cliente_id`, `direccion_entrega_id`) REFERENCES `direcciones_cliente` (`cliente_id`, `id`),
  CONSTRAINT `cf_pedidos_direccion_facturacion` FOREIGN KEY (`cliente_id`, `direccion_facturacion_id`) REFERENCES `direcciones_cliente` (`cliente_id`, `id`),
  CONSTRAINT `cf_pedidos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_pedidos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_pedidos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ver_pedidos_importes` CHECK (`total` >= 0 and `subtotal` >= 0 and `descuento` >= 0),
  CONSTRAINT `ver_pedidos_direcciones` CHECK ((`direccion_entrega_id` is null or `cliente_id` is not null) and (`direccion_facturacion_id` is null or `cliente_id` is not null)),
  CONSTRAINT `ver_pedidos_ambito` CHECK ((`sede_id` is null or `empresa_id` is not null) and (`almacen_id` is null or `sede_id` is not null))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfiles_inteligentes_productos`
--

DROP TABLE IF EXISTS `perfiles_inteligentes_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perfiles_inteligentes_productos` (
  `producto_id` int(10) unsigned NOT NULL,
  `puntuacion_rendimiento` tinyint(3) unsigned NOT NULL DEFAULT 70,
  `puntuacion_camara` tinyint(3) unsigned NOT NULL DEFAULT 70,
  `puntuacion_bateria` tinyint(3) unsigned NOT NULL DEFAULT 70,
  `puntuacion_valor` tinyint(3) unsigned NOT NULL DEFAULT 70,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`producto_id`),
  CONSTRAINT `cf_perfil_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ver_perfil_puntuaciones` CHECK (`puntuacion_rendimiento` <= 100 and `puntuacion_camara` <= 100 and `puntuacion_bateria` <= 100 and `puntuacion_valor` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfiles_inteligentes_productos`
--

LOCK TABLES `perfiles_inteligentes_productos` WRITE;
/*!40000 ALTER TABLE `perfiles_inteligentes_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `perfiles_inteligentes_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permisos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `modulo_id` bigint(20) unsigned NOT NULL,
  `pantalla_id` bigint(20) unsigned DEFAULT NULL,
  `codigo` varchar(160) NOT NULL,
  `accion` varchar(60) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `es_sistema` tinyint(1) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_permisos_codigo` (`codigo`),
  UNIQUE KEY `un_permisos_id_pantalla` (`id`,`pantalla_id`),
  KEY `ind_permisos_modulo_accion_activo` (`modulo_id`,`accion`,`activo`),
  KEY `ind_permisos_pantalla` (`pantalla_id`),
  KEY `cf_permisos_pantalla_modulo` (`pantalla_id`,`modulo_id`),
  CONSTRAINT `cf_permisos_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`),
  CONSTRAINT `cf_permisos_pantalla` FOREIGN KEY (`pantalla_id`) REFERENCES `pantallas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_permisos_pantalla_modulo` FOREIGN KEY (`pantalla_id`, `modulo_id`) REFERENCES `pantallas` (`id`, `modulo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_permisos_sistema_proteger_identidad BEFORE UPDATE ON permisos FOR EACH ROW
BEGIN
  IF OLD.es_sistema = TRUE AND (NEW.id <> OLD.id OR BINARY NEW.codigo <> BINARY OLD.codigo OR NEW.modulo_id <> OLD.modulo_id OR NOT (NEW.pantalla_id <=> OLD.pantalla_id) OR BINARY NEW.accion <> BINARY OLD.accion OR NEW.es_sistema <> OLD.es_sistema) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede cambiar la identidad de un permiso protegido';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_permisos_sistema_impedir_eliminacion BEFORE DELETE ON permisos FOR EACH ROW
BEGIN
  IF OLD.es_sistema = TRUE THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede borrar un permiso protegido';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `planes_cuentas`
--

DROP TABLE IF EXISTS `planes_cuentas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `planes_cuentas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `cuenta_padre_id` bigint(20) unsigned DEFAULT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `acepta_movimientos` tinyint(1) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_plan_cuentas_empresa_codigo` (`empresa_id`,`codigo`),
  UNIQUE KEY `un_plan_cuentas_id_empresa` (`id`,`empresa_id`),
  KEY `ind_plan_cuentas_padre` (`cuenta_padre_id`),
  KEY `cf_plan_cuentas_padre_empresa` (`cuenta_padre_id`,`empresa_id`),
  CONSTRAINT `cf_plan_cuentas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_plan_cuentas_padre` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `planes_cuentas` (`id`),
  CONSTRAINT `cf_plan_cuentas_padre_empresa` FOREIGN KEY (`cuenta_padre_id`, `empresa_id`) REFERENCES `planes_cuentas` (`id`, `empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planes_cuentas`
--

LOCK TABLES `planes_cuentas` WRITE;
/*!40000 ALTER TABLE `planes_cuentas` DISABLE KEYS */;
/*!40000 ALTER TABLE `planes_cuentas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `marca` varchar(80) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `categoria_id` int(10) unsigned NOT NULL,
  `categoria` varchar(120) NOT NULL COMMENT 'Campo legado; retirar solo tras conciliación aprobada',
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_original` decimal(10,2) DEFAULT NULL,
  `descuento` varchar(20) DEFAULT NULL,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `url_imagen` varchar(255) DEFAULT NULL,
  `existencias` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Proyección heredada, no fuente del kardex',
  `almacenamiento` varchar(80) DEFAULT NULL,
  `color` varchar(80) DEFAULT NULL,
  `etiqueta` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_productos_id_empresa` (`id`,`empresa_id`),
  KEY `ind_productos_marca` (`marca`),
  KEY `ind_productos_nombre` (`nombre`),
  KEY `ind_productos_activo` (`activo`),
  KEY `ind_productos_catalogo` (`activo`,`categoria_id`,`precio`),
  KEY `ind_productos_categoria` (`categoria_id`),
  KEY `ind_productos_empresa` (`empresa_id`),
  KEY `cf_productos_categoria_empresa` (`categoria_id`,`empresa_id`),
  KEY `ind_productos_catalogo_heredado` (`activo`,`categoria`,`marca`,`id`),
  CONSTRAINT `cf_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `cf_productos_categoria_empresa` FOREIGN KEY (`categoria_id`, `empresa_id`) REFERENCES `categorias` (`id`, `empresa_id`),
  CONSTRAINT `cf_productos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `ver_productos_precios` CHECK (`precio` >= 0 and (`precio_original` is null or `precio_original` >= 0) and (`precio_oferta` is null or `precio_oferta` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_proveedores`
--

DROP TABLE IF EXISTS `productos_proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos_proveedores` (
  `producto_id` int(10) unsigned NOT NULL,
  `proveedor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`producto_id`,`proveedor_id`),
  KEY `ind_producto_proveedor_proveedor` (`proveedor_id`),
  CONSTRAINT `cf_producto_proveedor_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cf_producto_proveedor_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_proveedores`
--

LOCK TABLES `productos_proveedores` WRITE;
/*!40000 ALTER TABLE `productos_proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos_proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(160) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_proveedores_ruc` (`ruc`),
  UNIQUE KEY `un_proveedores_id_empresa` (`id`,`empresa_id`),
  KEY `ind_proveedores_activo_nombre` (`activo`,`nombre`),
  KEY `ind_proveedores_empresa` (`empresa_id`),
  CONSTRAINT `cf_proveedores_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reembolsos`
--

DROP TABLE IF EXISTS `reembolsos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reembolsos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `devolucion_id` int(10) unsigned NOT NULL,
  `pago_id` bigint(20) unsigned NOT NULL,
  `importe` decimal(15,2) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `autorizado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_reembolsos_pago` (`pago_id`),
  KEY `ind_reembolsos_devolucion_estado` (`devolucion_id`,`estado`),
  KEY `cf_reembolsos_autorizador` (`autorizado_por_usuario_id`),
  CONSTRAINT `cf_reembolsos_autorizador` FOREIGN KEY (`autorizado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_reembolsos_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones_garantias` (`id`),
  CONSTRAINT `cf_reembolsos_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  CONSTRAINT `ver_reembolsos_importe` CHECK (`importe` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reembolsos`
--

LOCK TABLES `reembolsos` WRITE;
/*!40000 ALTER TABLE `reembolsos` DISABLE KEYS */;
/*!40000 ALTER TABLE `reembolsos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referencias_imagenes_productos`
--

DROP TABLE IF EXISTS `referencias_imagenes_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referencias_imagenes_productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `actualizado_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_producto_imagen_ruta` (`producto_id`,`ruta_imagen`),
  KEY `ind_producto_imagen_orden` (`producto_id`,`orden`,`id`),
  CONSTRAINT `cf_producto_imagen_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referencias_imagenes_productos`
--

LOCK TABLES `referencias_imagenes_productos` WRITE;
/*!40000 ALTER TABLE `referencias_imagenes_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `referencias_imagenes_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registros_auditoria`
--

DROP TABLE IF EXISTS `registros_auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registros_auditoria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `entidad` varchar(80) NOT NULL,
  `entidad_id` bigint(20) DEFAULT NULL COMMENT 'Identificador numerico legado; el identificador textual admite otros formatos',
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_nuevos`)),
  `direccion_ip` varchar(45) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `identificador_universal_evento` char(36) DEFAULT NULL,
  `rol_codigo` varchar(40) DEFAULT NULL,
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `sede_id` bigint(20) unsigned DEFAULT NULL,
  `modulo` varchar(80) DEFAULT NULL,
  `resultado` varchar(20) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `correlacion_id` varchar(64) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `ocurrido_en` datetime(6) DEFAULT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `identificador_entidad` varchar(128) DEFAULT NULL,
  `cambios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cambios`)),
  `origen` varchar(20) NOT NULL DEFAULT 'laravel',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_auditoria_evento_identificador_universal` (`identificador_universal_evento`),
  KEY `ind_auditoria_usuario_fecha` (`usuario_id`,`creado_en`),
  KEY `ind_auditoria_entidad` (`entidad`,`entidad_id`),
  KEY `ind_auditoria_ocurrido` (`ocurrido_en`),
  KEY `ind_auditoria_tabla_entidad_fecha` (`tabla_afectada`,`identificador_entidad`,`ocurrido_en`),
  KEY `ind_auditoria_sede_fecha` (`sede_id`,`ocurrido_en`),
  KEY `ind_auditoria_rol` (`rol_codigo`),
  KEY `ind_auditoria_modulo` (`modulo`),
  KEY `ind_auditoria_resultado` (`resultado`),
  KEY `ind_auditoria_correlacion` (`correlacion_id`),
  KEY `cf_auditoria_empresa` (`empresa_id`),
  KEY `cf_auditoria_sede_empresa` (`sede_id`,`empresa_id`),
  CONSTRAINT `cf_auditoria_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_auditoria_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_auditoria_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ver_auditoria_ambito` CHECK (`sede_id` is null or `empresa_id` is not null)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registros_auditoria`
--

LOCK TABLES `registros_auditoria` WRITE;
/*!40000 ALTER TABLE `registros_auditoria` DISABLE KEYS */;
INSERT INTO `registros_auditoria` VALUES (1,NULL,'inicio_sesion_fallido','autenticacion',NULL,NULL,NULL,'::1','2026-11-04 01:26:46','e898fdd0-f31a-4e0e-be15-591086826dd5',NULL,NULL,NULL,NULL,'fallo','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36',NULL,NULL,'2026-11-03 20:26:46.000000','autenticacion',NULL,NULL,'laravel'),(2,NULL,'inicio_sesion_fallido','autenticacion',NULL,NULL,NULL,'::1','2026-11-04 01:35:50','3baa2bde-f5e5-466b-aa44-aaacc913b436',NULL,NULL,NULL,NULL,'fallo','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36',NULL,NULL,'2026-11-03 20:35:50.000000','autenticacion',NULL,NULL,'laravel');
/*!40000 ALTER TABLE `registros_auditoria` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_auditoria_impedir_actualizacion BEFORE UPDATE ON registros_auditoria
FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'registros_auditoria es solo de insercion' */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_auditoria_impedir_eliminacion BEFORE DELETE ON registros_auditoria
FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'registros_auditoria es solo de insercion' */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `alcance_permitido` varchar(20) NOT NULL DEFAULT 'sede',
  `es_sistema` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_roles_codigo` (`codigo`),
  UNIQUE KEY `un_roles_id_alcance` (`id`,`alcance_permitido`),
  KEY `ind_roles_activo_sistema` (`activo`,`es_sistema`),
  CONSTRAINT `ver_roles_alcance` CHECK (`alcance_permitido` in ('empresa','sede','propio')),
  CONSTRAINT `ver_rol_administrador_global` CHECK (`codigo` <> 'administrador' or `alcance_permitido` = 'empresa' and `es_sistema` = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_roles_sistema_proteger_identidad BEFORE UPDATE ON roles FOR EACH ROW
BEGIN
  IF OLD.es_sistema = TRUE AND (NEW.id <> OLD.id OR BINARY NEW.codigo <> BINARY OLD.codigo OR NEW.alcance_permitido <> OLD.alcance_permitido OR NEW.es_sistema <> OLD.es_sistema) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede cambiar la identidad de un rol protegido';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_roles_sistema_impedir_eliminacion BEFORE DELETE ON roles FOR EACH ROW
BEGIN
  IF OLD.es_sistema = TRUE THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede borrar un rol protegido';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `roles_menus`
--

DROP TABLE IF EXISTS `roles_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_menus` (
  `rol_id` bigint(20) unsigned NOT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`rol_id`,`menu_id`),
  KEY `ind_roles_menus_menu_rol` (`menu_id`,`rol_id`),
  CONSTRAINT `cf_roles_menus_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  CONSTRAINT `cf_roles_menus_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_menus`
--

LOCK TABLES `roles_menus` WRITE;
/*!40000 ALTER TABLE `roles_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_permisos`
--

DROP TABLE IF EXISTS `roles_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_permisos` (
  `rol_id` bigint(20) unsigned NOT NULL,
  `permiso_id` bigint(20) unsigned NOT NULL,
  `alcance` varchar(20) NOT NULL DEFAULT 'sede',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`rol_id`,`permiso_id`,`alcance`),
  KEY `ind_rol_permiso_permiso_rol` (`permiso_id`,`rol_id`),
  KEY `cf_rol_permiso_alcance` (`rol_id`,`alcance`),
  CONSTRAINT `cf_rol_permiso_alcance` FOREIGN KEY (`rol_id`, `alcance`) REFERENCES `roles` (`id`, `alcance_permitido`),
  CONSTRAINT `cf_rol_permiso_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`),
  CONSTRAINT `cf_rol_permiso_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `ver_rol_permiso_alcance` CHECK (`alcance` in ('empresa','sede','propio'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permisos`
--

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles_permisos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_permisos_administrador_impedir_actualizacion BEFORE UPDATE ON roles_permisos FOR EACH ROW
BEGIN
  IF EXISTS (SELECT 1 FROM roles WHERE id IN (OLD.rol_id, NEW.rol_id) AND codigo = 'administrador') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede modificar un permiso del rol administrador';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`Rolando`@`localhost`*/ /*!50003 TRIGGER disparador_permisos_administrador_impedir_eliminacion BEFORE DELETE ON roles_permisos FOR EACH ROW
BEGIN
  IF EXISTS (SELECT 1 FROM roles WHERE id = OLD.rol_id AND codigo = 'administrador') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede retirar un permiso del rol administrador';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sedes`
--

DROP TABLE IF EXISTS `sedes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sedes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `zona_horaria` varchar(64) NOT NULL DEFAULT 'America/Lima',
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_sedes_empresa_codigo` (`empresa_id`,`codigo`),
  UNIQUE KEY `un_sedes_id_empresa` (`id`,`empresa_id`),
  KEY `ind_sedes_empresa_activa` (`empresa_id`,`activa`),
  CONSTRAINT `cf_sedes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sedes`
--

LOCK TABLES `sedes` WRITE;
/*!40000 ALTER TABLE `sedes` DISABLE KEYS */;
/*!40000 ALTER TABLE `sedes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `segmentos_clientes`
--

DROP TABLE IF EXISTS `segmentos_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `segmentos_clientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `criterios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`criterios`)),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_segmentos_nombre` (`nombre`),
  KEY `ind_segmentos_activo` (`activo`),
  CONSTRAINT `ver_segmentos_criterios` CHECK (json_valid(`criterios`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `segmentos_clientes`
--

LOCK TABLES `segmentos_clientes` WRITE;
/*!40000 ALTER TABLE `segmentos_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `segmentos_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `series_documentos`
--

DROP TABLE IF EXISTS `series_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series_documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `tipo_documento_id` char(2) NOT NULL,
  `serie` varchar(8) NOT NULL,
  `siguiente_correlativo` bigint(20) unsigned NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_series_documento` (`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`),
  UNIQUE KEY `un_series_documento_contexto` (`id`,`empresa_id`,`sede_id`),
  UNIQUE KEY `un_series_contexto_tipo_serie` (`id`,`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`),
  KEY `ind_series_documento_activa` (`sede_id`,`activa`,`tipo_documento_id`),
  KEY `cf_series_documentos_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_series_documentos_tipo` (`tipo_documento_id`),
  CONSTRAINT `cf_series_documentos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_series_documentos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_series_documentos_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_series_documentos_tipo` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento_electronico` (`codigo`),
  CONSTRAINT `ver_series_correlativo` CHECK (`siguiente_correlativo` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series_documentos`
--

LOCK TABLES `series_documentos` WRITE;
/*!40000 ALTER TABLE `series_documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `series_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_aplicacion`
--

DROP TABLE IF EXISTS `sesiones_aplicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_aplicacion` (
  `id` varchar(255) NOT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `contenido` longtext NOT NULL COMMENT 'No serializar secretos; proteger el payload en la capa de sesión',
  `ultima_actividad` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_sesiones_aplicacion_usuario` (`usuario_id`),
  KEY `ind_sesiones_aplicacion_actividad` (`ultima_actividad`),
  CONSTRAINT `cf_sesiones_aplicacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_aplicacion`
--

LOCK TABLES `sesiones_aplicacion` WRITE;
/*!40000 ALTER TABLE `sesiones_aplicacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_aplicacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_caja`
--

DROP TABLE IF EXISTS `sesiones_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_caja` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `caja_id` bigint(20) unsigned NOT NULL,
  `usuario_apertura_id` bigint(20) unsigned NOT NULL,
  `usuario_cierre_id` bigint(20) unsigned DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'abierta',
  `saldo_inicial` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo_cierre_declarado` decimal(15,2) DEFAULT NULL,
  `abierta_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `cerrada_en` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_sesiones_caja_estado_fecha` (`caja_id`,`estado`,`abierta_en`),
  KEY `ind_sesiones_caja_usuario_fecha` (`usuario_apertura_id`,`abierta_en`),
  KEY `cf_sesiones_caja_cierre` (`usuario_cierre_id`),
  CONSTRAINT `cf_sesiones_caja_apertura` FOREIGN KEY (`usuario_apertura_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `cf_sesiones_caja_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`),
  CONSTRAINT `cf_sesiones_caja_cierre` FOREIGN KEY (`usuario_cierre_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_sesiones_caja_saldos` CHECK (`saldo_inicial` >= 0 and (`saldo_cierre_declarado` is null or `saldo_cierre_declarado` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_caja`
--

LOCK TABLES `sesiones_caja` WRITE;
/*!40000 ALTER TABLE `sesiones_caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_usuarios`
--

DROP TABLE IF EXISTS `sesiones_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `resumen_identificador` char(64) NOT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `ultimo_acceso` timestamp NOT NULL DEFAULT current_timestamp(),
  `revocada_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_sesiones_usuario_resumen` (`resumen_identificador`),
  KEY `ind_sesiones_usuario_actividad` (`usuario_id`,`revocada_en`,`ultimo_acceso`),
  CONSTRAINT `cf_sesiones_usuario_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_usuarios`
--

LOCK TABLES `sesiones_usuarios` WRITE;
/*!40000 ALTER TABLE `sesiones_usuarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_documento_electronico`
--

DROP TABLE IF EXISTS `tipos_documento_electronico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_documento_electronico` (
  `codigo` char(2) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `requiere_documento_referencia` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo`),
  KEY `ind_tipos_documento_activo` (`activo`,`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_documento_electronico`
--

LOCK TABLES `tipos_documento_electronico` WRITE;
/*!40000 ALTER TABLE `tipos_documento_electronico` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipos_documento_electronico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tokens_recuperacion_contrasena`
--

DROP TABLE IF EXISTS `tokens_recuperacion_contrasena`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens_recuperacion_contrasena` (
  `correo` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL COMMENT 'Hash emitido por el repositorio de tokens',
  `creado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tokens_recuperacion_contrasena`
--

LOCK TABLES `tokens_recuperacion_contrasena` WRITE;
/*!40000 ALTER TABLE `tokens_recuperacion_contrasena` DISABLE KEYS */;
/*!40000 ALTER TABLE `tokens_recuperacion_contrasena` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajos_fallidos`
--

DROP TABLE IF EXISTS `trabajos_fallidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabajos_fallidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identificador_universal` varchar(255) NOT NULL,
  `conexion` varchar(255) NOT NULL,
  `cola` varchar(255) NOT NULL,
  `contenido` longtext NOT NULL,
  `excepcion` longtext NOT NULL,
  `fallido_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_trabajos_fallidos_identificador_universal` (`identificador_universal`),
  KEY `ind_trabajos_fallidos_conexion_cola_fecha` (`conexion`,`cola`,`fallido_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajos_fallidos`
--

LOCK TABLES `trabajos_fallidos` WRITE;
/*!40000 ALTER TABLE `trabajos_fallidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `trabajos_fallidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajos_pendientes`
--

DROP TABLE IF EXISTS `trabajos_pendientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabajos_pendientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cola` varchar(255) NOT NULL,
  `contenido` longtext NOT NULL,
  `intentos` smallint(5) unsigned NOT NULL,
  `reservado_en` int(10) unsigned DEFAULT NULL,
  `disponible_en` int(10) unsigned NOT NULL,
  `creado_en` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_trabajos_cola` (`cola`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajos_pendientes`
--

LOCK TABLES `trabajos_pendientes` WRITE;
/*!40000 ALTER TABLE `trabajos_pendientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `trabajos_pendientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transferencias_existencias`
--

DROP TABLE IF EXISTS `transferencias_existencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transferencias_existencias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_origen_id` bigint(20) unsigned NOT NULL,
  `almacen_origen_id` bigint(20) unsigned NOT NULL,
  `sede_destino_id` bigint(20) unsigned NOT NULL,
  `almacen_destino_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `solicitado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `aprobado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `enviado_en` datetime(6) DEFAULT NULL,
  `recibido_en` datetime(6) DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_transferencias_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `ind_transferencias_origen_estado` (`sede_origen_id`,`estado`,`creado_en`),
  KEY `ind_transferencias_destino_estado` (`sede_destino_id`,`estado`,`creado_en`),
  KEY `cf_transferencias_sede_origen_empresa` (`sede_origen_id`,`empresa_id`),
  KEY `cf_transferencias_almacen_origen_sede` (`almacen_origen_id`,`sede_origen_id`),
  KEY `cf_transferencias_sede_destino_empresa` (`sede_destino_id`,`empresa_id`),
  KEY `cf_transferencias_almacen_destino_sede` (`almacen_destino_id`,`sede_destino_id`),
  KEY `cf_transferencias_solicitante` (`solicitado_por_usuario_id`),
  KEY `cf_transferencias_aprobador` (`aprobado_por_usuario_id`),
  CONSTRAINT `cf_transferencias_almacen_destino` FOREIGN KEY (`almacen_destino_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_transferencias_almacen_destino_sede` FOREIGN KEY (`almacen_destino_id`, `sede_destino_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_transferencias_almacen_origen` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_transferencias_almacen_origen_sede` FOREIGN KEY (`almacen_origen_id`, `sede_origen_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_transferencias_aprobador` FOREIGN KEY (`aprobado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_transferencias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_transferencias_sede_destino` FOREIGN KEY (`sede_destino_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_transferencias_sede_destino_empresa` FOREIGN KEY (`sede_destino_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_transferencias_sede_origen` FOREIGN KEY (`sede_origen_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_transferencias_sede_origen_empresa` FOREIGN KEY (`sede_origen_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_transferencias_solicitante` FOREIGN KEY (`solicitado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_transferencias_almacenes` CHECK (`almacen_origen_id` <> `almacen_destino_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transferencias_existencias`
--

LOCK TABLES `transferencias_existencias` WRITE;
/*!40000 ALTER TABLE `transferencias_existencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `transferencias_existencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `resumen_contrasena` varchar(255) NOT NULL COMMENT 'Hash, nunca contraseña en claro',
  `rol` varchar(40) DEFAULT NULL COMMENT 'Dato heredado; no concede permisos por si solo',
  `token_recordatorio` varchar(100) DEFAULT NULL,
  `foto_perfil` varchar(500) DEFAULT NULL,
  `preferencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferencias`)),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `bloqueado_hasta` datetime(6) DEFAULT NULL,
  `acceso_controlado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_usuarios_correo` (`correo`),
  KEY `ind_usuarios_rol` (`rol`),
  KEY `ind_usuarios_activo_rol` (`activo`,`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_roles`
--

DROP TABLE IF EXISTS `usuarios_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios_roles` (
  `usuario_id` bigint(20) unsigned NOT NULL,
  `rol_id` bigint(20) unsigned NOT NULL,
  `asignado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `vigente_hasta` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`usuario_id`,`rol_id`),
  KEY `ind_usuario_roles_rol_activo` (`rol_id`,`activo`,`usuario_id`),
  KEY `cf_usuario_roles_asignador` (`asignado_por_usuario_id`),
  CONSTRAINT `cf_usuario_roles_asignador` FOREIGN KEY (`asignado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_usuario_roles_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `cf_usuario_roles_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_roles`
--

LOCK TABLES `usuarios_roles` WRITE;
/*!40000 ALTER TABLE `usuarios_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuarios_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_sedes_roles`
--

DROP TABLE IF EXISTS `usuarios_sedes_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios_sedes_roles` (
  `usuario_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `rol_id` bigint(20) unsigned NOT NULL,
  `alcance_asignacion` varchar(20) NOT NULL DEFAULT 'sede',
  `asignado_por_usuario_id` bigint(20) unsigned DEFAULT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `vigente_hasta` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`usuario_id`,`sede_id`,`rol_id`),
  KEY `ind_usuario_sede_roles_sede_rol_activo` (`sede_id`,`rol_id`,`activo`),
  KEY `ind_usuario_sede_roles_usuario_activo` (`usuario_id`,`activo`),
  KEY `cf_usuario_sede_roles_asignador` (`asignado_por_usuario_id`),
  KEY `cf_usuario_sede_rol_alcance` (`rol_id`,`alcance_asignacion`),
  CONSTRAINT `cf_usuario_sede_rol_alcance` FOREIGN KEY (`rol_id`, `alcance_asignacion`) REFERENCES `roles` (`id`, `alcance_permitido`),
  CONSTRAINT `cf_usuario_sede_roles_asignador` FOREIGN KEY (`asignado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cf_usuario_sede_roles_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `cf_usuario_sede_roles_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_usuario_sede_roles_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `ver_usuario_sede_alcance` CHECK (`alcance_asignacion` = 'sede')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_sedes_roles`
--

LOCK TABLES `usuarios_sedes_roles` WRITE;
/*!40000 ALTER TABLE `usuarios_sedes_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuarios_sedes_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variantes_productos`
--

DROP TABLE IF EXISTS `variantes_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variantes_productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(10) unsigned NOT NULL,
  `codigo_sku` varchar(80) DEFAULT NULL,
  `codigo_barras` varchar(80) DEFAULT NULL,
  `nombre_color` varchar(100) NOT NULL DEFAULT 'Único',
  `codigo_color` varchar(20) DEFAULT NULL,
  `existencias` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Proyección heredada; conciliar con kardex',
  `controla_serie` tinyint(1) NOT NULL DEFAULT 0,
  `estado` varchar(20) DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_variantes_id_producto` (`id`,`producto_id`),
  UNIQUE KEY `un_variantes_sku` (`codigo_sku`),
  UNIQUE KEY `un_variantes_codigo_barras` (`codigo_barras`),
  KEY `ind_variantes_producto` (`producto_id`),
  KEY `ind_variantes_producto_estado` (`producto_id`,`estado`),
  CONSTRAINT `cf_variantes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variantes_productos`
--

LOCK TABLES `variantes_productos` WRITE;
/*!40000 ALTER TABLE `variantes_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `variantes_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `sede_id` bigint(20) unsigned NOT NULL,
  `almacen_id` bigint(20) unsigned NOT NULL,
  `pedido_id` int(10) unsigned DEFAULT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `sesion_caja_id` bigint(20) unsigned DEFAULT NULL,
  `codigo` varchar(50) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha_venta` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ventas_empresa_codigo` (`empresa_id`,`codigo`),
  UNIQUE KEY `un_ventas_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
  UNIQUE KEY `un_ventas_pedido` (`pedido_id`),
  KEY `ind_ventas_sede_estado_fecha` (`sede_id`,`estado`,`fecha_venta`),
  KEY `ind_ventas_cliente_fecha` (`cliente_id`,`fecha_venta`),
  KEY `ind_ventas_caja_fecha` (`sesion_caja_id`,`fecha_venta`),
  KEY `cf_ventas_sede_empresa` (`sede_id`,`empresa_id`),
  KEY `cf_ventas_almacen_sede` (`almacen_id`,`sede_id`),
  KEY `cf_ventas_usuario` (`usuario_id`),
  KEY `cf_ventas_cliente_empresa` (`cliente_id`,`empresa_id`),
  KEY `cf_ventas_pedido_ambito` (`pedido_id`,`empresa_id`,`sede_id`),
  CONSTRAINT `cf_ventas_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `cf_ventas_almacen_sede` FOREIGN KEY (`almacen_id`, `sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  CONSTRAINT `cf_ventas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `cf_ventas_cliente_empresa` FOREIGN KEY (`cliente_id`, `empresa_id`) REFERENCES `clientes` (`id`, `empresa_id`),
  CONSTRAINT `cf_ventas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `cf_ventas_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `cf_ventas_pedido_ambito` FOREIGN KEY (`pedido_id`, `empresa_id`, `sede_id`) REFERENCES `pedidos` (`id`, `empresa_id`, `sede_id`),
  CONSTRAINT `cf_ventas_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  CONSTRAINT `cf_ventas_sede_empresa` FOREIGN KEY (`sede_id`, `empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  CONSTRAINT `cf_ventas_sesion_caja` FOREIGN KEY (`sesion_caja_id`) REFERENCES `sesiones_caja` (`id`),
  CONSTRAINT `cf_ventas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ver_ventas_importes` CHECK (`subtotal` >= 0 and `descuento` >= 0 and `impuestos` >= 0 and `total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `versiones_inicializacion_control_acceso`
--

DROP TABLE IF EXISTS `versiones_inicializacion_control_acceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `versiones_inicializacion_control_acceso` (
  `codigo` varchar(80) NOT NULL,
  `aplicado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `versiones_inicializacion_control_acceso`
--

LOCK TABLES `versiones_inicializacion_control_acceso` WRITE;
/*!40000 ALTER TABLE `versiones_inicializacion_control_acceso` DISABLE KEYS */;
/*!40000 ALTER TABLE `versiones_inicializacion_control_acceso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vista_existencias_disponibles`
--

DROP TABLE IF EXISTS `vista_existencias_disponibles`;
/*!50001 DROP VIEW IF EXISTS `vista_existencias_disponibles`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vista_existencias_disponibles` AS SELECT
 1 AS `empresa_id`,
  1 AS `sede_id`,
  1 AS `almacen_id`,
  1 AS `variante_id`,
  1 AS `producto_id`,
  1 AS `cantidad_kardex`,
  1 AS `cantidad_proyectada`,
  1 AS `cantidad_reservada`,
  1 AS `cantidad_disponible`,
  1 AS `diferencia_proyeccion`,
  1 AS `movimientos_sin_interpretar` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vista_kardex_existencias`
--

DROP TABLE IF EXISTS `vista_kardex_existencias`;
/*!50001 DROP VIEW IF EXISTS `vista_kardex_existencias`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vista_kardex_existencias` AS SELECT
 1 AS `empresa_id`,
  1 AS `sede_id`,
  1 AS `almacen_id`,
  1 AS `variante_id`,
  1 AS `producto_id`,
  1 AS `cantidad_kardex`,
  1 AS `movimientos_sin_interpretar` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'md_tecnologia_digital_cell'
--

--
-- Dumping routines for database 'md_tecnologia_digital_cell'
--

--
-- Final view structure for view `vista_existencias_disponibles`
--

/*!50001 DROP VIEW IF EXISTS `vista_existencias_disponibles`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`Rolando`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_existencias_disponibles` AS select `e`.`empresa_id` AS `empresa_id`,`e`.`sede_id` AS `sede_id`,`e`.`almacen_id` AS `almacen_id`,`e`.`variante_id` AS `variante_id`,`e`.`producto_id` AS `producto_id`,coalesce(`k`.`cantidad_kardex`,0) AS `cantidad_kardex`,`e`.`cantidad_fisica` AS `cantidad_proyectada`,`e`.`cantidad_reservada` AS `cantidad_reservada`,`e`.`cantidad_fisica` - `e`.`cantidad_reservada` AS `cantidad_disponible`,case when coalesce(`k`.`movimientos_sin_interpretar`,0) = 0 then coalesce(`k`.`cantidad_kardex`,0) - `e`.`cantidad_fisica` else NULL end AS `diferencia_proyeccion`,coalesce(`k`.`movimientos_sin_interpretar`,0) AS `movimientos_sin_interpretar` from (`existencias_almacen` `e` left join `vista_kardex_existencias` `k` on(`k`.`almacen_id` = `e`.`almacen_id` and `k`.`producto_id` = `e`.`producto_id` and `k`.`variante_id` <=> `e`.`variante_id` and `k`.`sede_id` = `e`.`sede_id` and `k`.`empresa_id` = `e`.`empresa_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vista_kardex_existencias`
--

/*!50001 DROP VIEW IF EXISTS `vista_kardex_existencias`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`Rolando`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_kardex_existencias` AS select `movimientos_inventario`.`empresa_id` AS `empresa_id`,`movimientos_inventario`.`sede_id` AS `sede_id`,`movimientos_inventario`.`almacen_id` AS `almacen_id`,`movimientos_inventario`.`variante_id` AS `variante_id`,`movimientos_inventario`.`producto_id` AS `producto_id`,sum(case when `movimientos_inventario`.`tipo_movimiento` = 'entrada' and `movimientos_inventario`.`cantidad` >= 0 then `movimientos_inventario`.`cantidad` when `movimientos_inventario`.`tipo_movimiento` = 'salida' and `movimientos_inventario`.`cantidad` >= 0 then -`movimientos_inventario`.`cantidad` when `movimientos_inventario`.`tipo_movimiento` = 'ajuste' then `movimientos_inventario`.`cantidad` else 0 end) AS `cantidad_kardex`,sum(case when `movimientos_inventario`.`tipo_movimiento` not in ('entrada','salida','ajuste') or `movimientos_inventario`.`tipo_movimiento` in ('entrada','salida') and `movimientos_inventario`.`cantidad` < 0 then 1 else 0 end) AS `movimientos_sin_interpretar` from `movimientos_inventario` where `movimientos_inventario`.`almacen_id` is not null group by `movimientos_inventario`.`empresa_id`,`movimientos_inventario`.`sede_id`,`movimientos_inventario`.`almacen_id`,`movimientos_inventario`.`variante_id`,`movimientos_inventario`.`producto_id` */;
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

-- Dump completed on 2026-11-04  5:55:50
