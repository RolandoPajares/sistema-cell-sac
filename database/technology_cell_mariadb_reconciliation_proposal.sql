-- TECHNOLOGY CELL / AGENTE MARIADB
-- Reconciliacion segura: solo auditoria y precondiciones.
-- No ejecutar migrate global ni marcar migraciones como aplicadas sin revisar este resultado.

USE `md_tecnologia_digital_cell`;

SELECT DATABASE() AS base_activa,
       IF(DATABASE() = 'md_tecnologia_digital_cell', 'OK', 'ABORTAR') AS guardia_base;

-- La instalacion/actualizacion debe usar estas tablas canonicas.
SELECT tabla_canonica, tabla_legacy, existe_canonica, existe_legacy, filas_legacy
FROM (
    SELECT 'variantes_productos' AS tabla_canonica, 'producto_variantes' AS tabla_legacy,
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'variantes_productos') AS existe_canonica,
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'producto_variantes') AS existe_legacy,
           IFNULL((SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'producto_variantes'), 0) AS filas_legacy
    UNION ALL
    SELECT 'detalles_compras', 'compra_detalles',
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalles_compras'),
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'compra_detalles'),
           IFNULL((SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'compra_detalles'), 0)
    UNION ALL
    SELECT 'detalles_cotizaciones', 'cotizacion_detalles',
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalles_cotizaciones'),
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cotizacion_detalles'),
           IFNULL((SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cotizacion_detalles'), 0)
    UNION ALL
    SELECT 'detalles_pedidos', 'detalle_pedidos',
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalles_pedidos'),
           EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalle_pedidos'),
           IFNULL((SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'detalle_pedidos'), 0)
) AS nombres;

-- No insertar filas en migrations desde este script. Cada migracion historica
-- usa nombres legacy que no coinciden con el esquema canonico del dump.
SELECT migration, batch
FROM migrations
ORDER BY id;

-- Evidencia minima para una futura marcacion controlada:
SELECT COUNT(*) AS tablas_base,
       SUM(ENGINE = 'InnoDB') AS tablas_innodb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE';

