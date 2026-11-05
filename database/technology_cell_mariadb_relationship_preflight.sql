-- Preflight de relaciones críticas, solo lectura.
USE `md_tecnologia_digital_cell`;

SELECT esperado,
       IF(EXISTS (
           SELECT 1
           FROM information_schema.KEY_COLUMN_USAGE k
           WHERE k.CONSTRAINT_SCHEMA = DATABASE()
             AND k.TABLE_NAME = rel.tabla_hija
             AND k.COLUMN_NAME = rel.columna_hija
             AND k.REFERENCED_TABLE_NAME = rel.tabla_padre
             AND k.REFERENCED_COLUMN_NAME = rel.columna_padre
       ), 'OK', 'FALTA') AS estado
FROM (
    SELECT 'sedes.empresa_id -> empresas.id' esperado, 'sedes' tabla_hija, 'empresa_id' columna_hija, 'empresas' tabla_padre, 'id' columna_padre
    UNION ALL SELECT 'almacenes.sede_id -> sedes.id', 'almacenes', 'sede_id', 'sedes', 'id'
    UNION ALL SELECT 'productos.categoria_id -> categorias.id', 'productos', 'categoria_id', 'categorias', 'id'
    UNION ALL SELECT 'variantes_productos.producto_id -> productos.id', 'variantes_productos', 'producto_id', 'productos', 'id'
    UNION ALL SELECT 'detalles_compras.compra_id -> compras.id', 'detalles_compras', 'compra_id', 'compras', 'id'
    UNION ALL SELECT 'detalles_compras.variante_id -> variantes_productos.id', 'detalles_compras', 'variante_id', 'variantes_productos', 'id'
    UNION ALL SELECT 'detalles_pedidos.pedido_id -> pedidos.id', 'detalles_pedidos', 'pedido_id', 'pedidos', 'id'
    UNION ALL SELECT 'detalles_pedidos.producto_id -> productos.id', 'detalles_pedidos', 'producto_id', 'productos', 'id'
    UNION ALL SELECT 'detalles_ventas.venta_id -> ventas.id', 'detalles_ventas', 'venta_id', 'ventas', 'id'
    UNION ALL SELECT 'detalles_ventas.producto_id -> productos.id', 'detalles_ventas', 'producto_id', 'productos', 'id'
    UNION ALL SELECT 'existencias_almacen.almacen_id -> almacenes.id', 'existencias_almacen', 'almacen_id', 'almacenes', 'id'
    UNION ALL SELECT 'existencias_almacen.variante_id -> variantes_productos.id', 'existencias_almacen', 'variante_id', 'variantes_productos', 'id'
    UNION ALL SELECT 'movimientos_inventario.producto_id -> productos.id', 'movimientos_inventario', 'producto_id', 'productos', 'id'
    UNION ALL SELECT 'movimientos_inventario.almacen_id -> almacenes.id', 'movimientos_inventario', 'almacen_id', 'almacenes', 'id'
    UNION ALL SELECT 'roles_permisos.rol_id -> roles.id', 'roles_permisos', 'rol_id', 'roles', 'id'
    UNION ALL SELECT 'roles_permisos.permiso_id -> permisos.id', 'roles_permisos', 'permiso_id', 'permisos', 'id'
    UNION ALL SELECT 'usuarios_roles.usuario_id -> usuarios.id', 'usuarios_roles', 'usuario_id', 'usuarios', 'id'
    UNION ALL SELECT 'usuarios_roles.rol_id -> roles.id', 'usuarios_roles', 'rol_id', 'roles', 'id'
    UNION ALL SELECT 'pagos.medio_pago_id -> medios_pago.id', 'pagos', 'medio_pago_id', 'medios_pago', 'id'
    UNION ALL SELECT 'documentos_electronicos.venta_id -> ventas.id', 'documentos_electronicos', 'venta_id', 'ventas', 'id'
) AS rel;

