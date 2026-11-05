# Auditoría MariaDB — Technology Cell

Fecha de auditoría: 2026-11-05. Rama: `agent/mariadb`. Origen: `4db2075`.

## Resultado

La base activa confirmada fue `md_tecnologia_digital_cell` en MariaDB `10.4.32`, servidor `127.0.0.1:15327`. No se ejecutó DDL en la base activa. El esquema activo ya contiene las 97 tablas del dump de referencia, con columnas compatibles en las tablas comunes, y además 15 tablas aditivas. No se comprobó una estructura indispensable ausente que justifique DDL activo.

`mysqlcheck --check --extended` devolvió `OK` para todas las tablas activas. La base activa contiene 377 claves foráneas; el dump contiene 356 porque no incluye las extensiones activadas posteriormente.

## Desincronización comprobada

La tabla `migrations` activa solo registra 11 migraciones: las tres migraciones Laravel base y las ocho migraciones `2026_11_05_12xxxx`. Las migraciones anteriores presentes en `database/migrations` no están registradas.

No es seguro marcar esas migraciones como aplicadas: varias crean nombres legacy (`producto_variantes`, `compra_detalles`, `cotizacion_detalles`, `detalle_pedidos`) y las migraciones posteriores también referencian esos nombres. El esquema real y el dump usan `variantes_productos`, `detalles_compras`, `detalles_cotizaciones` y `detalles_pedidos`. Los modelos y servicios actuales usan los nombres canónicos.

La única tabla legacy existente en la base activa es `compra_detalles`; está vacía y no tiene referencias activas. No se elimina ni se renombra por esta auditoría. Las tablas `producto_variantes`, `cotizacion_detalles` y `detalle_pedidos` no existen.

## Tablas adicionales activas

`cache`, `cache_locks`, `compra_detalles`, `jobs`, `job_batches`, `migrations`, `password_reset_tokens`, `sessions`, `users`, `configuraciones_tienda`, `precios_mayoristas`, `responsables_organizacionales`, `failed_jobs` y dos tablas `respaldo_tc_ecom_20261105_*`. Son adiciones observadas, no creadas por este agente.

## Respaldo

Se generó fuera del repositorio y fuera del directorio público:

`C:\Users\HP\Documents\Codex\2026-11-05\mariadb-backups\md_tecnologia_digital_cell-preaudit-20261105-162455.sql`

Tamaño: 654312 bytes. SHA-256: `334C6B4385632E697C0D4F59A336C8B781DDE6B4E6F942F25C65E68977982741`.

El respaldo fue producido con `--single-transaction --routines --events --triggers --hex-blob`. Se verificó que contiene definiciones de tablas, inserciones y objetos auxiliares. No se restauró sobre la base activa ni se sobrescribió la base aislada; queda pendiente una restauración física en un segundo entorno autorizado si el integrador la requiere.

## Validación aislada

Se comprobó que `tc_cell_sac_validacion_20261105` no existía, se creó una sola vez y se importó `md_tecnologia_digital_cell.sql`. Resultado: 97 tablas base y 2 vistas en MariaDB `10.4.32`.

La prueba CRUD aislada insertó empresa, sede, almacén, categoría, producto, variante, proveedor, compra y detalle; actualizó el detalle, verificó el total `200.00`, eliminó los registros hijos y padres y ejecutó `ROLLBACK`. La primera fixture fue rechazada por la FK compuesta categoría/empresa; se corrigió la fixture, confirmando que la integridad de alcance empresarial está activa.

## SQL ejecutado

- Consultas de identidad, sesiones, tablas, migraciones, columnas, índices y FK en modo lectura.
- Creación e importación de la base aislada autorizada.
- `mysqldump` completo de la base activa.
- `mysqlcheck --check --extended md_tecnologia_digital_cell`.
- Prueba CRUD transaccional aislada con rollback.

No se ejecutó `migrate`, `migrate:fresh`, `migrate:reset`, `migrate:rollback`, `db:wipe`, seed masivo ni DDL sobre `md_tecnologia_digital_cell`.

## Necesidades del integrador

1. Adoptar el dump/esquema canónico como fuente para instalación limpia.
2. Reconciliar las migraciones históricas mediante migraciones no-op/ledger explícito o una secuencia nueva que valide la estructura existente; no usar `migrate` global contra la base activa.
3. Mantener los nombres canónicos usados por Eloquent y actualizar las migraciones legacy en una rama coordinada.
4. Decidir explícitamente el destino de `compra_detalles` antes de cualquier renombre o eliminación; contiene cero filas, pero cambiarla sigue siendo una operación estructural.
5. Autorizar un segundo entorno aislado si se requiere probar restauración completa del respaldo sin reutilizar una base existente.

