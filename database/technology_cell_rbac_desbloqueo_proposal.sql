-- Propuesta RBAC mínima, solo para el rol administrador.
-- MariaDB 10.4.32. NO ejecutar sin autorización expresa.
-- Habilita únicamente las pantallas Responsables, Sesiones, Envíos e IMEI.
-- No crea roles, no cambia otros roles ni concede permisos de operación.
-- Ejecutar después de aplicar y verificar las propuestas de estructura 01-04.

USE `md_tecnologia_digital_cell`;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER //
CREATE PROCEDURE `tc_preflight_rbac_desbloqueo_20261105`()
BEGIN
    DECLARE cantidad INT DEFAULT 0;
    DECLARE rol_administrador BIGINT UNSIGNED DEFAULT NULL;
    DECLARE menu_administrador BIGINT UNSIGNED DEFAULT NULL;

    IF DATABASE() <> 'md_tecnologia_digital_cell' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Base incorrecta; se requiere md_tecnologia_digital_cell.';
    END IF;

    SELECT COUNT(*), MAX(id) INTO cantidad, rol_administrador
      FROM `roles` WHERE `codigo` = 'administrador';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Debe existir exactamente un rol administrador.';
    END IF;

    SELECT COUNT(*), MAX(id) INTO cantidad, menu_administrador
      FROM `menus` WHERE `codigo` = 'administrador.sidebar' AND `contexto` = 'sidebar';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se encuentra el menu lateral existente del administrador.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM `roles_menus` WHERE `rol_id` = rol_administrador AND `menu_id` = menu_administrador) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El menu lateral no esta asociado al rol administrador.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM `modulos` WHERE `codigo` = 'organizacion' AND `habilitado` = 1)
       OR NOT EXISTS (SELECT 1 FROM `modulos` WHERE `codigo` = 'seguridad' AND `habilitado` = 1)
       OR NOT EXISTS (SELECT 1 FROM `modulos` WHERE `codigo` = 'inventario' AND `habilitado` = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta habilitar un modulo existente; no se modificaran modulos globales.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM information_schema.tables
      WHERE table_schema = DATABASE() AND table_name = 'registros_auditoria' AND engine = 'InnoDB';
    IF cantidad <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta registros_auditoria InnoDB; no se aplicara un cambio RBAC sin auditoria.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM `pantallas` p JOIN `modulos` m ON m.id = p.modulo_id
      WHERE (p.codigo = 'admin.responsibles' AND m.codigo = 'organizacion' AND (p.ruta IS NULL OR p.ruta = 'management.company_settings.responsibles'))
         OR (p.codigo = 'admin.sessions' AND m.codigo = 'seguridad' AND (p.ruta IS NULL OR p.ruta = 'management.security_sessions'))
         OR (p.codigo = 'admin.shipments' AND m.codigo = 'inventario' AND (p.ruta IS NULL OR p.ruta = 'management.inventory.preview.shipments'))
         OR (p.codigo = 'admin.imei' AND m.codigo = 'inventario' AND (p.ruta IS NULL OR p.ruta = 'management.inventory.preview.serials'));
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pantallas existentes faltantes, asociadas a modulo incorrecto o con ruta conflictiva.';
    END IF;

    SELECT COUNT(*) INTO cantidad FROM `elementos_menu`
      WHERE `menu_id` = menu_administrador AND `tipo` = 'pantalla' AND (
        (`codigo` = 'admin.responsibles' AND `pantalla_id` = (SELECT id FROM pantallas WHERE codigo='admin.responsibles')) OR
        (`codigo` = 'admin.sessions' AND `pantalla_id` = (SELECT id FROM pantallas WHERE codigo='admin.sessions')) OR
        (`codigo` = 'admin.shipments' AND `pantalla_id` = (SELECT id FROM pantallas WHERE codigo='admin.shipments')) OR
        (`codigo` = 'admin.imei' AND `pantalla_id` = (SELECT id FROM pantallas WHERE codigo='admin.imei')));
    IF cantidad <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta un elemento de menu exacto para una de las cuatro pantallas.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM `permisos` p
        JOIN `pantallas` s ON s.id = p.pantalla_id
        JOIN `roles_permisos` rp ON rp.permiso_id = p.id
        JOIN `roles` r ON r.id = rp.rol_id
        WHERE s.codigo IN ('admin.responsibles','admin.sessions','admin.shipments','admin.imei')
          AND p.accion = 'consultar' AND r.codigo <> 'administrador'
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un permiso de pantalla ya esta asignado a otro rol; no se ampliaran privilegios.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM `permisos` p LEFT JOIN `pantallas` s ON s.id = p.pantalla_id
        WHERE p.codigo IN (
          'pantalla.admin.responsibles.consultar','pantalla.admin.sessions.consultar',
          'pantalla.admin.shipments.consultar','pantalla.admin.imei.consultar')
          AND (s.id IS NULL OR p.accion <> 'consultar' OR p.codigo <> CONCAT('pantalla.',s.codigo,'.consultar')
               OR p.modulo_id <> s.modulo_id)
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un permiso propuesto existe con una definicion incompatible.';
    END IF;
END//
DELIMITER ;

CALL `tc_preflight_rbac_desbloqueo_20261105`();

DELIMITER //
CREATE PROCEDURE `tc_aplicar_rbac_desbloqueo_20261105`()
BEGIN
    DECLARE rol_administrador BIGINT UNSIGNED;
    DECLARE menu_administrador BIGINT UNSIGNED;
    DECLARE estado_previo LONGTEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    CALL `tc_preflight_rbac_desbloqueo_20261105`();
    START TRANSACTION;
    SELECT id INTO rol_administrador FROM `roles` WHERE codigo = 'administrador' FOR UPDATE;
    SELECT id INTO menu_administrador FROM `menus` WHERE codigo = 'administrador.sidebar' AND contexto = 'sidebar' FOR UPDATE;
    SELECT GROUP_CONCAT(CONCAT(codigo,'=',COALESCE(ruta,'NULL'),':',habilitada) ORDER BY codigo SEPARATOR ';') INTO estado_previo
      FROM `pantallas`
     WHERE codigo IN ('admin.responsibles','admin.sessions','admin.shipments','admin.imei');

    INSERT INTO `permisos` (`modulo_id`,`pantalla_id`,`codigo`,`accion`,`nombre`,`es_sistema`,`activo`,`creado_en`,`actualizado_en`)
    SELECT s.modulo_id,s.id,CONCAT('pantalla.',s.codigo,'.consultar'),'consultar',CONCAT(s.nombre,' - consultar'),1,1,NOW(),NOW()
      FROM `pantallas` s
     WHERE s.codigo IN ('admin.responsibles','admin.sessions','admin.shipments','admin.imei')
       AND NOT EXISTS (SELECT 1 FROM `permisos` p WHERE p.codigo = CONCAT('pantalla.',s.codigo,'.consultar'));

    UPDATE `permisos` p JOIN `pantallas` s ON s.id = p.pantalla_id
       SET p.activo = 1, p.actualizado_en = NOW()
     WHERE s.codigo IN ('admin.responsibles','admin.sessions','admin.shipments','admin.imei')
       AND p.codigo = CONCAT('pantalla.',s.codigo,'.consultar') AND p.accion = 'consultar';

    UPDATE `pantallas` SET `ruta` = 'management.company_settings.responsibles', `habilitada` = 1, `actualizado_en` = NOW()
     WHERE `codigo` = 'admin.responsibles';
    UPDATE `pantallas` SET `ruta` = 'management.security_sessions', `habilitada` = 1, `actualizado_en` = NOW()
     WHERE `codigo` = 'admin.sessions';
    UPDATE `pantallas` SET `ruta` = 'management.inventory.preview.shipments', `habilitada` = 1, `actualizado_en` = NOW()
     WHERE `codigo` = 'admin.shipments';
    UPDATE `pantallas` SET `ruta` = 'management.inventory.preview.serials', `habilitada` = 1, `actualizado_en` = NOW()
     WHERE `codigo` = 'admin.imei';

    UPDATE `elementos_menu` e JOIN `pantallas` s ON s.id = e.pantalla_id
       JOIN `permisos` p ON p.pantalla_id = s.id AND p.codigo = CONCAT('pantalla.',s.codigo,'.consultar')
       SET e.permiso_id = p.id, e.habilitado = 1, e.actualizado_en = NOW()
     WHERE e.menu_id = menu_administrador AND e.tipo = 'pantalla'
       AND s.codigo IN ('admin.responsibles','admin.sessions','admin.shipments','admin.imei');

    INSERT INTO `roles_permisos` (`rol_id`,`permiso_id`,`alcance`,`creado_en`)
    SELECT rol_administrador,p.id,'empresa',NOW()
      FROM `permisos` p JOIN `pantallas` s ON s.id = p.pantalla_id
     WHERE s.codigo IN ('admin.responsibles','admin.sessions','admin.shipments','admin.imei')
       AND p.codigo = CONCAT('pantalla.',s.codigo,'.consultar') AND p.accion = 'consultar' AND p.activo = 1
       AND NOT EXISTS (SELECT 1 FROM `roles_permisos` rp WHERE rp.rol_id = rol_administrador AND rp.permiso_id = p.id AND rp.alcance = 'empresa');

    INSERT INTO `registros_auditoria` (
      `accion`,`entidad`,`valores_anteriores`,`valores_nuevos`,`creado_en`,
      `modulo`,`resultado`,`observaciones`,`ocurrido_en`,`tabla_afectada`,
      `identificador_entidad`,`cambios`,`origen`,`identificador_universal_evento`
    ) VALUES (
      'rbac.screens.enabled','pantallas',estado_previo,
      JSON_ARRAY('admin.responsibles','admin.sessions','admin.shipments','admin.imei'),NOW(),
      'seguridad','exitoso','Habilitacion minima propuesta aplicada al rol administrador; actor SQL no identificado por Laravel.',
      NOW(),'pantallas','admin.responsibles|admin.sessions|admin.shipments|admin.imei',
      JSON_OBJECT('rol_codigo','administrador','alcance','empresa','permisos','consulta'),
      'sql',UUID()
    );

    COMMIT;
END//
DELIMITER ;

CALL `tc_aplicar_rbac_desbloqueo_20261105`();
DROP PROCEDURE `tc_aplicar_rbac_desbloqueo_20261105`;
DROP PROCEDURE `tc_preflight_rbac_desbloqueo_20261105`;

-- La ejecución realiza cambios solo en pantallas/permisos/elementos_menu/roles_permisos
-- del administrador. La desactivación o reversión requiere conservar/exportar antes
-- las filas; no borrar permisos protegidos ni editar asignaciones administrativas.
