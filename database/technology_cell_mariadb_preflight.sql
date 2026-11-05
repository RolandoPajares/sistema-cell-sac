-- Preflight reproducible, solo lectura.
-- Uso: mysql --database=<base> < technology_cell_mariadb_preflight.sql

SELECT DATABASE() AS base_activa,
       VERSION() AS version_mariadb,
       IF(DATABASE() IN ('md_tecnologia_digital_cell', 'tc_cell_sac_validacion_20261105'), 'OK', 'ABORTAR') AS base_autorizada;

SELECT IF(VERSION() LIKE '10.4.32-MariaDB%', 'OK', 'ABORTAR') AS version_compatible;

SELECT COUNT(*) AS tablas_base,
       SUM(ENGINE = 'InnoDB') AS tablas_innodb,
       SUM(TABLE_NAME IN ('variantes_productos', 'detalles_compras', 'detalles_cotizaciones', 'detalles_pedidos')) AS tablas_canonicas_comercio
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE';

SELECT nombre_requerido,
       IF(EXISTS (
           SELECT 1 FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = nombre_requerido
       ), 'OK', 'FALTA') AS estado
FROM (
    SELECT 'empresas' AS nombre_requerido UNION ALL SELECT 'sedes' UNION ALL SELECT 'almacenes'
    UNION ALL SELECT 'usuarios' UNION ALL SELECT 'roles' UNION ALL SELECT 'permisos'
    UNION ALL SELECT 'roles_permisos' UNION ALL SELECT 'usuarios_roles' UNION ALL SELECT 'usuarios_sedes_roles'
    UNION ALL SELECT 'productos' UNION ALL SELECT 'variantes_productos' UNION ALL SELECT 'dispositivos'
    UNION ALL SELECT 'compras' UNION ALL SELECT 'detalles_compras' UNION ALL SELECT 'ventas'
    UNION ALL SELECT 'detalles_ventas' UNION ALL SELECT 'pedidos' UNION ALL SELECT 'detalles_pedidos'
    UNION ALL SELECT 'cotizaciones' UNION ALL SELECT 'detalles_cotizaciones'
    UNION ALL SELECT 'existencias_almacen' UNION ALL SELECT 'movimientos_inventario'
    UNION ALL SELECT 'cuentas_bancarias' UNION ALL SELECT 'pagos'
    UNION ALL SELECT 'documentos_electronicos' UNION ALL SELECT 'detalles_documentos_electronicos'
) AS requeridas;

SELECT nombre_legacy,
       IF(EXISTS (
           SELECT 1 FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = nombre_legacy
       ), 'REVISAR', 'AUSENTE_CORRECTO') AS estado
FROM (
    SELECT 'producto_variantes' AS nombre_legacy UNION ALL SELECT 'compra_detalles'
    UNION ALL SELECT 'cotizacion_detalles' UNION ALL SELECT 'detalle_pedidos'
    UNION ALL SELECT 'carrito_detalles' UNION ALL SELECT 'venta_detalles'
    UNION ALL SELECT 'documento_electronico_detalles' UNION ALL SELECT 'cuentas_cobrar'
    UNION ALL SELECT 'cuentas_pagar' UNION ALL SELECT 'plan_cuentas' UNION ALL SELECT 'asiento_lineas'
    UNION ALL SELECT 'guia_remision_detalles' UNION ALL SELECT 'envios_pedido'
    UNION ALL SELECT 'envio_pedido_detalles' UNION ALL SELECT 'devolucion_detalles'
    UNION ALL SELECT 'tickets_soporte' UNION ALL SELECT 'ticket_eventos'
    UNION ALL SELECT 'rol_permiso' UNION ALL SELECT 'usuario_roles' UNION ALL SELECT 'usuario_sede_roles'
) AS legacy;

SELECT COUNT(*) AS claves_foraneas
FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL;

SELECT TABLE_NAME, ENGINE, TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('empresas', 'sedes', 'almacenes', 'productos', 'variantes_productos',
                     'compras', 'detalles_compras', 'ventas', 'detalles_ventas', 'pedidos',
                     'detalles_pedidos', 'existencias_almacen', 'movimientos_inventario',
                     'roles', 'permisos', 'roles_permisos', 'usuarios_roles', 'usuarios_sedes_roles',
                     'pagos', 'documentos_electronicos');

