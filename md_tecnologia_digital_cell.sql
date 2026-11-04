-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:15327
-- Tiempo de generación: 03-11-2026 a las 20:46:28
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `md_tecnologia_digital_cell`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacenamiento_temporal`
--

CREATE TABLE `almacenamiento_temporal` (
  `clave` varchar(255) NOT NULL,
  `valor` mediumtext NOT NULL,
  `expira_en` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacenes`
--

CREATE TABLE `almacenes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `tipo` varchar(30) NOT NULL DEFAULT 'general',
  `permite_venta` tinyint(1) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aplicaciones_cobro`
--

CREATE TABLE `aplicaciones_cobro` (
  `pago_id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_cobrar_id` bigint(20) UNSIGNED NOT NULL,
  `importe_aplicado` decimal(15,2) NOT NULL,
  `aplicado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aplicaciones_pago`
--

CREATE TABLE `aplicaciones_pago` (
  `pago_id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_pagar_id` bigint(20) UNSIGNED NOT NULL,
  `importe_aplicado` decimal(15,2) NOT NULL,
  `aplicado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_documentales`
--

CREATE TABLE `archivos_documentales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documento_electronico_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_archivo` varchar(50) NOT NULL DEFAULT 'adjunto',
  `contrato_id` bigint(20) UNSIGNED DEFAULT NULL,
  `devolucion_id` int(10) UNSIGNED DEFAULT NULL,
  `guia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `ruta_almacenamiento` varchar(700) NOT NULL,
  `tipo_mime` varchar(150) DEFAULT NULL,
  `tamano_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `sha256` char(64) DEFAULT NULL,
  `cargado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asientos_contables`
--

CREATE TABLE `asientos_contables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `numero` varchar(60) NOT NULL,
  `fecha_asiento` date NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'borrador',
  `origen_modulo` varchar(80) DEFAULT NULL,
  `origen_tipo` varchar(80) DEFAULT NULL,
  `origen_id` varchar(100) DEFAULT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloqueos_almacenamiento_temporal`
--

CREATE TABLE `bloqueos_almacenamiento_temporal` (
  `clave` varchar(255) NOT NULL,
  `propietario` varchar(255) NOT NULL,
  `expira_en` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanas_publicitarias`
--

CREATE TABLE `campanas_publicitarias` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `vistas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `clics` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime(6) DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanas_segmentos`
--

CREATE TABLE `campanas_segmentos` (
  `campana_id` int(10) UNSIGNED NOT NULL,
  `segmento_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_productos`
--

CREATE TABLE `caracteristicas_productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carritos`
--

CREATE TABLE `carritos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `resumen_token` char(64) DEFAULT NULL,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'activo',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `expira_en` datetime(6) DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `casos_soporte`
--

CREATE TABLE `casos_soporte` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `asignado_a_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo` varchar(50) NOT NULL,
  `asunto` varchar(180) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'abierto',
  `prioridad` varchar(20) NOT NULL DEFAULT 'normal',
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'minorista',
  `documento` varchar(20) NOT NULL,
  `razon_social` varchar(160) DEFAULT NULL,
  `nombre_contacto` varchar(160) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(10) UNSIGNED NOT NULL,
  `proveedor_id` int(10) UNSIGNED NOT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(30) NOT NULL DEFAULT 'Pendiente',
  `fecha_compra` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `numero_referencia` varchar(100) DEFAULT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `actualizado_en` datetime(6) DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `almacen_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `proveedor_id` int(10) UNSIGNED DEFAULT NULL,
  `codigo` varchar(60) NOT NULL,
  `tipo` varchar(60) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `inicia_en` date DEFAULT NULL,
  `finaliza_en` date DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(30) NOT NULL DEFAULT 'Borrador',
  `notas` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_bancarias`
--

CREATE TABLE `cuentas_bancarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `banco` varchar(120) NOT NULL,
  `tipo_cuenta` varchar(40) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `cuenta_mascara` varchar(40) DEFAULT NULL,
  `referencia_secreto` varchar(255) DEFAULT NULL COMMENT 'Referencia externa; no almacenar el secreto',
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_por_cobrar`
--

CREATE TABLE `cuentas_por_cobrar` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documento_electronico_id` bigint(20) UNSIGNED NOT NULL,
  `monto_original` decimal(15,2) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `vence_en` date NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_por_pagar`
--

CREATE TABLE `cuentas_por_pagar` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `compra_id` int(10) UNSIGNED NOT NULL,
  `monto_original` decimal(15,2) NOT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `vence_en` date NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `tipo_descuento` varchar(20) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `minimo_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `limite_usos` int(10) UNSIGNED DEFAULT NULL,
  `usos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `inicia_en` datetime(6) NOT NULL,
  `finaliza_en` datetime(6) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_carritos`
--

CREATE TABLE `detalles_carritos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `carrito_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `variante_presente` tinyint(3) UNSIGNED GENERATED ALWAYS AS (case when `variante_id` is null then 0 else 1 end) STORED,
  `variante_clave` int(10) UNSIGNED GENERATED ALWAYS AS (ifnull(`variante_id`,0)) STORED,
  `cantidad` decimal(18,6) NOT NULL,
  `precio_mostrado_historico` decimal(15,2) DEFAULT NULL,
  `agregado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_compras`
--

CREATE TABLE `detalles_compras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `compra_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_compras_dispositivos`
--

CREATE TABLE `detalles_compras_dispositivos` (
  `detalle_compra_id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_cotizaciones`
--

CREATE TABLE `detalles_cotizaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `descripcion_historica` varchar(255) DEFAULT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_devoluciones`
--

CREATE TABLE `detalles_devoluciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `devolucion_id` int(10) UNSIGNED NOT NULL,
  `detalle_venta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `dispositivo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `estado_revision` varchar(40) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_documentos_electronicos`
--

CREATE TABLE `detalles_documentos_electronicos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documento_id` bigint(20) UNSIGNED NOT NULL,
  `detalle_venta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo_producto` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `unidad_medida` varchar(12) NOT NULL DEFAULT 'NIU',
  `valor_unitario` decimal(15,6) NOT NULL,
  `precio_unitario` decimal(15,6) NOT NULL,
  `tasa_impuesto` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_envios_pedidos`
--

CREATE TABLE `detalles_envios_pedidos` (
  `envio_pedido_id` bigint(20) UNSIGNED NOT NULL,
  `detalle_pedido_id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `cantidad` decimal(18,6) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_guias_remision`
--

CREATE TABLE `detalles_guias_remision` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `guia_id` bigint(20) UNSIGNED NOT NULL,
  `detalle_venta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `unidad_medida` varchar(12) NOT NULL DEFAULT 'NIU'
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedidos`
--

CREATE TABLE `detalles_pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `descripcion_historica` varchar(255) DEFAULT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_transferencias_existencias`
--

CREATE TABLE `detalles_transferencias_existencias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transferencia_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `variante_presente` tinyint(3) UNSIGNED GENERATED ALWAYS AS (case when `variante_id` is null then 0 else 1 end) STORED,
  `variante_clave` int(10) UNSIGNED GENERATED ALWAYS AS (ifnull(`variante_id`,0)) STORED,
  `cantidad_solicitada` decimal(18,6) NOT NULL,
  `cantidad_enviada` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `cantidad_recibida` decimal(18,6) NOT NULL DEFAULT 0.000000
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_ventas`
--

CREATE TABLE `detalles_ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `detalle_pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `descripcion_historica` varchar(255) NOT NULL,
  `sku_historico` varchar(80) DEFAULT NULL,
  `cantidad` decimal(18,6) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tasa_impuesto` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `impuesto` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_linea` decimal(15,2) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_ventas_dispositivos`
--

CREATE TABLE `detalles_ventas_dispositivos` (
  `detalle_venta_id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones_garantias`
--

CREATE TABLE `devoluciones_garantias` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED DEFAULT NULL,
  `tipo` varchar(30) NOT NULL,
  `motivo` text NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'Recibida',
  `fecha_solicitud` date NOT NULL,
  `fecha_limite` date DEFAULT NULL,
  `resolucion` text DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `venta_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones_cliente`
--

CREATE TABLE `direcciones_cliente` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
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
  `actualizado_en` datetime(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `imei` varchar(20) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `detalle_compra_id` bigint(20) UNSIGNED DEFAULT NULL,
  `almacen_actual_id` bigint(20) UNSIGNED DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'disponible',
  `fecha_ingreso` datetime(6) DEFAULT NULL,
  `fecha_salida` datetime(6) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_electronicos`
--

CREATE TABLE `documentos_electronicos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `serie_documento_id` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `documento_referencia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_documento_id` char(2) NOT NULL,
  `serie` varchar(8) NOT NULL,
  `correlativo` bigint(20) UNSIGNED NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `emisor_historico` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`emisor_historico`)),
  `receptor_historico` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`receptor_historico`)),
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha_emision` datetime(6) NOT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `elementos_menu`
--

CREATE TABLE `elementos_menu` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `padre_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pantalla_id` bigint(20) UNSIGNED DEFAULT NULL,
  `permiso_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo` varchar(100) NOT NULL,
  `etiqueta` varchar(160) NOT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'pantalla',
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `razon_social` varchar(180) NOT NULL,
  `nombre_comercial` varchar(180) DEFAULT NULL,
  `ruc` char(11) DEFAULT NULL,
  `moneda_base` char(3) NOT NULL DEFAULT 'PEN',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios_pedidos`
--

CREATE TABLE `envios_pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `almacen_id` bigint(20) UNSIGNED NOT NULL,
  `guia_remision_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios_sunat`
--

CREATE TABLE `envios_sunat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documento_id` bigint(20) UNSIGNED NOT NULL,
  `numero_intento` smallint(5) UNSIGNED NOT NULL,
  `estado` varchar(30) NOT NULL,
  `codigo_respuesta` varchar(40) DEFAULT NULL,
  `descripcion_respuesta` varchar(2000) DEFAULT NULL,
  `resumen_solicitud` char(64) DEFAULT NULL,
  `resumen_respuesta` char(64) DEFAULT NULL,
  `enviado_en` datetime(6) DEFAULT NULL,
  `respondido_en` datetime(6) DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_campana`
--

CREATE TABLE `eventos_campana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campana_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(20) NOT NULL,
  `resumen_sesion` char(64) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `pedido_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_casos_soporte`
--

CREATE TABLE `eventos_casos_soporte` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `caso_soporte_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(40) NOT NULL,
  `contenido` text DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_envios_pedidos`
--

CREATE TABLE `eventos_envios_pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `envio_pedido_id` bigint(20) UNSIGNED NOT NULL,
  `estado` varchar(30) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `fuente` varchar(30) NOT NULL DEFAULT 'interno',
  `identificador_externo` varchar(160) DEFAULT NULL,
  `ocurrido_en` datetime(6) NOT NULL,
  `registrado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `datos_seguros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_seguros`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_integracion`
--

CREATE TABLE `eventos_integracion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `integracion_id` bigint(20) UNSIGNED NOT NULL,
  `correlacion_id` varchar(100) DEFAULT NULL,
  `direccion` enum('entrada','salida') NOT NULL,
  `estado` varchar(30) NOT NULL,
  `codigo_http` smallint(5) UNSIGNED DEFAULT NULL,
  `resumen_solicitud` char(64) DEFAULT NULL,
  `resumen_respuesta` char(64) DEFAULT NULL,
  `detalle_seguro` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Solo datos saneados, sin secretos ni credenciales' CHECK (json_valid(`detalle_seguro`)),
  `intentos` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `iniciado_en` datetime(6) NOT NULL,
  `finalizado_en` datetime(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `existencias_almacen`
--

CREATE TABLE `existencias_almacen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `almacen_id` bigint(20) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `variante_presente` tinyint(3) UNSIGNED GENERATED ALWAYS AS (case when `variante_id` is null then 0 else 1 end) STORED,
  `variante_clave` int(10) UNSIGNED GENERATED ALWAYS AS (ifnull(`variante_id`,0)) STORED,
  `cantidad_fisica` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `cantidad_reservada` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guias_remision`
--

CREATE TABLE `guias_remision` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `almacen_origen_id` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `serie` varchar(8) NOT NULL,
  `correlativo` bigint(20) UNSIGNED NOT NULL,
  `motivo_traslado` varchar(80) NOT NULL,
  `modalidad_traslado` varchar(30) NOT NULL,
  `transportista_ruc` char(11) DEFAULT NULL,
  `transportista_nombre` varchar(180) DEFAULT NULL,
  `direccion_origen` varchar(255) NOT NULL,
  `direccion_destino` varchar(255) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `emitida_en` datetime(6) DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_migraciones_origen`
--

CREATE TABLE `historial_migraciones_origen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `origen_tabla` enum('migraciones','migrations') NOT NULL,
  `origen_id` bigint(20) UNSIGNED NOT NULL,
  `nombre_migracion` varchar(255) NOT NULL,
  `nombre_migracion_heredada` varchar(255) GENERATED ALWAYS AS (case when `origen_tabla` = 'migraciones' then `nombre_migracion` else NULL end) STORED,
  `lote` bigint(20) NOT NULL,
  `ejecutada_en` timestamp NULL DEFAULT NULL,
  `archivada_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_variantes`
--

CREATE TABLE `imagenes_variantes` (
  `id` int(10) UNSIGNED NOT NULL,
  `variante_id` int(10) UNSIGNED NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `imagen_principal` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `integraciones`
--

CREATE TABLE `integraciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `proveedor` varchar(100) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 0,
  `referencia_secreto` varchar(255) DEFAULT NULL COMMENT 'Referencia a gestor externo; no guardar secretos ni tokens',
  `configuracion_publica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracion_publica`)),
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_pago`
--

CREATE TABLE `intentos_pago` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `medio_pago_id` bigint(20) UNSIGNED NOT NULL,
  `pago_id` bigint(20) UNSIGNED DEFAULT NULL,
  `proveedor` varchar(100) NOT NULL,
  `clave_idempotencia` char(64) NOT NULL,
  `referencia_externa` varchar(160) DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'iniciado',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `importe` decimal(15,2) NOT NULL,
  `codigo_respuesta` varchar(80) DEFAULT NULL,
  `detalle_seguro` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Solo contenido saneado; no PAN, CVV, tokens ni secretos' CHECK (json_valid(`detalle_seguro`)),
  `solicitado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `respondido_en` datetime(6) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_asientos`
--

CREATE TABLE `lineas_asientos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asiento_id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `debe` decimal(15,2) NOT NULL DEFAULT 0.00,
  `haber` decimal(15,2) NOT NULL DEFAULT 0.00,
  `moneda` char(3) NOT NULL DEFAULT 'PEN'
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logistica_mayorista`
--

CREATE TABLE `logistica_mayorista` (
  `pedido_id` int(10) UNSIGNED NOT NULL,
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
  `cantidad_unidades` int(10) UNSIGNED DEFAULT NULL,
  `bultos` varchar(160) DEFAULT NULL,
  `tipo_embalaje` varchar(100) DEFAULT NULL,
  `dimensiones` varchar(120) DEFAULT NULL,
  `volumen_m3` decimal(10,3) DEFAULT NULL,
  `peso_neto` decimal(10,2) DEFAULT NULL,
  `peso_bruto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes_trabajos`
--

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
  `finalizado_en` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medios_pago`
--

CREATE TABLE `medios_pago` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `requiere_caja` tinyint(1) NOT NULL DEFAULT 0,
  `requiere_banco` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `contacto` varchar(160) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'Nuevo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus`
--

CREATE TABLE `menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `contexto` varchar(40) NOT NULL DEFAULT 'sidebar',
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migraciones_laravel`
--

CREATE TABLE `migraciones_laravel` (
  `id` int(10) UNSIGNED NOT NULL,
  `migracion` varchar(255) NOT NULL,
  `lote` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_bancarios`
--

CREATE TABLE `movimientos_bancarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_bancaria_id` bigint(20) UNSIGNED NOT NULL,
  `pago_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(40) NOT NULL,
  `importe` decimal(15,2) NOT NULL,
  `referencia` varchar(120) DEFAULT NULL,
  `ocurrido_en` datetime(6) NOT NULL,
  `conciliado` tinyint(1) NOT NULL DEFAULT 0,
  `conciliado_en` datetime(6) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_caja`
--

CREATE TABLE `movimientos_caja` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sesion_caja_id` bigint(20) UNSIGNED NOT NULL,
  `pago_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(40) NOT NULL,
  `direccion` enum('entrada','salida') NOT NULL,
  `importe` decimal(15,2) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `creado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_dispositivos`
--

CREATE TABLE `movimientos_dispositivos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` bigint(20) UNSIGNED NOT NULL,
  `movimiento_inventario_id` bigint(20) UNSIGNED NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_movimiento` varchar(30) NOT NULL,
  `cantidad` decimal(18,6) NOT NULL COMMENT 'Entrada y salida como magnitud; ajuste como delta firmado',
  `notas` varchar(500) NOT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `almacen_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variante_id` int(10) UNSIGNED DEFAULT NULL,
  `documento_tipo` varchar(60) DEFAULT NULL,
  `documento_id` varchar(80) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` char(36) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `tipo_notificable` varchar(255) NOT NULL,
  `notificable_id` bigint(20) UNSIGNED NOT NULL,
  `datos` text NOT NULL,
  `leido_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(60) NOT NULL,
  `tipo` enum('cobro','pago','reembolso') NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'registrado',
  `medio_pago_id` bigint(20) UNSIGNED NOT NULL,
  `sesion_caja_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cuenta_bancaria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `venta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `compra_id` int(10) UNSIGNED DEFAULT NULL,
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `importe` decimal(15,2) NOT NULL,
  `referencia` varchar(120) DEFAULT NULL,
  `fecha_pago` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pantallas`
--

CREATE TABLE `pantallas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `modulo_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(120) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `ruta` varchar(160) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `habilitada` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cupon_codigo` varchar(50) DEFAULT NULL,
  `estado` varchar(40) NOT NULL DEFAULT 'Pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `cotizacion_id` int(10) UNSIGNED DEFAULT NULL,
  `canal` varchar(30) DEFAULT NULL COMMENT 'Canal no confirmado cuando es NULL',
  `tipo_venta` varchar(30) DEFAULT NULL COMMENT 'Propuesta A21, pendiente validar semantica de canal',
  `observaciones` varchar(500) DEFAULT NULL COMMENT 'Propuesta A21, sin importacion historica implicita',
  `cupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direccion_entrega_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direccion_facturacion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direccion_entrega_historica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`direccion_entrega_historica`)),
  `direccion_facturacion_historica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`direccion_facturacion_historica`)),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `almacen_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles_inteligentes_productos`
--

CREATE TABLE `perfiles_inteligentes_productos` (
  `producto_id` int(10) UNSIGNED NOT NULL,
  `puntuacion_rendimiento` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `puntuacion_camara` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `puntuacion_bateria` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `puntuacion_valor` tinyint(3) UNSIGNED NOT NULL DEFAULT 70,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `modulo_id` bigint(20) UNSIGNED NOT NULL,
  `pantalla_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo` varchar(160) NOT NULL,
  `accion` varchar(60) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `es_sistema` tinyint(1) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `permisos`
--
DELIMITER $$
CREATE TRIGGER `disparador_permisos_sistema_impedir_eliminacion` BEFORE DELETE ON `permisos` FOR EACH ROW BEGIN
  IF OLD.es_sistema = TRUE THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede borrar un permiso protegido';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `disparador_permisos_sistema_proteger_identidad` BEFORE UPDATE ON `permisos` FOR EACH ROW BEGIN
  IF OLD.es_sistema = TRUE AND (NEW.id <> OLD.id OR BINARY NEW.codigo <> BINARY OLD.codigo OR NEW.modulo_id <> OLD.modulo_id OR NOT (NEW.pantalla_id <=> OLD.pantalla_id) OR BINARY NEW.accion <> BINARY OLD.accion OR NEW.es_sistema <> OLD.es_sistema) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede cambiar la identidad de un permiso protegido';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_cuentas`
--

CREATE TABLE `planes_cuentas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_padre_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `acepta_movimientos` tinyint(1) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `marca` varchar(80) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `categoria` varchar(120) NOT NULL COMMENT 'Campo legado; retirar solo tras conciliación aprobada',
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_original` decimal(10,2) DEFAULT NULL,
  `descuento` varchar(20) DEFAULT NULL,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `url_imagen` varchar(255) DEFAULT NULL,
  `existencias` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Proyección heredada, no fuente del kardex',
  `almacenamiento` varchar(80) DEFAULT NULL,
  `color` varchar(80) DEFAULT NULL,
  `etiqueta` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_proveedores`
--

CREATE TABLE `productos_proveedores` (
  `producto_id` int(10) UNSIGNED NOT NULL,
  `proveedor_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `correo` varchar(160) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reembolsos`
--

CREATE TABLE `reembolsos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `devolucion_id` int(10) UNSIGNED NOT NULL,
  `pago_id` bigint(20) UNSIGNED NOT NULL,
  `importe` decimal(15,2) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `autorizado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referencias_imagenes_productos`
--

CREATE TABLE `referencias_imagenes_productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `actualizado_en` datetime(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_auditoria`
--

CREATE TABLE `registros_auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `entidad` varchar(80) NOT NULL,
  `entidad_id` bigint(20) DEFAULT NULL COMMENT 'Identificador numerico legado; el identificador textual admite otros formatos',
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_nuevos`)),
  `direccion_ip` varchar(45) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `identificador_universal_evento` char(36) DEFAULT NULL,
  `rol_codigo` varchar(40) DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sede_id` bigint(20) UNSIGNED DEFAULT NULL,
  `modulo` varchar(80) DEFAULT NULL,
  `resultado` varchar(20) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `correlacion_id` varchar(64) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `ocurrido_en` datetime(6) DEFAULT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `identificador_entidad` varchar(128) DEFAULT NULL,
  `cambios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cambios`)),
  `origen` varchar(20) NOT NULL DEFAULT 'laravel'
) ;

--
-- Disparadores `registros_auditoria`
--
DELIMITER $$
CREATE TRIGGER `disparador_auditoria_impedir_actualizacion` BEFORE UPDATE ON `registros_auditoria` FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'registros_auditoria es solo de insercion'
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `disparador_auditoria_impedir_eliminacion` BEFORE DELETE ON `registros_auditoria` FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'registros_auditoria es solo de insercion'
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `alcance_permitido` varchar(20) NOT NULL DEFAULT 'sede',
  `es_sistema` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ;

--
-- Disparadores `roles`
--
DELIMITER $$
CREATE TRIGGER `disparador_roles_sistema_impedir_eliminacion` BEFORE DELETE ON `roles` FOR EACH ROW BEGIN
  IF OLD.es_sistema = TRUE THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede borrar un rol protegido';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `disparador_roles_sistema_proteger_identidad` BEFORE UPDATE ON `roles` FOR EACH ROW BEGIN
  IF OLD.es_sistema = TRUE AND (NEW.id <> OLD.id OR BINARY NEW.codigo <> BINARY OLD.codigo OR NEW.alcance_permitido <> OLD.alcance_permitido OR NEW.es_sistema <> OLD.es_sistema) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede cambiar la identidad de un rol protegido';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_menus`
--

CREATE TABLE `roles_menus` (
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

CREATE TABLE `roles_permisos` (
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `permiso_id` bigint(20) UNSIGNED NOT NULL,
  `alcance` varchar(20) NOT NULL DEFAULT 'sede',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Disparadores `roles_permisos`
--
DELIMITER $$
CREATE TRIGGER `disparador_permisos_administrador_impedir_actualizacion` BEFORE UPDATE ON `roles_permisos` FOR EACH ROW BEGIN
  IF EXISTS (SELECT 1 FROM roles WHERE id IN (OLD.rol_id, NEW.rol_id) AND codigo = 'administrador') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede modificar un permiso del rol administrador';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `disparador_permisos_administrador_impedir_eliminacion` BEFORE DELETE ON `roles_permisos` FOR EACH ROW BEGIN
  IF EXISTS (SELECT 1 FROM roles WHERE id = OLD.rol_id AND codigo = 'administrador') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede retirar un permiso del rol administrador';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(140) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `zona_horaria` varchar(64) NOT NULL DEFAULT 'America/Lima',
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `segmentos_clientes`
--

CREATE TABLE `segmentos_clientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `criterios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`criterios`)),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `series_documentos`
--

CREATE TABLE `series_documentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_documento_id` char(2) NOT NULL,
  `serie` varchar(8) NOT NULL,
  `siguiente_correlativo` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_aplicacion`
--

CREATE TABLE `sesiones_aplicacion` (
  `id` varchar(255) NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `contenido` longtext NOT NULL COMMENT 'No serializar secretos; proteger el payload en la capa de sesión',
  `ultima_actividad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_caja`
--

CREATE TABLE `sesiones_caja` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `caja_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_apertura_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_cierre_id` bigint(20) UNSIGNED DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'abierta',
  `saldo_inicial` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo_cierre_declarado` decimal(15,2) DEFAULT NULL,
  `abierta_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `cerrada_en` datetime(6) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuarios`
--

CREATE TABLE `sesiones_usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `resumen_identificador` char(64) NOT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `ultimo_acceso` timestamp NOT NULL DEFAULT current_timestamp(),
  `revocada_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_documento_electronico`
--

CREATE TABLE `tipos_documento_electronico` (
  `codigo` char(2) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `requiere_documento_referencia` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens_recuperacion_contrasena`
--

CREATE TABLE `tokens_recuperacion_contrasena` (
  `correo` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL COMMENT 'Hash emitido por el repositorio de tokens',
  `creado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos_fallidos`
--

CREATE TABLE `trabajos_fallidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identificador_universal` varchar(255) NOT NULL,
  `conexion` varchar(255) NOT NULL,
  `cola` varchar(255) NOT NULL,
  `contenido` longtext NOT NULL,
  `excepcion` longtext NOT NULL,
  `fallido_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos_pendientes`
--

CREATE TABLE `trabajos_pendientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cola` varchar(255) NOT NULL,
  `contenido` longtext NOT NULL,
  `intentos` smallint(5) UNSIGNED NOT NULL,
  `reservado_en` int(10) UNSIGNED DEFAULT NULL,
  `disponible_en` int(10) UNSIGNED NOT NULL,
  `creado_en` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transferencias_existencias`
--

CREATE TABLE `transferencias_existencias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_origen_id` bigint(20) UNSIGNED NOT NULL,
  `almacen_origen_id` bigint(20) UNSIGNED NOT NULL,
  `sede_destino_id` bigint(20) UNSIGNED NOT NULL,
  `almacen_destino_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `solicitado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aprobado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `enviado_en` datetime(6) DEFAULT NULL,
  `recibido_en` datetime(6) DEFAULT NULL,
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `actualizado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `asignado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `vigente_hasta` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_sedes_roles`
--

CREATE TABLE `usuarios_sedes_roles` (
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `alcance_asignacion` varchar(20) NOT NULL DEFAULT 'sede',
  `asignado_por_usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `vigente_hasta` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variantes_productos`
--

CREATE TABLE `variantes_productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `codigo_sku` varchar(80) DEFAULT NULL,
  `codigo_barras` varchar(80) DEFAULT NULL,
  `nombre_color` varchar(100) NOT NULL DEFAULT 'Único',
  `codigo_color` varchar(20) DEFAULT NULL,
  `existencias` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Proyección heredada; conciliar con kardex',
  `controla_serie` tinyint(1) NOT NULL DEFAULT 0,
  `estado` varchar(20) DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `sede_id` bigint(20) UNSIGNED NOT NULL,
  `almacen_id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sesion_caja_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo` varchar(50) NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'borrador',
  `moneda` char(3) NOT NULL DEFAULT 'PEN',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fecha_venta` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `creado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `actualizado_en` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `versiones_inicializacion_control_acceso`
--

CREATE TABLE `versiones_inicializacion_control_acceso` (
  `codigo` varchar(80) NOT NULL,
  `aplicado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_existencias_disponibles`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_existencias_disponibles` (
`empresa_id` bigint(20) unsigned
,`sede_id` bigint(20) unsigned
,`almacen_id` bigint(20) unsigned
,`variante_id` int(10) unsigned
,`producto_id` int(10) unsigned
,`cantidad_kardex` decimal(40,6)
,`cantidad_proyectada` decimal(18,6)
,`cantidad_reservada` decimal(18,6)
,`cantidad_disponible` decimal(19,6)
,`diferencia_proyeccion` decimal(41,6)
,`movimientos_sin_interpretar` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_kardex_existencias`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_kardex_existencias` (
`empresa_id` bigint(20) unsigned
,`sede_id` bigint(20) unsigned
,`almacen_id` bigint(20) unsigned
,`variante_id` int(10) unsigned
,`producto_id` int(10) unsigned
,`cantidad_kardex` decimal(40,6)
,`movimientos_sin_interpretar` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_existencias_disponibles`
--
DROP TABLE IF EXISTS `vista_existencias_disponibles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`Rolando`@`localhost` SQL SECURITY DEFINER VIEW `vista_existencias_disponibles`  AS SELECT `e`.`empresa_id` AS `empresa_id`, `e`.`sede_id` AS `sede_id`, `e`.`almacen_id` AS `almacen_id`, `e`.`variante_id` AS `variante_id`, `e`.`producto_id` AS `producto_id`, coalesce(`k`.`cantidad_kardex`,0) AS `cantidad_kardex`, `e`.`cantidad_fisica` AS `cantidad_proyectada`, `e`.`cantidad_reservada` AS `cantidad_reservada`, `e`.`cantidad_fisica`- `e`.`cantidad_reservada` AS `cantidad_disponible`, CASE WHEN coalesce(`k`.`movimientos_sin_interpretar`,0) = 0 THEN coalesce(`k`.`cantidad_kardex`,0) - `e`.`cantidad_fisica` ELSE NULL END AS `diferencia_proyeccion`, coalesce(`k`.`movimientos_sin_interpretar`,0) AS `movimientos_sin_interpretar` FROM (`existencias_almacen` `e` left join `vista_kardex_existencias` `k` on(`k`.`almacen_id` = `e`.`almacen_id` and `k`.`producto_id` = `e`.`producto_id` and `k`.`variante_id` <=> `e`.`variante_id` and `k`.`sede_id` = `e`.`sede_id` and `k`.`empresa_id` = `e`.`empresa_id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_kardex_existencias`
--
DROP TABLE IF EXISTS `vista_kardex_existencias`;

CREATE ALGORITHM=UNDEFINED DEFINER=`Rolando`@`localhost` SQL SECURITY DEFINER VIEW `vista_kardex_existencias`  AS SELECT `movimientos_inventario`.`empresa_id` AS `empresa_id`, `movimientos_inventario`.`sede_id` AS `sede_id`, `movimientos_inventario`.`almacen_id` AS `almacen_id`, `movimientos_inventario`.`variante_id` AS `variante_id`, `movimientos_inventario`.`producto_id` AS `producto_id`, sum(case when `movimientos_inventario`.`tipo_movimiento` = 'entrada' and `movimientos_inventario`.`cantidad` >= 0 then `movimientos_inventario`.`cantidad` when `movimientos_inventario`.`tipo_movimiento` = 'salida' and `movimientos_inventario`.`cantidad` >= 0 then -`movimientos_inventario`.`cantidad` when `movimientos_inventario`.`tipo_movimiento` = 'ajuste' then `movimientos_inventario`.`cantidad` else 0 end) AS `cantidad_kardex`, sum(case when `movimientos_inventario`.`tipo_movimiento` not in ('entrada','salida','ajuste') or `movimientos_inventario`.`tipo_movimiento` in ('entrada','salida') and `movimientos_inventario`.`cantidad` < 0 then 1 else 0 end) AS `movimientos_sin_interpretar` FROM `movimientos_inventario` WHERE `movimientos_inventario`.`almacen_id` is not null GROUP BY `movimientos_inventario`.`empresa_id`, `movimientos_inventario`.`sede_id`, `movimientos_inventario`.`almacen_id`, `movimientos_inventario`.`variante_id`, `movimientos_inventario`.`producto_id` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `almacenamiento_temporal`
--
ALTER TABLE `almacenamiento_temporal`
  ADD PRIMARY KEY (`clave`),
  ADD KEY `ind_almacenamiento_temporal_vencimiento` (`expira_en`);

--
-- Indices de la tabla `almacenes`
--
ALTER TABLE `almacenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_almacenes_sede_codigo` (`sede_id`,`codigo`),
  ADD UNIQUE KEY `un_almacenes_id_sede` (`id`,`sede_id`),
  ADD KEY `ind_almacenes_sede_activo` (`sede_id`,`activo`);

--
-- Indices de la tabla `aplicaciones_cobro`
--
ALTER TABLE `aplicaciones_cobro`
  ADD PRIMARY KEY (`pago_id`,`cuenta_cobrar_id`),
  ADD KEY `ind_aplicaciones_cobro_cuenta` (`cuenta_cobrar_id`,`aplicado_en`);

--
-- Indices de la tabla `aplicaciones_pago`
--
ALTER TABLE `aplicaciones_pago`
  ADD PRIMARY KEY (`pago_id`,`cuenta_pagar_id`),
  ADD KEY `ind_aplicaciones_pago_cuenta` (`cuenta_pagar_id`,`aplicado_en`);

--
-- Indices de la tabla `archivos_documentales`
--
ALTER TABLE `archivos_documentales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_archivos_documento` (`documento_electronico_id`),
  ADD KEY `ind_archivos_contrato` (`contrato_id`),
  ADD KEY `ind_archivos_devolucion` (`devolucion_id`),
  ADD KEY `ind_archivos_guia` (`guia_id`),
  ADD KEY `cf_archivos_usuario` (`cargado_por_usuario_id`);

--
-- Indices de la tabla `asientos_contables`
--
ALTER TABLE `asientos_contables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_asientos_empresa_numero` (`empresa_id`,`numero`),
  ADD KEY `ind_asientos_sede_fecha_estado` (`sede_id`,`fecha_asiento`,`estado`),
  ADD KEY `ind_asientos_origen` (`origen_modulo`,`origen_tipo`,`origen_id`),
  ADD KEY `cf_asientos_usuario` (`usuario_id`),
  ADD KEY `cf_asientos_sede_empresa` (`sede_id`,`empresa_id`);

--
-- Indices de la tabla `bloqueos_almacenamiento_temporal`
--
ALTER TABLE `bloqueos_almacenamiento_temporal`
  ADD PRIMARY KEY (`clave`),
  ADD KEY `ind_bloqueos_almacenamiento_temporal_vencimiento` (`expira_en`);

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_cajas_sede_codigo` (`sede_id`,`codigo`);

--
-- Indices de la tabla `campanas_publicitarias`
--
ALTER TABLE `campanas_publicitarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_campanas_estado_fechas` (`activo`,`inicia_en`,`finaliza_en`),
  ADD KEY `ind_campanas_ubicacion` (`ubicacion`),
  ADD KEY `ind_campanas_empresa` (`empresa_id`);

--
-- Indices de la tabla `campanas_segmentos`
--
ALTER TABLE `campanas_segmentos`
  ADD PRIMARY KEY (`campana_id`,`segmento_id`),
  ADD KEY `ind_campana_segmento_segmento` (`segmento_id`);

--
-- Indices de la tabla `caracteristicas_productos`
--
ALTER TABLE `caracteristicas_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_producto_caracteristicas_producto_nombre` (`producto_id`,`nombre`);

--
-- Indices de la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_carritos_token_resumen` (`resumen_token`),
  ADD UNIQUE KEY `un_carritos_pedido` (`pedido_id`),
  ADD KEY `ind_carritos_usuario_estado` (`usuario_id`,`estado`,`actualizado_en`),
  ADD KEY `ind_carritos_cliente_estado` (`cliente_id`,`estado`,`actualizado_en`),
  ADD KEY `cf_carritos_empresa` (`empresa_id`),
  ADD KEY `cf_carritos_sede_empresa` (`sede_id`,`empresa_id`);

--
-- Indices de la tabla `casos_soporte`
--
ALTER TABLE `casos_soporte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_casos_empresa_codigo` (`empresa_id`,`codigo`),
  ADD KEY `ind_casos_sede_estado_prioridad` (`sede_id`,`estado`,`prioridad`),
  ADD KEY `ind_casos_asignado_estado` (`asignado_a_usuario_id`,`estado`),
  ADD KEY `cf_casos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_casos_cliente` (`cliente_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_categorias_nombre` (`nombre`),
  ADD UNIQUE KEY `un_categorias_id_empresa` (`id`,`empresa_id`),
  ADD KEY `ind_categorias_activo` (`activo`),
  ADD KEY `ind_categorias_empresa` (`empresa_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_clientes_documento` (`documento`),
  ADD UNIQUE KEY `un_clientes_id_empresa` (`id`,`empresa_id`),
  ADD KEY `ind_clientes_usuario` (`usuario_id`),
  ADD KEY `ind_clientes_tipo_activo` (`tipo`,`activo`),
  ADD KEY `ind_clientes_correo` (`correo`),
  ADD KEY `ind_clientes_empresa` (`empresa_id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_compras_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
  ADD KEY `ind_compras_estado_fecha` (`estado`,`fecha_compra`),
  ADD KEY `ind_compras_proveedor` (`proveedor_id`),
  ADD KEY `ind_compras_creador` (`creado_por_usuario_id`),
  ADD KEY `ind_compras_empresa_fecha` (`empresa_id`,`fecha_compra`),
  ADD KEY `ind_compras_sede_fecha` (`sede_id`,`fecha_compra`),
  ADD KEY `cf_compras_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_compras_almacen_sede` (`almacen_id`,`sede_id`),
  ADD KEY `cf_compras_proveedor_empresa` (`proveedor_id`,`empresa_id`);

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_contratos_empresa_codigo` (`empresa_id`,`codigo`),
  ADD KEY `ind_contratos_empresa_estado_fin` (`empresa_id`,`estado`,`finaliza_en`),
  ADD KEY `cf_contratos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_contratos_cliente` (`cliente_id`),
  ADD KEY `cf_contratos_proveedor` (`proveedor_id`),
  ADD KEY `cf_contratos_usuario` (`creado_por_usuario_id`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_cotizaciones_estado_fecha` (`estado`,`creado_en`),
  ADD KEY `ind_cotizaciones_cliente` (`cliente_id`),
  ADD KEY `ind_cotizaciones_creador` (`creado_por_usuario_id`),
  ADD KEY `ind_cotizaciones_empresa_fecha` (`empresa_id`,`creado_en`),
  ADD KEY `cf_cotizaciones_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_cotizaciones_cliente_empresa` (`cliente_id`,`empresa_id`);

--
-- Indices de la tabla `cuentas_bancarias`
--
ALTER TABLE `cuentas_bancarias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_cuentas_bancarias_codigo` (`empresa_id`,`codigo`),
  ADD UNIQUE KEY `un_cuentas_bancarias_id_empresa` (`id`,`empresa_id`);

--
-- Indices de la tabla `cuentas_por_cobrar`
--
ALTER TABLE `cuentas_por_cobrar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_cuentas_cobrar_documento` (`documento_electronico_id`),
  ADD KEY `ind_cuentas_cobrar_estado_vencimiento` (`estado`,`vence_en`);

--
-- Indices de la tabla `cuentas_por_pagar`
--
ALTER TABLE `cuentas_por_pagar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_cuentas_pagar_compra` (`compra_id`),
  ADD KEY `ind_cuentas_pagar_estado_vencimiento` (`estado`,`vence_en`);

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_cupones_codigo` (`codigo`),
  ADD KEY `ind_cupones_vigencia` (`activo`,`inicia_en`,`finaliza_en`),
  ADD KEY `ind_cupones_activo` (`activo`);

--
-- Indices de la tabla `detalles_carritos`
--
ALTER TABLE `detalles_carritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_carrito_detalles_variante` (`carrito_id`,`variante_id`),
  ADD UNIQUE KEY `un_carrito_producto_modo` (`carrito_id`,`producto_id`,`variante_presente`,`variante_clave`),
  ADD KEY `cf_carrito_detalles_producto` (`producto_id`),
  ADD KEY `cf_carrito_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_compras`
--
ALTER TABLE `detalles_compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_compra_detalles_compra` (`compra_id`),
  ADD KEY `ind_compra_detalles_producto` (`producto_id`),
  ADD KEY `ind_compra_detalles_variante` (`variante_id`),
  ADD KEY `cf_compra_detalles_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_compras_dispositivos`
--
ALTER TABLE `detalles_compras_dispositivos`
  ADD PRIMARY KEY (`detalle_compra_id`,`dispositivo_id`),
  ADD UNIQUE KEY `un_compra_dispositivo` (`dispositivo_id`);

--
-- Indices de la tabla `detalles_cotizaciones`
--
ALTER TABLE `detalles_cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_cotizacion_detalles_cotizacion` (`cotizacion_id`),
  ADD KEY `ind_cotizacion_detalles_producto` (`producto_id`),
  ADD KEY `ind_cotizacion_detalles_variante` (`variante_id`),
  ADD KEY `cf_cotizacion_detalles_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_devoluciones`
--
ALTER TABLE `detalles_devoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_devolucion_detalles_devolucion` (`devolucion_id`),
  ADD KEY `ind_devolucion_detalles_dispositivo` (`dispositivo_id`),
  ADD KEY `cf_devolucion_detalles_venta` (`detalle_venta_id`),
  ADD KEY `cf_devolucion_detalles_producto` (`producto_id`),
  ADD KEY `cf_devolucion_detalles_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_documentos_electronicos`
--
ALTER TABLE `detalles_documentos_electronicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_documento_detalles_documento` (`documento_id`),
  ADD KEY `ind_documento_detalles_venta_linea` (`detalle_venta_id`);

--
-- Indices de la tabla `detalles_envios_pedidos`
--
ALTER TABLE `detalles_envios_pedidos`
  ADD PRIMARY KEY (`envio_pedido_id`,`detalle_pedido_id`),
  ADD KEY `ind_envio_detalle_pedido` (`detalle_pedido_id`,`pedido_id`),
  ADD KEY `cf_envio_detalles_envio` (`envio_pedido_id`,`pedido_id`);

--
-- Indices de la tabla `detalles_guias_remision`
--
ALTER TABLE `detalles_guias_remision`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_guia_detalles_guia` (`guia_id`),
  ADD KEY `cf_guia_detalles_venta` (`detalle_venta_id`),
  ADD KEY `cf_guia_detalles_producto` (`producto_id`),
  ADD KEY `cf_guia_remision_detalles_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_detalle_pedido_id_pedido` (`id`,`pedido_id`),
  ADD KEY `ind_detalle_pedidos_pedido` (`pedido_id`),
  ADD KEY `ind_detalle_pedidos_producto` (`producto_id`),
  ADD KEY `ind_detalle_pedidos_variante` (`variante_id`),
  ADD KEY `cf_detalle_pedidos_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_transferencias_existencias`
--
ALTER TABLE `detalles_transferencias_existencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_transferencia_detalle_variante` (`transferencia_id`,`variante_id`),
  ADD UNIQUE KEY `un_transferencia_producto_modo` (`transferencia_id`,`producto_id`,`variante_presente`,`variante_clave`),
  ADD KEY `ind_transferencia_detalle_producto` (`producto_id`,`variante_id`),
  ADD KEY `cf_transferencia_existencias_detalles_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_ventas`
--
ALTER TABLE `detalles_ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_venta_detalle_pedido_linea` (`detalle_pedido_id`),
  ADD KEY `ind_venta_detalles_venta` (`venta_id`),
  ADD KEY `ind_venta_detalles_producto_variante` (`producto_id`,`variante_id`),
  ADD KEY `cf_venta_detalles_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `detalles_ventas_dispositivos`
--
ALTER TABLE `detalles_ventas_dispositivos`
  ADD PRIMARY KEY (`detalle_venta_id`,`dispositivo_id`),
  ADD UNIQUE KEY `un_venta_dispositivo` (`dispositivo_id`);

--
-- Indices de la tabla `devoluciones_garantias`
--
ALTER TABLE `devoluciones_garantias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_devoluciones_codigo` (`codigo`),
  ADD KEY `ind_devoluciones_estado_fecha` (`estado`,`fecha_solicitud`),
  ADD KEY `ind_devoluciones_tipo` (`tipo`),
  ADD KEY `ind_devoluciones_fecha_limite` (`fecha_limite`),
  ADD KEY `ind_devoluciones_activo` (`activo`),
  ADD KEY `ind_devoluciones_creador` (`creado_por_usuario_id`),
  ADD KEY `ind_devoluciones_pedido` (`pedido_id`),
  ADD KEY `ind_devoluciones_cliente` (`cliente_id`),
  ADD KEY `ind_devoluciones_producto` (`producto_id`),
  ADD KEY `ind_devoluciones_venta` (`venta_id`),
  ADD KEY `ind_devoluciones_empresa_fecha` (`empresa_id`,`fecha_solicitud`),
  ADD KEY `cf_devoluciones_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `ind_devoluciones_fecha_solicitud` (`fecha_solicitud`);

--
-- Indices de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_direcciones_cliente_id` (`cliente_id`,`id`),
  ADD KEY `ind_direcciones_cliente_activa` (`cliente_id`,`activa`,`es_predeterminada`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_dispositivos_imei` (`imei`),
  ADD UNIQUE KEY `un_dispositivos_serie` (`numero_serie`),
  ADD KEY `ind_dispositivos_variante_estado` (`variante_id`,`estado`),
  ADD KEY `ind_dispositivos_almacen_estado` (`almacen_actual_id`,`estado`),
  ADD KEY `cf_dispositivos_compra_detalle` (`detalle_compra_id`),
  ADD KEY `cf_dispositivos_producto` (`producto_id`),
  ADD KEY `cf_dispositivos_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `documentos_electronicos`
--
ALTER TABLE `documentos_electronicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_documento_numero` (`empresa_id`,`tipo_documento_id`,`serie`,`correlativo`),
  ADD KEY `ind_documento_sede_estado_fecha` (`sede_id`,`estado`,`fecha_emision`),
  ADD KEY `ind_documento_venta` (`venta_id`),
  ADD KEY `ind_documento_referencia` (`documento_referencia_id`),
  ADD KEY `cf_documentos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_documentos_tipo` (`tipo_documento_id`),
  ADD KEY `cf_documentos_usuario` (`creado_por_usuario_id`),
  ADD KEY `cf_documentos_serie_contexto` (`serie_documento_id`,`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`),
  ADD KEY `cf_documentos_venta_ambito` (`venta_id`,`empresa_id`,`sede_id`);

--
-- Indices de la tabla `elementos_menu`
--
ALTER TABLE `elementos_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_elementos_menu_codigo` (`menu_id`,`codigo`),
  ADD UNIQUE KEY `un_elementos_menu_id_menu` (`id`,`menu_id`),
  ADD KEY `ind_elementos_menu_padre` (`menu_id`,`padre_id`,`habilitado`,`orden`),
  ADD KEY `ind_elementos_menu_pantalla` (`pantalla_id`),
  ADD KEY `ind_elementos_menu_permiso` (`permiso_id`),
  ADD KEY `cf_elementos_menu_padre_menu` (`padre_id`,`menu_id`),
  ADD KEY `cf_elementos_menu_permiso_pantalla` (`permiso_id`,`pantalla_id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_empresas_codigo` (`codigo`),
  ADD UNIQUE KEY `un_empresas_ruc` (`ruc`),
  ADD KEY `ind_empresas_activo` (`activo`);

--
-- Indices de la tabla `envios_pedidos`
--
ALTER TABLE `envios_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_envios_empresa_codigo` (`empresa_id`,`codigo`),
  ADD UNIQUE KEY `un_envios_id_pedido` (`id`,`pedido_id`),
  ADD UNIQUE KEY `un_envios_guia` (`guia_remision_id`),
  ADD KEY `ind_envios_sede_estado_fecha` (`sede_id`,`estado`,`creado_en`),
  ADD KEY `ind_envios_seguimiento` (`transportista`,`numero_seguimiento`),
  ADD KEY `cf_envios_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_envios_almacen_sede` (`almacen_id`,`sede_id`),
  ADD KEY `cf_envios_usuario` (`creado_por_usuario_id`),
  ADD KEY `cf_envios_pedido_ambito` (`pedido_id`,`empresa_id`,`sede_id`);

--
-- Indices de la tabla `envios_sunat`
--
ALTER TABLE `envios_sunat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_envios_sunat_intento` (`documento_id`,`numero_intento`),
  ADD KEY `ind_envios_sunat_estado_fecha` (`estado`,`creado_en`);

--
-- Indices de la tabla `eventos_campana`
--
ALTER TABLE `eventos_campana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_eventos_campana_usuario` (`usuario_id`),
  ADD KEY `ind_eventos_campana_sesion` (`resumen_sesion`),
  ADD KEY `ind_eventos_campana_tipo` (`tipo`),
  ADD KEY `ind_eventos_campana_campana_tipo_fecha` (`campana_id`,`tipo`,`creado_en`),
  ADD KEY `ind_eventos_campana_pedido` (`pedido_id`);

--
-- Indices de la tabla `eventos_casos_soporte`
--
ALTER TABLE `eventos_casos_soporte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_caso_eventos_fecha` (`caso_soporte_id`,`creado_en`),
  ADD KEY `cf_caso_eventos_usuario` (`usuario_id`);

--
-- Indices de la tabla `eventos_envios_pedidos`
--
ALTER TABLE `eventos_envios_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_eventos_envio_externo` (`envio_pedido_id`,`fuente`,`identificador_externo`),
  ADD KEY `ind_eventos_envio_fecha` (`envio_pedido_id`,`ocurrido_en`);

--
-- Indices de la tabla `eventos_integracion`
--
ALTER TABLE `eventos_integracion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_eventos_integracion_estado_fecha` (`integracion_id`,`estado`,`iniciado_en`),
  ADD KEY `ind_eventos_integracion_correlacion` (`correlacion_id`);

--
-- Indices de la tabla `existencias_almacen`
--
ALTER TABLE `existencias_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_existencias_almacen_variante` (`almacen_id`,`variante_id`),
  ADD UNIQUE KEY `un_existencias_producto_modo` (`almacen_id`,`producto_id`,`variante_presente`,`variante_clave`),
  ADD KEY `ind_existencias_variante_almacen` (`variante_id`,`almacen_id`),
  ADD KEY `cf_existencias_almacen_sede` (`almacen_id`,`sede_id`),
  ADD KEY `cf_existencias_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_existencias_variante_producto` (`variante_id`,`producto_id`),
  ADD KEY `cf_existencias_producto_empresa` (`producto_id`,`empresa_id`);

--
-- Indices de la tabla `guias_remision`
--
ALTER TABLE `guias_remision`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_guias_empresa_sede_numero` (`empresa_id`,`sede_id`,`serie`,`correlativo`),
  ADD KEY `ind_guias_sede_estado_fecha` (`sede_id`,`estado`,`creado_en`),
  ADD KEY `cf_guias_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_guias_almacen_sede` (`almacen_origen_id`,`sede_id`),
  ADD KEY `cf_guias_usuario` (`creado_por_usuario_id`),
  ADD KEY `cf_guias_venta_ambito` (`venta_id`,`empresa_id`,`sede_id`);

--
-- Indices de la tabla `historial_migraciones_origen`
--
ALTER TABLE `historial_migraciones_origen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_migracion_origen_id` (`origen_tabla`,`origen_id`),
  ADD UNIQUE KEY `un_historial_migracion_heredada` (`nombre_migracion_heredada`),
  ADD KEY `ind_migracion_origen_nombre` (`origen_tabla`,`nombre_migracion`),
  ADD KEY `ind_migracion_nombre_lote` (`nombre_migracion`,`lote`);

--
-- Indices de la tabla `imagenes_variantes`
--
ALTER TABLE `imagenes_variantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_variante_imagenes_variante` (`variante_id`);

--
-- Indices de la tabla `integraciones`
--
ALTER TABLE `integraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_integraciones_empresa_codigo` (`empresa_id`,`codigo`),
  ADD KEY `ind_integraciones_empresa_activa` (`empresa_id`,`activa`);

--
-- Indices de la tabla `intentos_pago`
--
ALTER TABLE `intentos_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_intentos_pago_idempotencia` (`empresa_id`,`clave_idempotencia`),
  ADD UNIQUE KEY `un_intentos_pago_pago` (`pago_id`),
  ADD KEY `ind_intentos_pago_pedido_estado` (`pedido_id`,`estado`,`solicitado_en`),
  ADD KEY `ind_intentos_pago_proveedor_referencia` (`proveedor`,`referencia_externa`),
  ADD KEY `cf_intentos_pago_medio` (`medio_pago_id`);

--
-- Indices de la tabla `lineas_asientos`
--
ALTER TABLE `lineas_asientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_asiento_lineas_asiento` (`asiento_id`),
  ADD KEY `ind_asiento_lineas_cuenta` (`cuenta_id`,`asiento_id`);

--
-- Indices de la tabla `logistica_mayorista`
--
ALTER TABLE `logistica_mayorista`
  ADD PRIMARY KEY (`pedido_id`);

--
-- Indices de la tabla `lotes_trabajos`
--
ALTER TABLE `lotes_trabajos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `medios_pago`
--
ALTER TABLE `medios_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_medios_pago_codigo` (`codigo`),
  ADD KEY `ind_medios_pago_activo` (`activo`,`nombre`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_mensajes_contacto_estado_fecha` (`estado`,`creado_en`);

--
-- Indices de la tabla `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_menus_codigo` (`codigo`),
  ADD KEY `ind_menus_contexto_habilitado` (`contexto`,`habilitado`);

--
-- Indices de la tabla `migraciones_laravel`
--
ALTER TABLE `migraciones_laravel`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_modulos_codigo` (`codigo`),
  ADD KEY `ind_modulos_habilitado_orden` (`habilitado`,`orden`);

--
-- Indices de la tabla `movimientos_bancarios`
--
ALTER TABLE `movimientos_bancarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_movimientos_bancarios_cuenta_fecha` (`cuenta_bancaria_id`,`ocurrido_en`),
  ADD KEY `ind_movimientos_bancarios_conciliacion` (`cuenta_bancaria_id`,`conciliado`,`ocurrido_en`),
  ADD KEY `cf_movimientos_bancarios_pago` (`pago_id`);

--
-- Indices de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_movimientos_caja_sesion_fecha` (`sesion_caja_id`,`creado_en`),
  ADD KEY `ind_movimientos_caja_pago` (`pago_id`),
  ADD KEY `cf_movimientos_caja_usuario` (`creado_por_usuario_id`);

--
-- Indices de la tabla `movimientos_dispositivos`
--
ALTER TABLE `movimientos_dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_dispositivo_movimiento` (`movimiento_inventario_id`,`dispositivo_id`),
  ADD KEY `ind_dispositivo_movimientos_fecha` (`dispositivo_id`,`creado_en`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_movimientos_producto_fecha` (`producto_id`,`creado_en`),
  ADD KEY `ind_movimientos_usuario` (`usuario_id`),
  ADD KEY `ind_movimientos_sede_fecha` (`sede_id`,`creado_en`),
  ADD KEY `ind_movimientos_almacen_variante_fecha` (`almacen_id`,`variante_id`,`creado_en`),
  ADD KEY `ind_movimientos_documento` (`documento_tipo`,`documento_id`),
  ADD KEY `ind_movimientos_variante` (`variante_id`),
  ADD KEY `cf_movimientos_empresa` (`empresa_id`),
  ADD KEY `cf_movimientos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_movimientos_almacen_sede` (`almacen_id`,`sede_id`),
  ADD KEY `cf_movimientos_producto_empresa` (`producto_id`,`empresa_id`),
  ADD KEY `cf_movimientos_inventario_variante_producto` (`variante_id`,`producto_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_notificaciones_notificable` (`tipo_notificable`,`notificable_id`),
  ADD KEY `ind_notificaciones_lectura` (`tipo_notificable`,`notificable_id`,`leido_en`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_pagos_empresa_codigo` (`empresa_id`,`codigo`),
  ADD KEY `ind_pagos_sede_tipo_estado_fecha` (`sede_id`,`tipo`,`estado`,`fecha_pago`),
  ADD KEY `ind_pagos_medio_fecha` (`medio_pago_id`,`fecha_pago`),
  ADD KEY `ind_pagos_pedido_fecha` (`pedido_id`,`fecha_pago`),
  ADD KEY `ind_pagos_venta_fecha` (`venta_id`,`fecha_pago`),
  ADD KEY `ind_pagos_compra_fecha` (`compra_id`,`fecha_pago`),
  ADD KEY `cf_pagos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_pagos_sesion_caja` (`sesion_caja_id`),
  ADD KEY `cf_pagos_usuario` (`usuario_id`),
  ADD KEY `cf_pagos_venta_ambito` (`venta_id`,`empresa_id`,`sede_id`),
  ADD KEY `cf_pagos_pedido_ambito` (`pedido_id`,`empresa_id`,`sede_id`),
  ADD KEY `cf_pagos_compra_ambito` (`compra_id`,`empresa_id`,`sede_id`),
  ADD KEY `cf_pagos_banco_empresa` (`cuenta_bancaria_id`,`empresa_id`);

--
-- Indices de la tabla `pantallas`
--
ALTER TABLE `pantallas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_pantallas_codigo` (`codigo`),
  ADD UNIQUE KEY `un_pantallas_id_modulo` (`id`,`modulo_id`),
  ADD KEY `ind_pantallas_modulo_habilitada` (`modulo_id`,`habilitada`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_pedidos_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
  ADD KEY `ind_pedidos_usuario_fecha` (`usuario_id`,`creado_en`),
  ADD KEY `ind_pedidos_estado_fecha` (`estado`,`creado_en`),
  ADD KEY `ind_pedidos_cliente_fecha` (`cliente_id`,`creado_en`),
  ADD KEY `ind_pedidos_empresa_fecha` (`empresa_id`,`creado_en`),
  ADD KEY `ind_pedidos_sede_estado_fecha` (`sede_id`,`estado`,`creado_en`),
  ADD KEY `ind_pedidos_almacen` (`almacen_id`),
  ADD KEY `cf_pedidos_cotizacion` (`cotizacion_id`),
  ADD KEY `cf_pedidos_cupon` (`cupon_id`),
  ADD KEY `cf_pedidos_direccion_entrega` (`cliente_id`,`direccion_entrega_id`),
  ADD KEY `cf_pedidos_direccion_facturacion` (`cliente_id`,`direccion_facturacion_id`),
  ADD KEY `cf_pedidos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_pedidos_almacen_sede` (`almacen_id`,`sede_id`),
  ADD KEY `cf_pedidos_cliente_empresa` (`cliente_id`,`empresa_id`);

--
-- Indices de la tabla `perfiles_inteligentes_productos`
--
ALTER TABLE `perfiles_inteligentes_productos`
  ADD PRIMARY KEY (`producto_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_permisos_codigo` (`codigo`),
  ADD UNIQUE KEY `un_permisos_id_pantalla` (`id`,`pantalla_id`),
  ADD KEY `ind_permisos_modulo_accion_activo` (`modulo_id`,`accion`,`activo`),
  ADD KEY `ind_permisos_pantalla` (`pantalla_id`),
  ADD KEY `cf_permisos_pantalla_modulo` (`pantalla_id`,`modulo_id`);

--
-- Indices de la tabla `planes_cuentas`
--
ALTER TABLE `planes_cuentas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_plan_cuentas_empresa_codigo` (`empresa_id`,`codigo`),
  ADD UNIQUE KEY `un_plan_cuentas_id_empresa` (`id`,`empresa_id`),
  ADD KEY `ind_plan_cuentas_padre` (`cuenta_padre_id`),
  ADD KEY `cf_plan_cuentas_padre_empresa` (`cuenta_padre_id`,`empresa_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_productos_id_empresa` (`id`,`empresa_id`),
  ADD KEY `ind_productos_marca` (`marca`),
  ADD KEY `ind_productos_nombre` (`nombre`),
  ADD KEY `ind_productos_activo` (`activo`),
  ADD KEY `ind_productos_catalogo` (`activo`,`categoria_id`,`precio`),
  ADD KEY `ind_productos_categoria` (`categoria_id`),
  ADD KEY `ind_productos_empresa` (`empresa_id`),
  ADD KEY `cf_productos_categoria_empresa` (`categoria_id`,`empresa_id`),
  ADD KEY `ind_productos_catalogo_heredado` (`activo`,`categoria`,`marca`,`id`);

--
-- Indices de la tabla `productos_proveedores`
--
ALTER TABLE `productos_proveedores`
  ADD PRIMARY KEY (`producto_id`,`proveedor_id`),
  ADD KEY `ind_producto_proveedor_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_proveedores_ruc` (`ruc`),
  ADD UNIQUE KEY `un_proveedores_id_empresa` (`id`,`empresa_id`),
  ADD KEY `ind_proveedores_activo_nombre` (`activo`,`nombre`),
  ADD KEY `ind_proveedores_empresa` (`empresa_id`);

--
-- Indices de la tabla `reembolsos`
--
ALTER TABLE `reembolsos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_reembolsos_pago` (`pago_id`),
  ADD KEY `ind_reembolsos_devolucion_estado` (`devolucion_id`,`estado`),
  ADD KEY `cf_reembolsos_autorizador` (`autorizado_por_usuario_id`);

--
-- Indices de la tabla `referencias_imagenes_productos`
--
ALTER TABLE `referencias_imagenes_productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_producto_imagen_ruta` (`producto_id`,`ruta_imagen`),
  ADD KEY `ind_producto_imagen_orden` (`producto_id`,`orden`,`id`);

--
-- Indices de la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_auditoria_evento_identificador_universal` (`identificador_universal_evento`),
  ADD KEY `ind_auditoria_usuario_fecha` (`usuario_id`,`creado_en`),
  ADD KEY `ind_auditoria_entidad` (`entidad`,`entidad_id`),
  ADD KEY `ind_auditoria_ocurrido` (`ocurrido_en`),
  ADD KEY `ind_auditoria_tabla_entidad_fecha` (`tabla_afectada`,`identificador_entidad`,`ocurrido_en`),
  ADD KEY `ind_auditoria_sede_fecha` (`sede_id`,`ocurrido_en`),
  ADD KEY `ind_auditoria_rol` (`rol_codigo`),
  ADD KEY `ind_auditoria_modulo` (`modulo`),
  ADD KEY `ind_auditoria_resultado` (`resultado`),
  ADD KEY `ind_auditoria_correlacion` (`correlacion_id`),
  ADD KEY `cf_auditoria_empresa` (`empresa_id`),
  ADD KEY `cf_auditoria_sede_empresa` (`sede_id`,`empresa_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_roles_codigo` (`codigo`),
  ADD UNIQUE KEY `un_roles_id_alcance` (`id`,`alcance_permitido`),
  ADD KEY `ind_roles_activo_sistema` (`activo`,`es_sistema`);

--
-- Indices de la tabla `roles_menus`
--
ALTER TABLE `roles_menus`
  ADD PRIMARY KEY (`rol_id`,`menu_id`),
  ADD KEY `ind_roles_menus_menu_rol` (`menu_id`,`rol_id`);

--
-- Indices de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD PRIMARY KEY (`rol_id`,`permiso_id`,`alcance`),
  ADD KEY `ind_rol_permiso_permiso_rol` (`permiso_id`,`rol_id`),
  ADD KEY `cf_rol_permiso_alcance` (`rol_id`,`alcance`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_sedes_empresa_codigo` (`empresa_id`,`codigo`),
  ADD UNIQUE KEY `un_sedes_id_empresa` (`id`,`empresa_id`),
  ADD KEY `ind_sedes_empresa_activa` (`empresa_id`,`activa`);

--
-- Indices de la tabla `segmentos_clientes`
--
ALTER TABLE `segmentos_clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_segmentos_nombre` (`nombre`),
  ADD KEY `ind_segmentos_activo` (`activo`);

--
-- Indices de la tabla `series_documentos`
--
ALTER TABLE `series_documentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_series_documento` (`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`),
  ADD UNIQUE KEY `un_series_documento_contexto` (`id`,`empresa_id`,`sede_id`),
  ADD UNIQUE KEY `un_series_contexto_tipo_serie` (`id`,`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`),
  ADD KEY `ind_series_documento_activa` (`sede_id`,`activa`,`tipo_documento_id`),
  ADD KEY `cf_series_documentos_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_series_documentos_tipo` (`tipo_documento_id`);

--
-- Indices de la tabla `sesiones_aplicacion`
--
ALTER TABLE `sesiones_aplicacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_sesiones_aplicacion_usuario` (`usuario_id`),
  ADD KEY `ind_sesiones_aplicacion_actividad` (`ultima_actividad`);

--
-- Indices de la tabla `sesiones_caja`
--
ALTER TABLE `sesiones_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_sesiones_caja_estado_fecha` (`caja_id`,`estado`,`abierta_en`),
  ADD KEY `ind_sesiones_caja_usuario_fecha` (`usuario_apertura_id`,`abierta_en`),
  ADD KEY `cf_sesiones_caja_cierre` (`usuario_cierre_id`);

--
-- Indices de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_sesiones_usuario_resumen` (`resumen_identificador`),
  ADD KEY `ind_sesiones_usuario_actividad` (`usuario_id`,`revocada_en`,`ultimo_acceso`);

--
-- Indices de la tabla `tipos_documento_electronico`
--
ALTER TABLE `tipos_documento_electronico`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `ind_tipos_documento_activo` (`activo`,`nombre`);

--
-- Indices de la tabla `tokens_recuperacion_contrasena`
--
ALTER TABLE `tokens_recuperacion_contrasena`
  ADD PRIMARY KEY (`correo`);

--
-- Indices de la tabla `trabajos_fallidos`
--
ALTER TABLE `trabajos_fallidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_trabajos_fallidos_identificador_universal` (`identificador_universal`),
  ADD KEY `ind_trabajos_fallidos_conexion_cola_fecha` (`conexion`,`cola`,`fallido_en`);

--
-- Indices de la tabla `trabajos_pendientes`
--
ALTER TABLE `trabajos_pendientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ind_trabajos_cola` (`cola`);

--
-- Indices de la tabla `transferencias_existencias`
--
ALTER TABLE `transferencias_existencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_transferencias_empresa_codigo` (`empresa_id`,`codigo`),
  ADD KEY `ind_transferencias_origen_estado` (`sede_origen_id`,`estado`,`creado_en`),
  ADD KEY `ind_transferencias_destino_estado` (`sede_destino_id`,`estado`,`creado_en`),
  ADD KEY `cf_transferencias_sede_origen_empresa` (`sede_origen_id`,`empresa_id`),
  ADD KEY `cf_transferencias_almacen_origen_sede` (`almacen_origen_id`,`sede_origen_id`),
  ADD KEY `cf_transferencias_sede_destino_empresa` (`sede_destino_id`,`empresa_id`),
  ADD KEY `cf_transferencias_almacen_destino_sede` (`almacen_destino_id`,`sede_destino_id`),
  ADD KEY `cf_transferencias_solicitante` (`solicitado_por_usuario_id`),
  ADD KEY `cf_transferencias_aprobador` (`aprobado_por_usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_usuarios_correo` (`correo`),
  ADD KEY `ind_usuarios_rol` (`rol`),
  ADD KEY `ind_usuarios_activo_rol` (`activo`,`rol`);

--
-- Indices de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`usuario_id`,`rol_id`),
  ADD KEY `ind_usuario_roles_rol_activo` (`rol_id`,`activo`,`usuario_id`),
  ADD KEY `cf_usuario_roles_asignador` (`asignado_por_usuario_id`);

--
-- Indices de la tabla `usuarios_sedes_roles`
--
ALTER TABLE `usuarios_sedes_roles`
  ADD PRIMARY KEY (`usuario_id`,`sede_id`,`rol_id`),
  ADD KEY `ind_usuario_sede_roles_sede_rol_activo` (`sede_id`,`rol_id`,`activo`),
  ADD KEY `ind_usuario_sede_roles_usuario_activo` (`usuario_id`,`activo`),
  ADD KEY `cf_usuario_sede_roles_asignador` (`asignado_por_usuario_id`),
  ADD KEY `cf_usuario_sede_rol_alcance` (`rol_id`,`alcance_asignacion`);

--
-- Indices de la tabla `variantes_productos`
--
ALTER TABLE `variantes_productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_variantes_id_producto` (`id`,`producto_id`),
  ADD UNIQUE KEY `un_variantes_sku` (`codigo_sku`),
  ADD UNIQUE KEY `un_variantes_codigo_barras` (`codigo_barras`),
  ADD KEY `ind_variantes_producto` (`producto_id`),
  ADD KEY `ind_variantes_producto_estado` (`producto_id`,`estado`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `un_ventas_empresa_codigo` (`empresa_id`,`codigo`),
  ADD UNIQUE KEY `un_ventas_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
  ADD UNIQUE KEY `un_ventas_pedido` (`pedido_id`),
  ADD KEY `ind_ventas_sede_estado_fecha` (`sede_id`,`estado`,`fecha_venta`),
  ADD KEY `ind_ventas_cliente_fecha` (`cliente_id`,`fecha_venta`),
  ADD KEY `ind_ventas_caja_fecha` (`sesion_caja_id`,`fecha_venta`),
  ADD KEY `cf_ventas_sede_empresa` (`sede_id`,`empresa_id`),
  ADD KEY `cf_ventas_almacen_sede` (`almacen_id`,`sede_id`),
  ADD KEY `cf_ventas_usuario` (`usuario_id`),
  ADD KEY `cf_ventas_cliente_empresa` (`cliente_id`,`empresa_id`),
  ADD KEY `cf_ventas_pedido_ambito` (`pedido_id`,`empresa_id`,`sede_id`);

--
-- Indices de la tabla `versiones_inicializacion_control_acceso`
--
ALTER TABLE `versiones_inicializacion_control_acceso`
  ADD PRIMARY KEY (`codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `almacenes`
--
ALTER TABLE `almacenes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `archivos_documentales`
--
ALTER TABLE `archivos_documentales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asientos_contables`
--
ALTER TABLE `asientos_contables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `campanas_publicitarias`
--
ALTER TABLE `campanas_publicitarias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caracteristicas_productos`
--
ALTER TABLE `caracteristicas_productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carritos`
--
ALTER TABLE `carritos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `casos_soporte`
--
ALTER TABLE `casos_soporte`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas_bancarias`
--
ALTER TABLE `cuentas_bancarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas_por_cobrar`
--
ALTER TABLE `cuentas_por_cobrar`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas_por_pagar`
--
ALTER TABLE `cuentas_por_pagar`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_carritos`
--
ALTER TABLE `detalles_carritos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_compras`
--
ALTER TABLE `detalles_compras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_cotizaciones`
--
ALTER TABLE `detalles_cotizaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_devoluciones`
--
ALTER TABLE `detalles_devoluciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_documentos_electronicos`
--
ALTER TABLE `detalles_documentos_electronicos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_guias_remision`
--
ALTER TABLE `detalles_guias_remision`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_transferencias_existencias`
--
ALTER TABLE `detalles_transferencias_existencias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_ventas`
--
ALTER TABLE `detalles_ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `devoluciones_garantias`
--
ALTER TABLE `devoluciones_garantias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_electronicos`
--
ALTER TABLE `documentos_electronicos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `elementos_menu`
--
ALTER TABLE `elementos_menu`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `envios_pedidos`
--
ALTER TABLE `envios_pedidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `envios_sunat`
--
ALTER TABLE `envios_sunat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_campana`
--
ALTER TABLE `eventos_campana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_casos_soporte`
--
ALTER TABLE `eventos_casos_soporte`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_envios_pedidos`
--
ALTER TABLE `eventos_envios_pedidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_integracion`
--
ALTER TABLE `eventos_integracion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `existencias_almacen`
--
ALTER TABLE `existencias_almacen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `guias_remision`
--
ALTER TABLE `guias_remision`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_migraciones_origen`
--
ALTER TABLE `historial_migraciones_origen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `imagenes_variantes`
--
ALTER TABLE `imagenes_variantes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `integraciones`
--
ALTER TABLE `integraciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `intentos_pago`
--
ALTER TABLE `intentos_pago`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineas_asientos`
--
ALTER TABLE `lineas_asientos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `medios_pago`
--
ALTER TABLE `medios_pago`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migraciones_laravel`
--
ALTER TABLE `migraciones_laravel`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_bancarios`
--
ALTER TABLE `movimientos_bancarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_dispositivos`
--
ALTER TABLE `movimientos_dispositivos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pantallas`
--
ALTER TABLE `pantallas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planes_cuentas`
--
ALTER TABLE `planes_cuentas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reembolsos`
--
ALTER TABLE `reembolsos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `referencias_imagenes_productos`
--
ALTER TABLE `referencias_imagenes_productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `segmentos_clientes`
--
ALTER TABLE `segmentos_clientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `series_documentos`
--
ALTER TABLE `series_documentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones_caja`
--
ALTER TABLE `sesiones_caja`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajos_fallidos`
--
ALTER TABLE `trabajos_fallidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajos_pendientes`
--
ALTER TABLE `trabajos_pendientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `transferencias_existencias`
--
ALTER TABLE `transferencias_existencias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `variantes_productos`
--
ALTER TABLE `variantes_productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `almacenes`
--
ALTER TABLE `almacenes`
  ADD CONSTRAINT `cf_almacenes_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`);

--
-- Filtros para la tabla `aplicaciones_cobro`
--
ALTER TABLE `aplicaciones_cobro`
  ADD CONSTRAINT `cf_aplicaciones_cobro_cuenta` FOREIGN KEY (`cuenta_cobrar_id`) REFERENCES `cuentas_por_cobrar` (`id`),
  ADD CONSTRAINT `cf_aplicaciones_cobro_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`);

--
-- Filtros para la tabla `aplicaciones_pago`
--
ALTER TABLE `aplicaciones_pago`
  ADD CONSTRAINT `cf_aplicaciones_pago_cuenta` FOREIGN KEY (`cuenta_pagar_id`) REFERENCES `cuentas_por_pagar` (`id`),
  ADD CONSTRAINT `cf_aplicaciones_pago_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`);

--
-- Filtros para la tabla `archivos_documentales`
--
ALTER TABLE `archivos_documentales`
  ADD CONSTRAINT `cf_archivos_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  ADD CONSTRAINT `cf_archivos_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones_garantias` (`id`),
  ADD CONSTRAINT `cf_archivos_documento` FOREIGN KEY (`documento_electronico_id`) REFERENCES `documentos_electronicos` (`id`),
  ADD CONSTRAINT `cf_archivos_guia` FOREIGN KEY (`guia_id`) REFERENCES `guias_remision` (`id`),
  ADD CONSTRAINT `cf_archivos_usuario` FOREIGN KEY (`cargado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asientos_contables`
--
ALTER TABLE `asientos_contables`
  ADD CONSTRAINT `cf_asientos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_asientos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_asientos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_asientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD CONSTRAINT `cf_cajas_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`);

--
-- Filtros para la tabla `campanas_publicitarias`
--
ALTER TABLE `campanas_publicitarias`
  ADD CONSTRAINT `cf_campanas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `campanas_segmentos`
--
ALTER TABLE `campanas_segmentos`
  ADD CONSTRAINT `cf_campana_segmento_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas_publicitarias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cf_campana_segmento_segmento` FOREIGN KEY (`segmento_id`) REFERENCES `segmentos_clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `caracteristicas_productos`
--
ALTER TABLE `caracteristicas_productos`
  ADD CONSTRAINT `cf_producto_caracteristicas_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD CONSTRAINT `cf_carritos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cf_carritos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_carritos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `cf_carritos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_carritos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_carritos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `casos_soporte`
--
ALTER TABLE `casos_soporte`
  ADD CONSTRAINT `cf_casos_asignado` FOREIGN KEY (`asignado_a_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_casos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_casos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_casos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_casos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`);

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `cf_categorias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `cf_clientes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_clientes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `cf_compras_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_compras_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_compras_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_compras_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_compras_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `cf_compras_proveedor_empresa` FOREIGN KEY (`proveedor_id`,`empresa_id`) REFERENCES `proveedores` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_compras_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_compras_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`);

--
-- Filtros para la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `cf_contratos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cf_contratos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_contratos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `cf_contratos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_contratos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_contratos_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cf_cotizaciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cf_cotizaciones_cliente_empresa` FOREIGN KEY (`cliente_id`,`empresa_id`) REFERENCES `clientes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_cotizaciones_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_cotizaciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_cotizaciones_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_cotizaciones_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`);

--
-- Filtros para la tabla `cuentas_bancarias`
--
ALTER TABLE `cuentas_bancarias`
  ADD CONSTRAINT `cf_cuentas_bancarias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `cuentas_por_cobrar`
--
ALTER TABLE `cuentas_por_cobrar`
  ADD CONSTRAINT `cf_cuentas_cobrar_documento` FOREIGN KEY (`documento_electronico_id`) REFERENCES `documentos_electronicos` (`id`);

--
-- Filtros para la tabla `cuentas_por_pagar`
--
ALTER TABLE `cuentas_por_pagar`
  ADD CONSTRAINT `cf_cuentas_pagar_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`);

--
-- Filtros para la tabla `detalles_carritos`
--
ALTER TABLE `detalles_carritos`
  ADD CONSTRAINT `cf_carrito_detalles_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cf_carrito_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_carrito_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_carrito_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `detalles_compras`
--
ALTER TABLE `detalles_compras`
  ADD CONSTRAINT `cf_compra_detalles_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `cf_compra_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_compra_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_compra_detalles_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `detalles_compras_dispositivos`
--
ALTER TABLE `detalles_compras_dispositivos`
  ADD CONSTRAINT `cf_compra_dispositivo_detalle` FOREIGN KEY (`detalle_compra_id`) REFERENCES `detalles_compras` (`id`),
  ADD CONSTRAINT `cf_compra_dispositivo_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`);

--
-- Filtros para la tabla `detalles_cotizaciones`
--
ALTER TABLE `detalles_cotizaciones`
  ADD CONSTRAINT `cf_cotizacion_detalles_cotizacion` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  ADD CONSTRAINT `cf_cotizacion_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_cotizacion_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_cotizacion_detalles_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `detalles_devoluciones`
--
ALTER TABLE `detalles_devoluciones`
  ADD CONSTRAINT `cf_devolucion_detalles_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones_garantias` (`id`),
  ADD CONSTRAINT `cf_devolucion_detalles_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`),
  ADD CONSTRAINT `cf_devolucion_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_devolucion_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_devolucion_detalles_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  ADD CONSTRAINT `cf_devolucion_detalles_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`);

--
-- Filtros para la tabla `detalles_documentos_electronicos`
--
ALTER TABLE `detalles_documentos_electronicos`
  ADD CONSTRAINT `cf_documento_detalle_documento` FOREIGN KEY (`documento_id`) REFERENCES `documentos_electronicos` (`id`),
  ADD CONSTRAINT `cf_documento_detalle_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalles_envios_pedidos`
--
ALTER TABLE `detalles_envios_pedidos`
  ADD CONSTRAINT `cf_envio_detalles_envio` FOREIGN KEY (`envio_pedido_id`,`pedido_id`) REFERENCES `envios_pedidos` (`id`, `pedido_id`),
  ADD CONSTRAINT `cf_envio_detalles_pedido_detalle` FOREIGN KEY (`detalle_pedido_id`,`pedido_id`) REFERENCES `detalles_pedidos` (`id`, `pedido_id`);

--
-- Filtros para la tabla `detalles_guias_remision`
--
ALTER TABLE `detalles_guias_remision`
  ADD CONSTRAINT `cf_guia_detalles_guia` FOREIGN KEY (`guia_id`) REFERENCES `guias_remision` (`id`),
  ADD CONSTRAINT `cf_guia_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_guia_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_guia_detalles_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_guia_remision_detalles_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  ADD CONSTRAINT `cf_detalle_pedidos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `cf_detalle_pedidos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_detalle_pedidos_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_detalle_pedidos_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `detalles_transferencias_existencias`
--
ALTER TABLE `detalles_transferencias_existencias`
  ADD CONSTRAINT `cf_transferencia_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_transferencia_detalle_transferencia` FOREIGN KEY (`transferencia_id`) REFERENCES `transferencias_existencias` (`id`),
  ADD CONSTRAINT `cf_transferencia_detalle_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_transferencia_existencias_detalles_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `detalles_ventas`
--
ALTER TABLE `detalles_ventas`
  ADD CONSTRAINT `cf_venta_detalles_pedido_detalle` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalles_pedidos` (`id`),
  ADD CONSTRAINT `cf_venta_detalles_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_venta_detalles_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_venta_detalles_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  ADD CONSTRAINT `cf_venta_detalles_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `detalles_ventas_dispositivos`
--
ALTER TABLE `detalles_ventas_dispositivos`
  ADD CONSTRAINT `cf_venta_dispositivo_detalle` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalles_ventas` (`id`),
  ADD CONSTRAINT `cf_venta_dispositivo_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`);

--
-- Filtros para la tabla `devoluciones_garantias`
--
ALTER TABLE `devoluciones_garantias`
  ADD CONSTRAINT `cf_devoluciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cf_devoluciones_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_devoluciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_devoluciones_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `cf_devoluciones_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_devoluciones_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_devoluciones_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_devoluciones_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD CONSTRAINT `cf_direcciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `cf_dispositivos_almacen` FOREIGN KEY (`almacen_actual_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_dispositivos_compra_detalle` FOREIGN KEY (`detalle_compra_id`) REFERENCES `detalles_compras` (`id`),
  ADD CONSTRAINT `cf_dispositivos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_dispositivos_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_dispositivos_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `documentos_electronicos`
--
ALTER TABLE `documentos_electronicos`
  ADD CONSTRAINT `cf_documentos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_documentos_referencia` FOREIGN KEY (`documento_referencia_id`) REFERENCES `documentos_electronicos` (`id`),
  ADD CONSTRAINT `cf_documentos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_documentos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_documentos_serie` FOREIGN KEY (`serie_documento_id`) REFERENCES `series_documentos` (`id`),
  ADD CONSTRAINT `cf_documentos_serie_contexto` FOREIGN KEY (`serie_documento_id`,`empresa_id`,`sede_id`,`tipo_documento_id`,`serie`) REFERENCES `series_documentos` (`id`, `empresa_id`, `sede_id`, `tipo_documento_id`, `serie`),
  ADD CONSTRAINT `cf_documentos_tipo` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento_electronico` (`codigo`),
  ADD CONSTRAINT `cf_documentos_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_documentos_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `cf_documentos_venta_ambito` FOREIGN KEY (`venta_id`,`empresa_id`,`sede_id`) REFERENCES `ventas` (`id`, `empresa_id`, `sede_id`);

--
-- Filtros para la tabla `elementos_menu`
--
ALTER TABLE `elementos_menu`
  ADD CONSTRAINT `cf_elementos_menu_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  ADD CONSTRAINT `cf_elementos_menu_padre` FOREIGN KEY (`padre_id`) REFERENCES `elementos_menu` (`id`),
  ADD CONSTRAINT `cf_elementos_menu_padre_menu` FOREIGN KEY (`padre_id`,`menu_id`) REFERENCES `elementos_menu` (`id`, `menu_id`),
  ADD CONSTRAINT `cf_elementos_menu_pantalla` FOREIGN KEY (`pantalla_id`) REFERENCES `pantallas` (`id`),
  ADD CONSTRAINT `cf_elementos_menu_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`),
  ADD CONSTRAINT `cf_elementos_menu_permiso_pantalla` FOREIGN KEY (`permiso_id`,`pantalla_id`) REFERENCES `permisos` (`id`, `pantalla_id`);

--
-- Filtros para la tabla `envios_pedidos`
--
ALTER TABLE `envios_pedidos`
  ADD CONSTRAINT `cf_envios_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_envios_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_envios_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_envios_guia` FOREIGN KEY (`guia_remision_id`) REFERENCES `guias_remision` (`id`),
  ADD CONSTRAINT `cf_envios_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `cf_envios_pedido_ambito` FOREIGN KEY (`pedido_id`,`empresa_id`,`sede_id`) REFERENCES `pedidos` (`id`, `empresa_id`, `sede_id`),
  ADD CONSTRAINT `cf_envios_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_envios_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_envios_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `envios_sunat`
--
ALTER TABLE `envios_sunat`
  ADD CONSTRAINT `cf_envios_sunat_documento` FOREIGN KEY (`documento_id`) REFERENCES `documentos_electronicos` (`id`);

--
-- Filtros para la tabla `eventos_campana`
--
ALTER TABLE `eventos_campana`
  ADD CONSTRAINT `cf_eventos_campana_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas_publicitarias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cf_eventos_campana_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_eventos_campana_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `eventos_casos_soporte`
--
ALTER TABLE `eventos_casos_soporte`
  ADD CONSTRAINT `cf_caso_eventos_caso` FOREIGN KEY (`caso_soporte_id`) REFERENCES `casos_soporte` (`id`),
  ADD CONSTRAINT `cf_caso_eventos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `eventos_envios_pedidos`
--
ALTER TABLE `eventos_envios_pedidos`
  ADD CONSTRAINT `cf_eventos_envio` FOREIGN KEY (`envio_pedido_id`) REFERENCES `envios_pedidos` (`id`);

--
-- Filtros para la tabla `eventos_integracion`
--
ALTER TABLE `eventos_integracion`
  ADD CONSTRAINT `cf_eventos_integracion_integracion` FOREIGN KEY (`integracion_id`) REFERENCES `integraciones` (`id`);

--
-- Filtros para la tabla `existencias_almacen`
--
ALTER TABLE `existencias_almacen`
  ADD CONSTRAINT `cf_existencias_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_existencias_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_existencias_producto_empresa` FOREIGN KEY (`producto_id`,`empresa_id`) REFERENCES `productos` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_existencias_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_existencias_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`),
  ADD CONSTRAINT `cf_existencias_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`);

--
-- Filtros para la tabla `guias_remision`
--
ALTER TABLE `guias_remision`
  ADD CONSTRAINT `cf_guias_almacen` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_guias_almacen_sede` FOREIGN KEY (`almacen_origen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_guias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_guias_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_guias_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_guias_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_guias_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `cf_guias_venta_ambito` FOREIGN KEY (`venta_id`,`empresa_id`,`sede_id`) REFERENCES `ventas` (`id`, `empresa_id`, `sede_id`);

--
-- Filtros para la tabla `imagenes_variantes`
--
ALTER TABLE `imagenes_variantes`
  ADD CONSTRAINT `cf_variante_imagenes_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `integraciones`
--
ALTER TABLE `integraciones`
  ADD CONSTRAINT `cf_integraciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `intentos_pago`
--
ALTER TABLE `intentos_pago`
  ADD CONSTRAINT `cf_intentos_pago_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_intentos_pago_medio` FOREIGN KEY (`medio_pago_id`) REFERENCES `medios_pago` (`id`),
  ADD CONSTRAINT `cf_intentos_pago_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  ADD CONSTRAINT `cf_intentos_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `lineas_asientos`
--
ALTER TABLE `lineas_asientos`
  ADD CONSTRAINT `cf_asiento_lineas_asiento` FOREIGN KEY (`asiento_id`) REFERENCES `asientos_contables` (`id`),
  ADD CONSTRAINT `cf_asiento_lineas_cuenta` FOREIGN KEY (`cuenta_id`) REFERENCES `planes_cuentas` (`id`);

--
-- Filtros para la tabla `logistica_mayorista`
--
ALTER TABLE `logistica_mayorista`
  ADD CONSTRAINT `cf_logistica_mayorista_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `movimientos_bancarios`
--
ALTER TABLE `movimientos_bancarios`
  ADD CONSTRAINT `cf_movimientos_bancarios_cuenta` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`),
  ADD CONSTRAINT `cf_movimientos_bancarios_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`);

--
-- Filtros para la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD CONSTRAINT `cf_movimientos_caja_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`),
  ADD CONSTRAINT `cf_movimientos_caja_sesion` FOREIGN KEY (`sesion_caja_id`) REFERENCES `sesiones_caja` (`id`),
  ADD CONSTRAINT `cf_movimientos_caja_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimientos_dispositivos`
--
ALTER TABLE `movimientos_dispositivos`
  ADD CONSTRAINT `cf_dispositivo_movimiento_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`),
  ADD CONSTRAINT `cf_dispositivo_movimiento_existencias` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimientos_inventario` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `cf_movimientos_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_movimientos_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_movimientos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_movimientos_inventario_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`, `producto_id`),
  ADD CONSTRAINT `cf_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `cf_movimientos_producto_empresa` FOREIGN KEY (`producto_id`,`empresa_id`) REFERENCES `productos` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_movimientos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_movimientos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_movimientos_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_productos` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `cf_pagos_banco_empresa` FOREIGN KEY (`cuenta_bancaria_id`,`empresa_id`) REFERENCES `cuentas_bancarias` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_pagos_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `cf_pagos_compra_ambito` FOREIGN KEY (`compra_id`,`empresa_id`,`sede_id`) REFERENCES `compras` (`id`, `empresa_id`, `sede_id`),
  ADD CONSTRAINT `cf_pagos_cuenta_bancaria` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`),
  ADD CONSTRAINT `cf_pagos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_pagos_medio` FOREIGN KEY (`medio_pago_id`) REFERENCES `medios_pago` (`id`),
  ADD CONSTRAINT `cf_pagos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `cf_pagos_pedido_ambito` FOREIGN KEY (`pedido_id`,`empresa_id`,`sede_id`) REFERENCES `pedidos` (`id`, `empresa_id`, `sede_id`),
  ADD CONSTRAINT `cf_pagos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_pagos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_pagos_sesion_caja` FOREIGN KEY (`sesion_caja_id`) REFERENCES `sesiones_caja` (`id`),
  ADD CONSTRAINT `cf_pagos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_pagos_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `cf_pagos_venta_ambito` FOREIGN KEY (`venta_id`,`empresa_id`,`sede_id`) REFERENCES `ventas` (`id`, `empresa_id`, `sede_id`);

--
-- Filtros para la tabla `pantallas`
--
ALTER TABLE `pantallas`
  ADD CONSTRAINT `cf_pantallas_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `cf_pedidos_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_pedidos_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_pedidos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cf_pedidos_cliente_empresa` FOREIGN KEY (`cliente_id`,`empresa_id`) REFERENCES `clientes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_pedidos_cotizacion` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  ADD CONSTRAINT `cf_pedidos_cupon` FOREIGN KEY (`cupon_id`) REFERENCES `cupones` (`id`),
  ADD CONSTRAINT `cf_pedidos_direccion_entrega` FOREIGN KEY (`cliente_id`,`direccion_entrega_id`) REFERENCES `direcciones_cliente` (`cliente_id`, `id`),
  ADD CONSTRAINT `cf_pedidos_direccion_facturacion` FOREIGN KEY (`cliente_id`,`direccion_facturacion_id`) REFERENCES `direcciones_cliente` (`cliente_id`, `id`),
  ADD CONSTRAINT `cf_pedidos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_pedidos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_pedidos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `perfiles_inteligentes_productos`
--
ALTER TABLE `perfiles_inteligentes_productos`
  ADD CONSTRAINT `cf_perfil_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `cf_permisos_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`),
  ADD CONSTRAINT `cf_permisos_pantalla` FOREIGN KEY (`pantalla_id`) REFERENCES `pantallas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_permisos_pantalla_modulo` FOREIGN KEY (`pantalla_id`,`modulo_id`) REFERENCES `pantallas` (`id`, `modulo_id`);

--
-- Filtros para la tabla `planes_cuentas`
--
ALTER TABLE `planes_cuentas`
  ADD CONSTRAINT `cf_plan_cuentas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_plan_cuentas_padre` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `planes_cuentas` (`id`),
  ADD CONSTRAINT `cf_plan_cuentas_padre_empresa` FOREIGN KEY (`cuenta_padre_id`,`empresa_id`) REFERENCES `planes_cuentas` (`id`, `empresa_id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `cf_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `cf_productos_categoria_empresa` FOREIGN KEY (`categoria_id`,`empresa_id`) REFERENCES `categorias` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_productos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `productos_proveedores`
--
ALTER TABLE `productos_proveedores`
  ADD CONSTRAINT `cf_producto_proveedor_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cf_producto_proveedor_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `cf_proveedores_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `reembolsos`
--
ALTER TABLE `reembolsos`
  ADD CONSTRAINT `cf_reembolsos_autorizador` FOREIGN KEY (`autorizado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_reembolsos_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones_garantias` (`id`),
  ADD CONSTRAINT `cf_reembolsos_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`);

--
-- Filtros para la tabla `referencias_imagenes_productos`
--
ALTER TABLE `referencias_imagenes_productos`
  ADD CONSTRAINT `cf_producto_imagen_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD CONSTRAINT `cf_auditoria_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_auditoria_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_auditoria_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `roles_menus`
--
ALTER TABLE `roles_menus`
  ADD CONSTRAINT `cf_roles_menus_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  ADD CONSTRAINT `cf_roles_menus_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD CONSTRAINT `cf_rol_permiso_alcance` FOREIGN KEY (`rol_id`,`alcance`) REFERENCES `roles` (`id`, `alcance_permitido`),
  ADD CONSTRAINT `cf_rol_permiso_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`),
  ADD CONSTRAINT `cf_rol_permiso_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD CONSTRAINT `cf_sedes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `series_documentos`
--
ALTER TABLE `series_documentos`
  ADD CONSTRAINT `cf_series_documentos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_series_documentos_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_series_documentos_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_series_documentos_tipo` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento_electronico` (`codigo`);

--
-- Filtros para la tabla `sesiones_aplicacion`
--
ALTER TABLE `sesiones_aplicacion`
  ADD CONSTRAINT `cf_sesiones_aplicacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sesiones_caja`
--
ALTER TABLE `sesiones_caja`
  ADD CONSTRAINT `cf_sesiones_caja_apertura` FOREIGN KEY (`usuario_apertura_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `cf_sesiones_caja_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`),
  ADD CONSTRAINT `cf_sesiones_caja_cierre` FOREIGN KEY (`usuario_cierre_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  ADD CONSTRAINT `cf_sesiones_usuario_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transferencias_existencias`
--
ALTER TABLE `transferencias_existencias`
  ADD CONSTRAINT `cf_transferencias_almacen_destino` FOREIGN KEY (`almacen_destino_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_transferencias_almacen_destino_sede` FOREIGN KEY (`almacen_destino_id`,`sede_destino_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_transferencias_almacen_origen` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_transferencias_almacen_origen_sede` FOREIGN KEY (`almacen_origen_id`,`sede_origen_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_transferencias_aprobador` FOREIGN KEY (`aprobado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_transferencias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_transferencias_sede_destino` FOREIGN KEY (`sede_destino_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_transferencias_sede_destino_empresa` FOREIGN KEY (`sede_destino_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_transferencias_sede_origen` FOREIGN KEY (`sede_origen_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_transferencias_sede_origen_empresa` FOREIGN KEY (`sede_origen_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_transferencias_solicitante` FOREIGN KEY (`solicitado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD CONSTRAINT `cf_usuario_roles_asignador` FOREIGN KEY (`asignado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_usuario_roles_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `cf_usuario_roles_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios_sedes_roles`
--
ALTER TABLE `usuarios_sedes_roles`
  ADD CONSTRAINT `cf_usuario_sede_rol_alcance` FOREIGN KEY (`rol_id`,`alcance_asignacion`) REFERENCES `roles` (`id`, `alcance_permitido`),
  ADD CONSTRAINT `cf_usuario_sede_roles_asignador` FOREIGN KEY (`asignado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cf_usuario_sede_roles_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `cf_usuario_sede_roles_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_usuario_sede_roles_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `variantes_productos`
--
ALTER TABLE `variantes_productos`
  ADD CONSTRAINT `cf_variantes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `cf_ventas_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `cf_ventas_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`, `sede_id`),
  ADD CONSTRAINT `cf_ventas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cf_ventas_cliente_empresa` FOREIGN KEY (`cliente_id`,`empresa_id`) REFERENCES `clientes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_ventas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `cf_ventas_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `cf_ventas_pedido_ambito` FOREIGN KEY (`pedido_id`,`empresa_id`,`sede_id`) REFERENCES `pedidos` (`id`, `empresa_id`, `sede_id`),
  ADD CONSTRAINT `cf_ventas_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `cf_ventas_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`, `empresa_id`),
  ADD CONSTRAINT `cf_ventas_sesion_caja` FOREIGN KEY (`sesion_caja_id`) REFERENCES `sesiones_caja` (`id`),
  ADD CONSTRAINT `cf_ventas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
