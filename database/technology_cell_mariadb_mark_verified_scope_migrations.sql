-- Reconciliación de migraciones no-op verificadas contra el esquema activo.
-- No crea tablas, no modifica columnas y no transforma datos de negocio.

USE `md_tecnologia_digital_cell`;

SET @base_correcta := DATABASE() = 'md_tecnologia_digital_cell';
SET @scope_columns_ok := (
    SELECT COUNT(*) = 15
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND ((TABLE_NAME = 'sedes' AND COLUMN_NAME = 'empresa_id' AND COLUMN_TYPE = 'bigint(20) unsigned')
        OR (TABLE_NAME = 'almacenes' AND COLUMN_NAME = 'sede_id' AND COLUMN_TYPE = 'bigint(20) unsigned')
        OR (TABLE_NAME IN ('pedidos', 'compras', 'movimientos_inventario') AND COLUMN_NAME IN ('empresa_id', 'sede_id', 'almacen_id') AND COLUMN_TYPE = 'bigint(20) unsigned')
        OR (TABLE_NAME IN ('cotizaciones', 'devoluciones_garantias') AND COLUMN_NAME IN ('empresa_id', 'sede_id') AND COLUMN_TYPE = 'bigint(20) unsigned'))
);
SET @scope_indexes_ok := (
    SELECT COUNT(DISTINCT CONCAT(TABLE_NAME, ':', INDEX_NAME)) = 2
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND ((TABLE_NAME = 'sedes' AND INDEX_NAME = 'un_sedes_id_empresa' AND NON_UNIQUE = 0)
        OR (TABLE_NAME = 'almacenes' AND INDEX_NAME = 'un_almacenes_id_sede' AND NON_UNIQUE = 0))
);
SET @return_fk_ok := (
    SELECT COUNT(*) = 3
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones_garantias'
      AND ((COLUMN_NAME = 'pedido_id' AND REFERENCED_TABLE_NAME = 'pedidos' AND REFERENCED_COLUMN_NAME = 'id')
        OR (COLUMN_NAME = 'cliente_id' AND REFERENCED_TABLE_NAME = 'clientes' AND REFERENCED_COLUMN_NAME = 'id')
        OR (COLUMN_NAME = 'producto_id' AND REFERENCED_TABLE_NAME = 'productos' AND REFERENCED_COLUMN_NAME = 'id'))
);
SET @free := (
    SELECT COUNT(*) = 0
    FROM migrations
    WHERE migration IN (
        '2026_11_01_014019_add_business_operation_site_scope_columns',
        '2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types'
    )
);
SET @batch_nuevo := (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

SELECT DATABASE() AS base_activa, @base_correcta AS base_ok,
       @scope_columns_ok AS scope_columns_ok, @scope_indexes_ok AS scope_indexes_ok,
       @return_fk_ok AS return_fk_ok, @free AS migraciones_libres;

START TRANSACTION;
INSERT INTO migrations (migration, batch)
SELECT '2026_11_01_014019_add_business_operation_site_scope_columns', @batch_nuevo
FROM (SELECT 1 AS guardia) AS guardia
WHERE @base_correcta AND @scope_columns_ok AND @scope_indexes_ok AND @return_fk_ok AND @free;
INSERT INTO migrations (migration, batch)
SELECT '2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types', @batch_nuevo
FROM (SELECT 1 AS guardia) AS guardia
WHERE @base_correcta AND @scope_columns_ok AND @scope_indexes_ok AND @return_fk_ok AND @free;
COMMIT;

SELECT migration, batch
FROM migrations
WHERE migration IN (
    '2026_11_01_014019_add_business_operation_site_scope_columns',
    '2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types'
)
ORDER BY migration;

