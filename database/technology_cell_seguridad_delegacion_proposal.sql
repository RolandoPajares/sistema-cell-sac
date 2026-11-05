-- Technology Cell: almacenamiento propuesto para políticas de aprobación y delegación.
-- MariaDB 10.4.32. Solo DDL aditivo; NO ejecutar en la base activa sin autorización.
-- No concede RBAC ni altera roles, permisos, usuarios o asignaciones existentes.
--
-- Dependencias verificadas en md_tecnologia_digital_cell.sql:
-- empresas/sedes/usuarios/roles/permisos usan id BIGINT UNSIGNED; sedes tiene
-- un_sedes_id_empresa(id,empresa_id). La operación debe referenciar permisos
-- concretos (no un módulo completo) y validar el mismo alcance en Laravel.
--
-- Contratos aún por aprobar antes de implementar el motor:
-- * si cada nivel requiere a cualquiera o a todos sus aprobadores;
-- * prioridad de políticas solapadas por permiso, sede e intervalo de importes;
-- * si una delegación transfiere solo un permiso específico o sustituye al
--   aprobador designado en un nivel y cómo se comportan revocación y suplencias.
-- Estas tablas no activan ni aplican esas reglas por sí mismas.
--
-- Preflight automatizado: valida base, tablas, tipos, InnoDB, índice compuesto
-- de sedes, tablas destino y nombres de restricciones antes del primer CREATE.
-- Recuperación antes de registrar datos: DROP TABLE `delegaciones_permisos`,
-- `aprobadores_politica`, `politicas_aprobacion` (en ese orden). Con filas,
-- exportarlas y revisarlas; nunca borrarlas como recuperación automática.

USE `md_tecnologia_digital_cell`;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER //
CREATE PROCEDURE `tc_preflight_seguridad_20261105`()
BEGIN
    DECLARE cantidad INT DEFAULT 0;

    IF DATABASE() <> 'md_tecnologia_digital_cell' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Base incorrecta; se requiere md_tecnologia_digital_cell.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema = DATABASE() AND table_name IN ('empresas','sedes','usuarios','roles','permisos');
    IF cantidad <> 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta una tabla padre requerida; comparar con md_tecnologia_digital_cell.sql.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema = DATABASE() AND table_name IN ('politicas_aprobacion','aprobadores_politica','delegaciones_permisos');
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una tabla propuesta ya existe; no se sobrescribira.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.columns
      WHERE table_schema = DATABASE() AND (
        (table_name IN ('empresas','sedes','usuarios','roles','permisos') AND column_name = 'id' AND column_type = 'bigint(20) unsigned') OR
        (table_name = 'sedes' AND column_name = 'empresa_id' AND column_type = 'bigint(20) unsigned'));
    IF cantidad <> 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tipos de IDs padre distintos a los requeridos por las claves foraneas.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema = DATABASE() AND table_name IN ('empresas','sedes','usuarios','roles','permisos') AND engine = 'InnoDB';
    IF cantidad <> 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una tabla padre no usa InnoDB; no se puede garantizar integridad referencial.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.statistics
      WHERE table_schema = DATABASE() AND table_name = 'sedes' AND index_name = 'un_sedes_id_empresa'
        AND non_unique = 0 AND ((column_name = 'id' AND seq_in_index = 1) OR (column_name = 'empresa_id' AND seq_in_index = 2));
    IF cantidad <> 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta el indice unico sedes(id,empresa_id) requerido.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.table_constraints
      WHERE constraint_schema = DATABASE() AND constraint_name IN (
        'ck_politica_importes','ck_politica_niveles','ck_politica_vigencia','cf_politica_empresa',
        'cf_politica_sede_empresa','cf_politica_permiso','cf_politica_creador',
        'ck_aprobador_un_sujeto','ck_aprobador_nivel','cf_aprobador_politica_empresa',
        'cf_aprobador_rol','cf_aprobador_usuario','ck_delegacion_usuarios','ck_delegacion_vigencia',
        'cf_delegacion_empresa','cf_delegacion_sede_empresa','cf_delegacion_permiso',
        'cf_delegacion_delegante','cf_delegacion_delegado','cf_delegacion_creador');
    IF cantidad <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un nombre de restriccion propuesto ya existe; revisar antes de aplicar.';
    END IF;
END//
DELIMITER ;

CALL `tc_preflight_seguridad_20261105`();
DROP PROCEDURE `tc_preflight_seguridad_20261105`;

CREATE TABLE `politicas_aprobacion` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT(20) UNSIGNED NOT NULL,
    `sede_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `permiso_id` BIGINT(20) UNSIGNED NOT NULL,
    `codigo` VARCHAR(80) NOT NULL,
    `nombre` VARCHAR(160) NOT NULL,
    `moneda` CHAR(3) NOT NULL,
    `importe_minimo` DECIMAL(15,2) DEFAULT NULL,
    `importe_maximo` DECIMAL(15,2) DEFAULT NULL,
    `niveles_requeridos` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
    `modo_aprobacion` ENUM('cualquiera','todos') NOT NULL DEFAULT 'cualquiera',
    `vigente_desde` DATE NOT NULL,
    `vigente_hasta` DATE DEFAULT NULL,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `motivo` VARCHAR(500) NOT NULL,
    `creado_por_usuario_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `creado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `actualizado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `un_politica_id_empresa` (`id`,`empresa_id`),
    UNIQUE KEY `un_politica_empresa_codigo` (`empresa_id`,`codigo`),
    KEY `ind_politica_permiso_sede_activa` (`permiso_id`,`sede_id`,`activa`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_politica_empresa_sede` (`empresa_id`,`sede_id`),
    CONSTRAINT `ck_politica_importes` CHECK (`importe_minimo` IS NULL OR `importe_maximo` IS NULL OR `importe_maximo` >= `importe_minimo`),
    CONSTRAINT `ck_politica_niveles` CHECK (`niveles_requeridos` BETWEEN 1 AND 3),
    CONSTRAINT `ck_politica_vigencia` CHECK (`vigente_hasta` IS NULL OR `vigente_hasta` >= `vigente_desde`),
    CONSTRAINT `cf_politica_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_politica_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`,`empresa_id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_politica_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_politica_creador` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `aprobadores_politica` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `politica_id` BIGINT(20) UNSIGNED NOT NULL,
    `empresa_id` BIGINT(20) UNSIGNED NOT NULL,
    `nivel` TINYINT(3) UNSIGNED NOT NULL,
    `rol_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `usuario_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `creado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `un_aprobador_politica_nivel_usuario` (`politica_id`,`nivel`,`usuario_id`),
    UNIQUE KEY `un_aprobador_politica_nivel_rol` (`politica_id`,`nivel`,`rol_id`),
    KEY `ind_aprobador_empresa_usuario` (`empresa_id`,`usuario_id`),
    KEY `ind_aprobador_empresa_rol` (`empresa_id`,`rol_id`),
    CONSTRAINT `ck_aprobador_un_sujeto` CHECK ((`rol_id` IS NULL) <> (`usuario_id` IS NULL)),
    CONSTRAINT `ck_aprobador_nivel` CHECK (`nivel` BETWEEN 1 AND 3),
    CONSTRAINT `cf_aprobador_politica_empresa` FOREIGN KEY (`politica_id`,`empresa_id`) REFERENCES `politicas_aprobacion` (`id`,`empresa_id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_aprobador_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_aprobador_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `delegaciones_permisos` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT(20) UNSIGNED NOT NULL,
    `sede_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `permiso_id` BIGINT(20) UNSIGNED NOT NULL,
    `delegante_usuario_id` BIGINT(20) UNSIGNED NOT NULL,
    `delegado_usuario_id` BIGINT(20) UNSIGNED NOT NULL,
    `vigente_desde` DATETIME(6) NOT NULL,
    `vigente_hasta` DATETIME(6) NOT NULL,
    `motivo` VARCHAR(500) NOT NULL,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `revocada_en` DATETIME(6) DEFAULT NULL,
    `creada_por_usuario_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `creado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `actualizado_en` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    KEY `ind_delegacion_vigente` (`empresa_id`,`sede_id`,`permiso_id`,`activa`,`vigente_desde`,`vigente_hasta`),
    KEY `ind_delegacion_delegante` (`delegante_usuario_id`,`activa`,`vigente_hasta`),
    KEY `ind_delegacion_delegado` (`delegado_usuario_id`,`activa`,`vigente_hasta`),
    CONSTRAINT `ck_delegacion_usuarios` CHECK (`delegante_usuario_id` <> `delegado_usuario_id`),
    CONSTRAINT `ck_delegacion_vigencia` CHECK (`vigente_hasta` > `vigente_desde`),
    CONSTRAINT `cf_delegacion_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_delegacion_sede_empresa` FOREIGN KEY (`sede_id`,`empresa_id`) REFERENCES `sedes` (`id`,`empresa_id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_delegacion_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_delegacion_delegante` FOREIGN KEY (`delegante_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_delegacion_delegado` FOREIGN KEY (`delegado_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `cf_delegacion_creador` FOREIGN KEY (`creada_por_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
