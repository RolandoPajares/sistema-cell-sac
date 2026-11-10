# Línea base de esquema MariaDB

## Estado

- Framework verificado: Laravel 13.35.0.
- Archivo estructural: `mysql-schema.sql`.
- Objetos incluidos: 112 tablas, 2 vistas y 8 triggers.
- El archivo no contiene datos de negocio ni registros de `migrations`.
- Este documento es descriptivo; no es cargado ni ejecutado automáticamente por Laravel.

## Comportamiento real de Laravel

`php artisan migrate` busca `database/schema/mysql-schema.sql` antes de ejecutar migraciones. Si lo encuentra, elimina el repositorio de migraciones, carga el SQL y después ejecuta las migraciones que no aparecen registradas en la tabla `migrations`.

Como esta línea base estructural no contiene filas de `migrations`, Laravel intentaría ejecutar todas las migraciones históricas. Por eso todavía no debe utilizarse como instalación automática final.

La integración segura requiere una fase posterior que establezca un manifiesto oficial de migraciones cubiertas y ejecute únicamente migraciones posteriores o transformaciones de datos expresamente aprobadas. No se deben insertar filas ficticias en la base activa.

## Matriz de migraciones

| Migración | Clasificación frente al esquema | Tratamiento para una instalación nueva |
|---|---|---|
| `0001_01_01_000000_create_users_table.php` | Estructura cubierta; conserva `users`, `password_reset_tokens` y `sessions` | No repetir después del baseline |
| `0001_01_01_000001_create_cache_table.php` | Estructura cubierta | No repetir |
| `0001_01_01_000002_create_jobs_table.php` | Estructura cubierta | No repetir |
| `2026_10_10_171500_create_cell_sac_commerce_tables.php` | Histórica parcialmente incompatible; crea nombres antiguos como `producto_proveedor`, `cotizacion_detalles` y `detalle_pedidos` | No ejecutar automáticamente; requiere conciliación explícita |
| `2026_10_10_172618_migrate_legacy_users_to_laravel_users.php` | Migración de datos entre `usuarios` y `users` | No ejecutar sin decisión de compatibilidad |
| `2026_10_28_203424_create_devoluciones_garantias_table.php` | Estructura cubierta | No repetir |
| `2026_11_01_000753_create_enterprise_rbac_tables.php` | Estructura RBAC cubierta | No repetir; datos iniciales van por seeder aprobado |
| `2026_11_01_000754_add_admin_profile_security_fields.php` | Columnas y tablas de soporte cubiertas | No repetir |
| `2026_11_01_013915_create_rbac_seed_versions_table.php` | Tabla no presente en el SQL actual | Ejecutar o incorporar en una revisión posterior del baseline |
| `2026_11_01_014019_add_business_operation_site_scope_columns.php` | Estructura y restricciones cubiertas | No repetir |
| `2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types.php` | Correctiva; resultado estructural cubierto | No repetir; conservar como evidencia histórica |
| `2026_11_01_015104_create_ecommerce_extension_tables.php` | Estructura cubierta | No repetir |
| `2026_11_01_015105_create_stock_sales_device_tables.php` | Estructura cubierta | No repetir |
| `2026_11_01_015106_create_electronic_billing_tables.php` | Estructura cubierta | No repetir |
| `2026_11_01_015107_create_finance_accounting_tables.php` | Estructura cubierta | No repetir |
| `2026_11_01_015108_create_logistics_returns_tables.php` | Estructura cubierta | No repetir |
| `2026_11_01_015109_create_support_legal_integration_tables.php` | Estructura cubierta | No repetir |
| `2026_11_01_015110_add_commerce_integration_columns.php` | Columnas e índices actuales cubiertos | No repetir |
| `2026_11_01_015111_backfill_main_organization_and_stock.php` | Datos iniciales y backfill; no representado por SQL estructural | Sustituir por procedimiento/seeder idempotente aprobado |
| `2026_11_01_015112_link_commerce_enterprise_foreign_keys.php` | Claves foráneas cubiertas | No repetir |
| `2026_11_01_015113_standardize_audit_events.php` | Estructura de auditoría cubierta | No repetir |
| `2026_11_03_192348_agregar_canal_y_observaciones_a_ventas.php` | Columnas cubiertas | No repetir |
| `2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias.php` | Columnas cubiertas | No repetir |
| `2026_11_05_120000_enable_admin_responsibles_screen.php` | Datos RBAC y menú | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_120100_create_responsables_organizacionales_table.php` | Estructura cubierta | No repetir |
| `2026_11_05_120200_enable_configuration_screens.php` | Datos RBAC y menú | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_120300_enable_billing_query_screen.php` | Datos RBAC y menú | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_120400_enable_inventory_read_screens.php` | Datos RBAC y menú | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_120500_enable_security_sessions_screen.php` | Datos RBAC y menú | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_121000_enable_refunds_export_permission.php` | Datos RBAC y permisos | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_121100_enable_support_export_permission.php` | Datos RBAC y permisos | Ejecutar como etapa de catálogo inicial o seeder |
| `2026_11_05_121200_unificar_menu_operaciones_comerciales.php` | Reorganización de datos de menú | Ejecutar solo como transformación controlada |
| `2026_11_05_130000_reduce_security_catalog_to_six_interfaces.php` | Eliminación/actualización de datos RBAC | Ejecutar solo como transformación controlada |
| `2026_11_05_140000_add_tipo_documento_to_clientes.php` | Columna cubierta | No repetir |
| `2026_11_05_140000_remove_organization_submenus.php` | Eliminación/actualización de datos RBAC | Ejecutar solo como transformación controlada |
| `2026_11_05_235900_reduce_analysis_audit_menu_to_two_interfaces.php` | Eliminación/actualización de datos RBAC y auditoría | Ejecutar solo como transformación controlada |
| `2026_11_06_100000_create_product_reviews_favorites_tables.php` | Estructura cubierta | No repetir |
| `2026_11_06_100100_normalize_catalog_table_names.php` | Resultado estructural normalizado cubierto; transformación histórica | No repetir; conservar como evidencia |
| `2026_11_09_000100_create_system_configurations_table.php` | Tabla no presente en el SQL actual | Migración posterior al baseline |
| `2026_11_10_144352_create_dynamic_profile_fields_tables.php` | Estructura cubierta | No repetir |
| `2026_11_10_150656_add_guest_checkout_fields_to_pedidos_table.php` | Columnas cubiertas | No repetir |

El repositorio contiene actualmente 41 archivos de migración en el sistema de archivos: 39 versionados y 2 cambios locales no versionados. Los dos archivos no versionados se conservaron y se incluyeron en esta matriz.

## Registro huérfano

La base activa contiene el registro `2026_11_05_150000_limitar_ecommerce_marketing_a_cuatro_opciones`, pero no existe el archivo PHP correspondiente. No se debe marcar ni eliminar nada automáticamente. Debe conservarse como incidencia histórica y resolverse mediante una decisión de auditoría antes de crear una instalación oficial.

## Procedimiento futuro de instalación

1. Crear una base MariaDB nueva y verificablemente aislada.
2. Importar `mysql-schema.sql`.
3. Aplicar un mecanismo oficial de baseline que registre únicamente migraciones cuya cobertura haya sido demostrada.
4. Ejecutar las migraciones estructurales posteriores, actualmente identificadas como `create_rbac_seed_versions_table` y `create_system_configurations_table`, después de confirmar su compatibilidad.
5. Ejecutar un seeder idempotente para módulos, pantallas, permisos y menús.
6. Crear el administrador inicial mediante entrada segura de credenciales, nunca desde valores fijos.
7. Verificar autenticación, RBAC, catálogo, pedidos, ventas, finanzas e inventario.

Este procedimiento no se ha ejecutado porque no existe una MariaDB temporal independiente verificada.
