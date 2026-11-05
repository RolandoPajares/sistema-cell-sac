-- Registrar solo migraciones cuya estructura ya fue verificada en la base activa.
-- Este script no crea tablas ni modifica datos de negocio.
-- Ejecutar con el respaldo preauditado disponible y revisar el SELECT previo.

USE `md_tecnologia_digital_cell`;

SET @base_correcta := (DATABASE() = 'md_tecnologia_digital_cell');
SET @ventas_ok := (
    SELECT COUNT(*) = 2
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ventas'
      AND ((COLUMN_NAME = 'canal' AND COLUMN_TYPE = 'varchar(30)' AND IS_NULLABLE = 'YES' AND COALESCE(COLUMN_DEFAULT, 'NULL') = 'NULL')
        OR (COLUMN_NAME = 'observaciones' AND COLUMN_TYPE = 'varchar(500)' AND IS_NULLABLE = 'YES' AND COALESCE(COLUMN_DEFAULT, 'NULL') = 'NULL'))
);
SET @transferencias_ok := (
    SELECT COUNT(*) = 2
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'detalles_transferencias_existencias'
      AND ((COLUMN_NAME = 'cantidad_devuelta' AND COLUMN_TYPE = 'decimal(18,6)' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT = '0.000000')
        OR (COLUMN_NAME = 'cantidad_conciliada' AND COLUMN_TYPE = 'decimal(18,6)' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT = '0.000000'))
);
SET @migraciones_ok := (
    SELECT COUNT(*) = 0
    FROM migrations
    WHERE migration IN (
        '2026_11_03_192348_agregar_canal_y_observaciones_a_ventas',
        '2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias'
    )
);
SET @batch_nuevo := (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

SELECT DATABASE() AS base_activa, @base_correcta AS base_ok, @ventas_ok AS ventas_ok,
       @transferencias_ok AS transferencias_ok, @migraciones_ok AS migraciones_libres;

START TRANSACTION;

INSERT INTO migrations (migration, batch)
SELECT '2026_11_03_192348_agregar_canal_y_observaciones_a_ventas', @batch_nuevo
FROM (SELECT 1 AS guardia) AS guardia
WHERE @base_correcta AND @ventas_ok AND @migraciones_ok;

INSERT INTO migrations (migration, batch)
SELECT '2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias', @batch_nuevo
FROM (SELECT 1 AS guardia) AS guardia
WHERE @base_correcta AND @transferencias_ok AND @migraciones_ok;

COMMIT;

SELECT migration, batch
FROM migrations
WHERE migration IN (
    '2026_11_03_192348_agregar_canal_y_observaciones_a_ventas',
    '2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias'
)
ORDER BY migration;

