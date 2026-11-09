-- Technology Cell: estructura propuesta de ecommerce.
-- MariaDB 10.4.32. NO ejecutar en produccion sin aprobacion expresa.
-- El DDL de MariaDB hace commit implicito: usar ventana de mantenimiento.
--
-- Precondiciones: md_tecnologia_digital_cell; cero cupones existentes y cero pedidos
-- con cupon_id asignado; estructura base exactamente compatible con md_tecnologia_digital_cell.sql.
-- La rutina de preflight aborta antes del respaldo/DDL si no se cumple alguna condicion.
--
-- Respaldo: conserva las filas de cupones y cualquier referencia de pedido a cupon.
-- Exportar externamente SHOW CREATE TABLE de cupones y pedidos antes de ejecutar.
-- Las tablas respaldo_tc_ecom_20261105_* no se sobrescriben; si ya existen, detenerse.
-- Ejecutar sin la opcion de cliente --force. Si un CALL falla, el cliente debe detener
-- el archivo antes de cualquier DDL. La rutina creada puede retirarse manualmente con
-- DROP PROCEDURE `tc_preflight_ecommerce_20261105`; no se borra ningun objeto previo.
-- La rutina NO crea ni modifica usuarios, permisos, roles ni registros empresariales.

USE `md_tecnologia_digital_cell`;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER //
CREATE PROCEDURE `tc_preflight_ecommerce_20261105`()
BEGIN
    DECLARE cantidad INT DEFAULT 0;
    DECLARE tabla_engine VARCHAR(64) DEFAULT NULL;

    IF DATABASE() <> 'md_tecnologia_digital_cell' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Base activa incorrecta; se requiere md_tecnologia_digital_cell.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema = DATABASE() AND table_name IN ('cupones','pedidos','empresas','productos','variantes_productos','usuarios');
    IF cantidad <> 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta una tabla requerida; comparar con el SQL de referencia.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema = DATABASE() AND table_name IN ('precios_mayoristas','configuraciones_tienda','respaldo_tc_ecom_20261105_cupones','respaldo_tc_ecom_20261105_pedidos_cupon');
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una tabla propuesta o de respaldo ya existe; no se sobrescribira.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema = DATABASE() AND table_name = 'cupones'
        AND ((column_name='id' AND column_type='bigint(20) unsigned')
          OR (column_name='codigo' AND column_type='varchar(50)')
          OR (column_name='inicia_en' AND data_type='datetime')
          OR (column_name='finaliza_en' AND data_type='datetime')
          OR column_name='empresa_id');
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estructura de cupones distinta a la referencia o empresa_id ya presente.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema = DATABASE() AND table_name = 'pedidos'
        AND ((column_name='id' AND column_type='int(10) unsigned')
          OR (column_name='cupon_id' AND column_type='bigint(20) unsigned' AND is_nullable='YES')
          OR (column_name='empresa_id' AND column_type='bigint(20) unsigned')
          OR (column_name='sede_id' AND column_type='bigint(20) unsigned'));
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tipos de pedidos.id/cupon_id/empresa_id/sede_id no compatibles.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema=DATABASE() AND table_name='productos'
        AND ((column_name='id' AND column_type='int(10) unsigned')
          OR (column_name='empresa_id' AND column_type='bigint(20) unsigned'));
    IF cantidad <> 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tipos de productos.id/empresa_id no compatibles.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema=DATABASE() AND table_name='variantes_productos'
        AND ((column_name='id' AND column_type='int(10) unsigned')
          OR (column_name='producto_id' AND column_type='int(10) unsigned'));
    IF cantidad <> 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tipos de variantes_productos.id/producto_id no compatibles.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema=DATABASE() AND table_name='usuarios'
        AND column_name='id' AND column_type='bigint(20) unsigned';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'usuarios.id no coincide con BIGINT UNSIGNED.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema = DATABASE() AND table_name = 'empresas'
        AND column_name='id' AND column_type='bigint(20) unsigned';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'empresas.id no coincide con BIGINT UNSIGNED.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='cupones' AND index_name='un_cupones_codigo'
        AND non_unique=0 AND column_name='codigo' AND seq_in_index=1;
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se encontro el indice unico original un_cupones_codigo.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='productos' AND index_name='un_productos_id_empresa'
        AND non_unique=0 AND ((column_name='id' AND seq_in_index=1) OR (column_name='empresa_id' AND seq_in_index=2));
    IF cantidad <> 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta la clave unica productos(id, empresa_id).';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='variantes_productos' AND index_name='un_variantes_id_producto'
        AND non_unique=0 AND ((column_name='id' AND seq_in_index=1) OR (column_name='producto_id' AND seq_in_index=2));
    IF cantidad <> 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta la clave unica variantes_productos(id, producto_id).';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.referential_constraints
      WHERE constraint_schema=DATABASE() AND table_name='pedidos' AND constraint_name='cf_pedidos_cupon';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta o difiere la FK original pedidos.cupon_id.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.key_column_usage
      WHERE constraint_schema=DATABASE() AND table_name='pedidos' AND constraint_name='cf_pedidos_cupon'
        AND column_name='cupon_id' AND referenced_table_name='cupones' AND referenced_column_name='id';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La FK original de pedidos no apunta exactamente a cupones.id.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='pedidos' AND index_name='cf_pedidos_cupon'
        AND column_name='cupon_id' AND seq_in_index=1;
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta el indice de soporte esperado para pedidos.cupon_id.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='pedidos' AND index_name='un_pedidos_id_empresa_sede'
        AND non_unique=0 AND ((column_name='id' AND seq_in_index=1)
          OR (column_name='empresa_id' AND seq_in_index=2) OR (column_name='sede_id' AND seq_in_index=3));
    IF cantidad <> 3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta la clave unica pedidos(id, empresa_id, sede_id).';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='cupones'
        AND index_name IN ('un_cupones_empresa_codigo','un_cupones_id_empresa','ind_cupones_empresa_vigencia');
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un indice propuesto para cupones ya existe; revisar posible aplicacion parcial.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND table_name='pedidos' AND index_name='ind_pedidos_cupon_empresa';
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El indice propuesto para pedidos ya existe; revisar posible aplicacion parcial.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.table_constraints
      WHERE constraint_schema=DATABASE() AND constraint_name IN (
        'cf_cupones_empresa','cf_pedidos_cupon_empresa','ck_pedidos_cupon_empresa',
        'ck_precios_mayoristas_precio','ck_precios_mayoristas_vigencia',
        'cf_precios_mayoristas_empresa','cf_precios_mayoristas_producto_empresa',
        'cf_precios_mayoristas_variante_producto','cf_precios_mayoristas_usuario',
        'ck_configuraciones_tienda_json','cf_configuraciones_tienda_empresa','cf_configuraciones_tienda_usuario');
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un nombre de restriccion propuesto ya existe; revisar antes de continuar.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM cupones;
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hay cupones; asignar cada uno a su empresa antes de cambiar la unicidad.';
    END IF;
    SELECT COUNT(*) INTO cantidad FROM pedidos WHERE cupon_id IS NOT NULL;
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hay pedidos historicos con cupon; no inferir ni alterar su empresa.';
    END IF;

    SELECT engine INTO tabla_engine FROM information_schema.tables
      WHERE table_schema=DATABASE() AND table_name='cupones';
    IF tabla_engine <> 'InnoDB' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cupones debe usar InnoDB para conservar integridad referencial.';
    END IF;
    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema=DATABASE() AND table_name IN ('pedidos','empresas','productos','variantes_productos','usuarios') AND engine='InnoDB';
    IF cantidad <> 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una tabla relacionada no usa InnoDB; no se puede garantizar la integridad referencial.';
    END IF;
END//
DELIMITER ;

CALL `tc_preflight_ecommerce_20261105`();
DROP PROCEDURE `tc_preflight_ecommerce_20261105`;

-- Respaldo verificable previo al DDL. CREATE TABLE LIKE conserva columnas e índices;
-- mantener estas tablas hasta comprobar la aplicación y cerrar la ventana de cambio.
CREATE TABLE `respaldo_tc_ecom_20261105_cupones` LIKE `cupones`;
INSERT INTO `respaldo_tc_ecom_20261105_cupones` SELECT * FROM `cupones`;
CREATE TABLE `respaldo_tc_ecom_20261105_pedidos_cupon` AS
    SELECT `id`,`cupon_id`,`empresa_id`,`sede_id` FROM `pedidos` WHERE `cupon_id` IS NOT NULL;

-- 1. Cupones aislados por empresa. Solo se retira la unicidad global después de
-- verificar que no existen filas; se agrega unicidad dentro de la empresa.
ALTER TABLE `cupones`
    ADD COLUMN `empresa_id` BIGINT(20) UNSIGNED NOT NULL AFTER `id`,
    DROP INDEX `un_cupones_codigo`,
    ADD UNIQUE KEY `un_cupones_empresa_codigo` (`empresa_id`,`codigo`),
    ADD UNIQUE KEY `un_cupones_id_empresa` (`id`,`empresa_id`),
    ADD KEY `ind_cupones_empresa_vigencia` (`empresa_id`,`activo`,`inicia_en`,`finaliza_en`),
    ADD CONSTRAINT `cf_cupones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
      ON UPDATE RESTRICT ON DELETE RESTRICT;

-- Conserva cf_pedidos_cupon y agrega la verificación de que pedido y cupón
-- pertenecen a la misma empresa. pedidos.cupon_id ya es BIGINT UNSIGNED.
ALTER TABLE `pedidos`
    ADD KEY `ind_pedidos_cupon_empresa` (`cupon_id`,`empresa_id`),
    ADD CONSTRAINT `cf_pedidos_cupon_empresa` FOREIGN KEY (`cupon_id`,`empresa_id`)
      REFERENCES `cupones` (`id`,`empresa_id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    ADD CONSTRAINT `ck_pedidos_cupon_empresa` CHECK (`cupon_id` IS NULL OR `empresa_id` IS NOT NULL);

-- 2. Vigencias de precio por empresa/producto y, opcionalmente, variante.
-- El formulario actual registra precios por producto; variante_id queda NULL hasta
-- que la interfaz ofrezca explícitamente selección de variante. No hay tramos por cantidad.
CREATE TABLE `precios_mayoristas` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT(20) UNSIGNED NOT NULL,
    `producto_id` INT(10) UNSIGNED NOT NULL,
    `variante_id` INT(10) UNSIGNED DEFAULT NULL,
    `precio` DECIMAL(10,2) NOT NULL,
    `vigente_desde` DATETIME(6) NOT NULL,
    `vigente_hasta` DATETIME(6) DEFAULT NULL,
    `motivo` VARCHAR(300) NOT NULL,
    `creado_por_usuario_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `creado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    KEY `ind_precios_mayoristas_empresa_producto_vigencia` (`empresa_id`,`producto_id`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_precios_mayoristas_variante_vigencia` (`variante_id`,`producto_id`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_precios_mayoristas_usuario` (`creado_por_usuario_id`),
    CONSTRAINT `ck_precios_mayoristas_precio` CHECK (`precio` > 0),
    CONSTRAINT `ck_precios_mayoristas_vigencia` CHECK (`vigente_hasta` IS NULL OR `vigente_hasta` >= `vigente_desde`),
    CONSTRAINT `cf_precios_mayoristas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_precios_mayoristas_producto_empresa` FOREIGN KEY (`producto_id`,`empresa_id`) REFERENCES `productos` (`id`,`empresa_id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_precios_mayoristas_variante_producto` FOREIGN KEY (`variante_id`,`producto_id`) REFERENCES `variantes_productos` (`id`,`producto_id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_precios_mayoristas_usuario` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Preferencias públicas no secretas, por empresa. No almacenar credenciales,
-- tokens, claves de pasarela, webhooks ni datos de integraciones externas.
CREATE TABLE `configuraciones_tienda` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT(20) UNSIGNED NOT NULL,
    `configuracion_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    `actualizado_por_usuario_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `creado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `actualizado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `un_configuraciones_tienda_empresa` (`empresa_id`),
    KEY `ind_configuraciones_tienda_usuario` (`actualizado_por_usuario_id`),
    CONSTRAINT `ck_configuraciones_tienda_json` CHECK (JSON_VALID(`configuracion_json`)),
    CONSTRAINT `cf_configuraciones_tienda_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_configuraciones_tienda_usuario` FOREIGN KEY (`actualizado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recuperación: primero detener escrituras, respaldar las filas creadas desde la aplicación
-- y verificar que no haya referencias a cupones nuevos. No ejecutar DROP a ciegas.
-- Si ambas tablas nuevas están vacías y cupones no contiene registros:
-- ALTER TABLE `pedidos` DROP FOREIGN KEY `cf_pedidos_cupon_empresa`, DROP CONSTRAINT `ck_pedidos_cupon_empresa`, DROP INDEX `ind_pedidos_cupon_empresa`;
-- ALTER TABLE `cupones` DROP FOREIGN KEY `cf_cupones_empresa`, DROP INDEX `un_cupones_empresa_codigo`, DROP INDEX `un_cupones_id_empresa`, DROP INDEX `ind_cupones_empresa_vigencia`, DROP COLUMN `empresa_id`, ADD UNIQUE KEY `un_cupones_codigo` (`codigo`);
-- DROP TABLE `configuraciones_tienda`, `precios_mayoristas`;
-- DROP TABLE `respaldo_tc_ecom_20261105_pedidos_cupon`, `respaldo_tc_ecom_20261105_cupones`;
-- Si ya hay cupones/pedidos nuevos, exportarlos y migrar su propiedad antes del rollback.
