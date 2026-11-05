-- Technology Cell: asignacion de responsables de organizacion.
-- Compatible por definicion con MariaDB 10.4.32; revisar y probar en una instancia
-- aislada antes de aplicar. NO ejecutar en la base activa sin autorizacion expresa.
--
-- usuarios_sedes_roles representa asignaciones RBAC. No guarda almacenes/areas,
-- cargo, motivo, responsable anterior ni vigencias funcionales; no se reutiliza
-- como asignacion empresarial.
-- Este archivo solo agrega una tabla nueva. No modifica ni rellena filas existentes,
-- no altera roles/permisos y no crea un responsable ficticio.
--
-- Aplicar en una ventana de mantenimiento y con el cliente SQL configurado para
-- detenerse ante error (no usar --force). Si falla CALL, retirar unicamente la
-- rutina creada por este archivo con:
-- DROP PROCEDURE `tc_preflight_responsables_20261105`;
-- Si la tabla se creo pero aun esta vacia, recuperacion: DROP TABLE
-- `responsables_organizacionales`. Si contiene filas, exportarlas y revisarlas;
-- no descartar asignaciones registradas.

USE `md_tecnologia_digital_cell`;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER //
CREATE PROCEDURE `tc_preflight_responsables_20261105`()
BEGIN
    DECLARE cantidad INT DEFAULT 0;

    IF DATABASE() <> 'md_tecnologia_digital_cell' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Base incorrecta; se requiere md_tecnologia_digital_cell.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema=DATABASE() AND table_name IN ('empresas','sedes','almacenes','usuarios');
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta una tabla padre requerida; revisar el SQL de referencia.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema=DATABASE() AND table_name='responsables_organizacionales';
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La tabla de responsables ya existe; no se sobrescribira.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema=DATABASE() AND (
        (table_name='empresas' AND column_name='id' AND column_type='bigint(20) unsigned') OR
        (table_name='sedes' AND column_name='id' AND column_type='bigint(20) unsigned') OR
        (table_name='sedes' AND column_name='empresa_id' AND column_type='bigint(20) unsigned') OR
        (table_name='almacenes' AND column_name='id' AND column_type='bigint(20) unsigned') OR
        (table_name='almacenes' AND column_name='sede_id' AND column_type='bigint(20) unsigned') OR
        (table_name='usuarios' AND column_name='id' AND column_type='bigint(20) unsigned'));
    IF cantidad <> 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tipos de columnas padre distintos a los esperados.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema=DATABASE() AND (
        (table_name='sedes' AND index_name='un_sedes_id_empresa' AND non_unique=0
          AND ((column_name='id' AND seq_in_index=1) OR (column_name='empresa_id' AND seq_in_index=2))) OR
        (table_name='almacenes' AND index_name='un_almacenes_id_sede' AND non_unique=0
          AND ((column_name='id' AND seq_in_index=1) OR (column_name='sede_id' AND seq_in_index=2))));
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Faltan indices unicos compuestos requeridos en sedes o almacenes.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema=DATABASE() AND table_name IN ('empresas','sedes','almacenes','usuarios') AND engine='InnoDB';
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una tabla padre no usa InnoDB; no se puede garantizar la integridad referencial.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.table_constraints
      WHERE constraint_schema=DATABASE() AND constraint_name IN (
        'cf_responsables_empresa','cf_responsables_sede_empresa','cf_responsables_almacen_sede',
        'cf_responsables_usuario','cf_responsables_asignador','cf_responsables_relevo_contexto',
        'ck_responsables_recurso','ck_responsables_vigencia');
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un nombre de restriccion propuesto ya existe; revisar antes de continuar.';
    END IF;
END//
DELIMITER ;

CALL `tc_preflight_responsables_20261105`();
DROP PROCEDURE `tc_preflight_responsables_20261105`;

CREATE TABLE `responsables_organizacionales` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT(20) UNSIGNED NOT NULL,
    `sede_id` BIGINT(20) UNSIGNED NOT NULL,
    `tipo_recurso` ENUM('sede','almacen') NOT NULL,
    `almacen_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `responsable_usuario_id` BIGINT(20) UNSIGNED NOT NULL,
    `cargo` VARCHAR(100) NOT NULL,
    `vigente_desde` DATE NOT NULL,
    `vigente_hasta` DATE DEFAULT NULL,
    `motivo` VARCHAR(500) NOT NULL,
    `reemplaza_asignacion_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `asignado_por_usuario_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `actualizado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `un_responsables_id_empresa_sede` (`id`,`empresa_id`,`sede_id`),
    KEY `ind_responsables_empresa_vigencia` (`empresa_id`,`activo`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_responsables_sede_recurso` (`empresa_id`,`sede_id`,`tipo_recurso`,`cargo`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_responsables_almacen_recurso` (`almacen_id`,`activo`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_responsables_usuario_vigencia` (`responsable_usuario_id`,`activo`,`vigente_hasta`),
    KEY `ind_responsables_relevo` (`reemplaza_asignacion_id`),
    KEY `ind_responsables_asignador` (`asignado_por_usuario_id`),
    CONSTRAINT `ck_responsables_recurso` CHECK (
      (`tipo_recurso`='sede' AND `almacen_id` IS NULL) OR
      (`tipo_recurso`='almacen' AND `almacen_id` IS NOT NULL)
    ),
    CONSTRAINT `ck_responsables_vigencia` CHECK (`vigente_hasta` IS NULL OR `vigente_hasta` >= `vigente_desde`),
    CONSTRAINT `cf_responsables_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
      ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_responsables_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`,`empresa_id`)
      ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_responsables_almacen_sede` FOREIGN KEY (`almacen_id`,`sede_id`) REFERENCES `almacenes` (`id`,`sede_id`)
      ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_responsables_usuario` FOREIGN KEY (`responsable_usuario_id`) REFERENCES `usuarios` (`id`)
      ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_responsables_asignador` FOREIGN KEY (`asignado_por_usuario_id`) REFERENCES `usuarios` (`id`)
      ON UPDATE RESTRICT ON DELETE SET NULL,
    CONSTRAINT `cf_responsables_relevo_contexto` FOREIGN KEY (`reemplaza_asignacion_id`,`empresa_id`,`sede_id`)
      REFERENCES `responsables_organizacionales` (`id`,`empresa_id`,`sede_id`)
      ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Regla aprobada: un responsable principal vigente por empresa/sede/recurso/cargo.
-- El recurso se identifica por tipo_recurso; almacen_id se usa solo para almacenes.
-- No existe entidad de areas en el esquema revisado, por lo que no se crea ni se
-- permite registrar un recurso de área.
-- El servicio bloquea la fila de sede antes de buscar solapamientos, cierra el
-- registro anterior en inicio_nuevo - 1 dia y guarda su id en reemplaza_asignacion_id.
-- La tabla conserva historia; no se borra el predecesor. La exclusion de intervalos
-- se valida en transaccion, porque MariaDB 10.4 no tiene exclusion constraints.
