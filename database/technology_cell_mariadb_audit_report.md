# Auditoría MariaDB — Technology Cell

Fecha de auditoría: 2026-11-05. Rama: `agent/mariadb`. Origen: `4db2075`.

## Resultado

La base activa confirmada fue `md_tecnologia_digital_cell` en MariaDB `10.4.32`, servidor `127.0.0.1:15327`. No se ejecutó DDL en la base activa. El esquema activo ya contiene las 97 tablas del dump de referencia, con columnas compatibles en las tablas comunes, y además 15 tablas aditivas. No se comprobó una estructura indispensable ausente que justifique DDL activo.

`mysqlcheck --check --extended` devolvió `OK` para todas las tablas activas. La base activa contiene 377 claves foráneas; el dump contiene 356 porque no incluye las extensiones activadas posteriormente.

## Desincronización comprobada

La tabla `migrations` activa registraba inicialmente 11 migraciones: las tres migraciones Laravel base y las ocho migraciones `2026_11_05_12xxxx`. Hay 31 archivos de migración en el repositorio; 20 estaban pendientes.

No es seguro marcar las 18 migraciones históricas como aplicadas: varias crean nombres legacy (`producto_variantes`, `compra_detalles`, `cotizacion_detalles`, `detalle_pedidos`) y las migraciones posteriores también referencian esos nombres. El esquema real y el dump usan `variantes_productos`, `detalles_compras`, `detalles_cotizaciones` y `detalles_pedidos`. Los modelos y servicios actuales usan los nombres canónicos.

Las dos migraciones del 2026-11-03 sí coinciden exactamente con las columnas existentes (tipos, nulabilidad, defaults y orden). Fueron registradas con batch nuevo mediante `technology_cell_mariadb_mark_verified_migrations.sql`, tras validar base, columnas y ausencia de duplicados. También se verificaron y registraron `014019` (alcance organizacional) y `015103` (tipos/FK de devoluciones) mediante `technology_cell_mariadb_mark_verified_scope_migrations.sql`. Quedan 16 migraciones históricas pendientes para reconciliación coordinada.

Durante la primera ejecución se detectó y corrigió una guardia que podía insertar una fila por el comportamiento de `MAX()` sobre un conjunto vacío; la comprobación posterior confirmó las dos filas y se normalizó su batch a `10`. La segunda guardia de índices se corrigió para contar índices compuestos distintos, y `migrate:status` confirmó ambas migraciones nuevas como `Ran` en batch `11`. No se alteraron datos de negocio.

Matriz pendiente: las 16 restantes son `2026_10_10_171500_create_cell_sac_commerce_tables`, `2026_10_10_172618_migrate_legacy_users_to_laravel_users`, `2026_10_28_203424_create_devoluciones_garantias_table`, `2026_11_01_000753_create_enterprise_rbac_tables`, `2026_11_01_000754_add_admin_profile_security_fields`, `2026_11_01_013915_create_rbac_seed_versions_table` y `2026_11_01_015104`–`015113`. El grupo `2026_11_01_015104`–`015113` referencia nombres legacy o estructuras que deben compararse migración por migración; no se puede marcar en bloque.

Mapeo de bloqueos principales: `producto_variantes`→`variantes_productos`, `compra_detalles`→`detalles_compras`, `cotizacion_detalles`→`detalles_cotizaciones`, `detalle_pedidos`→`detalles_pedidos`, `carrito_detalles`→`detalles_carritos`, `venta_detalles`→`detalles_ventas`, `documento_electronico_detalles`→`detalles_documentos_electronicos`, `cuentas_cobrar`/`cuentas_pagar`→`cuentas_por_cobrar`/`cuentas_por_pagar`, `plan_cuentas`→`planes_cuentas`, `asiento_lineas`→`lineas_asientos`, `guia_remision_detalles`→`detalles_guias_remision`, `envios_pedido`/`envio_pedido_detalles`→`envios_pedidos`/`detalles_envios_pedidos`, `devolucion_detalles`→`detalles_devoluciones`, `tickets_soporte`→`casos_soporte`, `ticket_eventos`→`eventos_casos_soporte`, `rol_permiso`→`roles_permisos`, `usuario_roles`→`usuarios_roles` y `usuario_sede_roles`→`usuarios_sedes_roles`. Estos cambios requieren reemplazo coordinado de migraciones, no alias ni tablas duplicadas.

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

## Bloqueo de pruebas Laravel

Las pruebas aisladas MariaDB se descubrieron como `6 skipped` porque requieren una instancia descartable por named pipe (`TECHNOLOGY_CELL_QA_PIPE` y `TECHNOLOGY_CELL_QA_ROOT`), no disponible en este entorno.

Al ejecutar `MigrationCoverageTest` con `APP_KEY` temporal del proceso, Laravel falló en SQLite al aplicar `2026_11_05_120100_create_responsables_organizacionales_table.php`: la migración consulta `information_schema.table_constraints`, que no existe en SQLite. La corrección debe hacerse en ese archivo compartido por el integrador, condicionando la consulta al driver MariaDB/MySQL o usando una verificación compatible con SQLite. No se modificó el archivo compartido en esta rama.

