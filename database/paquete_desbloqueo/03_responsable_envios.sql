-- Propuesta no aplicada: responsable vigente de cada envio.
-- Compatible con MariaDB 10.4.32. Ejecutar solo tras autorizacion explicita.
-- La validacion de empresa/sede y vigencia del responsable debe hacerse en Laravel.

SELECT DATABASE() AS base_seleccionada,
       (SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND column_name = 'responsable_usuario_id') AS columna_ya_existente,
       (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
        WHERE constraint_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND constraint_name = 'cf_envios_responsable_usuario'
          AND constraint_type = 'FOREIGN KEY') AS clave_foranea_ya_existente;

DELIMITER //
CREATE PROCEDURE `sp_propuesta_envios_responsable_20261105`()
BEGIN
    IF DATABASE() <> 'md_tecnologia_digital_cell' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Selecciona md_tecnologia_digital_cell antes de aplicar esta propuesta.';
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLES
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos' AND engine = 'InnoDB'
    ) OR NOT EXISTS (
        SELECT 1 FROM information_schema.TABLES
        WHERE table_schema = DATABASE() AND table_name = 'usuarios' AND engine = 'InnoDB'
    ) OR NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND column_name = 'sede_id' AND column_type = 'bigint(20) unsigned'
    ) OR NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE table_schema = DATABASE() AND table_name = 'usuarios'
          AND column_name = 'id' AND column_type = 'bigint(20) unsigned'
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El esquema no coincide con las precondiciones verificadas; no se aplico ningun cambio.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND column_name = 'responsable_usuario_id'
          AND (column_type <> 'bigint(20) unsigned' OR is_nullable <> 'YES')
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'responsable_usuario_id ya existe con tipo o nulabilidad incompatible.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND index_name = 'ind_envios_responsable_estado'
        GROUP BY index_name
        HAVING GROUP_CONCAT(column_name ORDER BY seq_in_index SEPARATOR ',') <> 'responsable_usuario_id,estado'
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El indice ind_envios_responsable_estado ya existe con una definicion incompatible.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND constraint_name = 'cf_envios_responsable_usuario'
          AND (column_name <> 'responsable_usuario_id' OR referenced_table_name <> 'usuarios'
               OR referenced_column_name <> 'id')
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La clave foranea cf_envios_responsable_usuario ya existe con una definicion incompatible.';
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND column_name = 'responsable_usuario_id'
    ) THEN
        ALTER TABLE `envios_pedidos`
            ADD COLUMN `responsable_usuario_id` BIGINT(20) UNSIGNED NULL
            AFTER `creado_por_usuario_id`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE table_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND index_name = 'ind_envios_responsable_estado'
    ) THEN
        ALTER TABLE `envios_pedidos`
            ADD INDEX `ind_envios_responsable_estado` (`responsable_usuario_id`, `estado`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE constraint_schema = DATABASE() AND table_name = 'envios_pedidos'
          AND constraint_name = 'cf_envios_responsable_usuario'
          AND constraint_type = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE `envios_pedidos`
            ADD CONSTRAINT `cf_envios_responsable_usuario`
            FOREIGN KEY (`responsable_usuario_id`) REFERENCES `usuarios` (`id`)
            ON UPDATE RESTRICT ON DELETE SET NULL;
    END IF;
END//
CALL `sp_propuesta_envios_responsable_20261105`()//
DROP PROCEDURE `sp_propuesta_envios_responsable_20261105`//
DELIMITER ;

-- Recuperacion opcional, solo antes de guardar asignaciones en la nueva columna:
-- ALTER TABLE `envios_pedidos` DROP FOREIGN KEY `cf_envios_responsable_usuario`;
-- ALTER TABLE `envios_pedidos` DROP INDEX `ind_envios_responsable_estado`;
-- ALTER TABLE `envios_pedidos` DROP COLUMN `responsable_usuario_id`;
