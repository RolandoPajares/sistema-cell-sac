# Procedimiento reproducible de instalación y actualización

## Fuente canónica

La fuente de instalación limpia es `md_tecnologia_digital_cell.sql`, validada en MariaDB `10.4.32`. El dump crea 97 tablas y 2 vistas con los nombres canónicos del código actual.

Comando de instalación limpia en una base nueva autorizada:

```powershell
$mysql = 'C:\xampp\mysql\bin\mysql.exe'
Get-Content -Raw .\md_tecnologia_digital_cell.sql |
  & $mysql --no-defaults --protocol=tcp --host=127.0.0.1 --port=15327 --user=root --database=BASE_NUEVA --default-character-set=utf8mb4
Get-Content -Raw .\database\technology_cell_mariadb_preflight.sql |
  & $mysql --no-defaults --protocol=tcp --host=127.0.0.1 --port=15327 --user=root --database=BASE_NUEVA --batch --raw
```

`BASE_NUEVA` debe ser una base nueva autorizada; nunca se debe reutilizar una base existente ni sobrescribirla.

## Actualización segura de la base empresarial

1. Confirmar `SELECT DATABASE(), VERSION()` y que la base sea exactamente `md_tecnologia_digital_cell`.
2. Confirmar que no haya otra operación estructural concurrente.
3. Ejecutar `mysqldump --single-transaction --routines --events --triggers --hex-blob` fuera del repositorio y guardar SHA-256.
4. Ejecutar `technology_cell_mariadb_preflight.sql` y conservar su salida.
5. No ejecutar `php artisan migrate` global: el historial contiene migraciones legacy incompatibles.
6. Aplicar solo scripts de reconciliación explícitamente aprobados, con guardas de base, estructura, tipos, FK y ausencia de duplicados.
7. Registrar en `migrations` únicamente migraciones cuyo `up()` sea demostrablemente no-op sobre el esquema existente.
8. Ejecutar `migrate:status`, `mysqlcheck --check --extended` y las pruebas MariaDB aisladas.
9. Si una migración requiere DDL, backfill, renombre, eliminación o modificación de una columna existente, detenerse y preparar una migración canónica separada para aprobación del integrador.

## Clasificación individual de las 16 pendientes

| Migración | Clasificación | Motivo y acción |
|---|---|---|
| `2026_10_10_171500_create_cell_sac_commerce_tables` | Sustituir | Crea nombres legacy y tablas duplicables; reemplazar por instalación desde dump canónico. |
| `2026_10_10_172618_migrate_legacy_users_to_laravel_users` | Excluir del update | Puede copiar usuarios y alterar FK; requiere decisión de identidad histórica y validación de datos. |
| `2026_10_28_203424_create_devoluciones_garantias_table` | Adaptar | La tabla existe, pero usa `creado_por_usuario_id` en lugar de `creado_por`; no es equivalente. |
| `2026_11_01_000753_create_enterprise_rbac_tables` | Sustituir | Crea `rol_permiso`, `usuario_roles` y `usuario_sede_roles`; el esquema canónico usa tablas pluralizadas distintas. |
| `2026_11_01_000754_add_admin_profile_security_fields` | Sustituir | Espera `sesiones_usuario` y `notifications`; el sistema usa `sesiones_usuarios` y `notificaciones`. |
| `2026_11_01_013915_create_rbac_seed_versions_table` | Adaptar | Espera `rbac_seed_versions`; el esquema canónico usa `versiones_inicializacion_control_acceso`. |
| `2026_11_01_015104_create_ecommerce_extension_tables` | Sustituir | Usa `carrito_detalles` y FK a `producto_variantes`; existen equivalentes canónicos diferentes. |
| `2026_11_01_015105_create_stock_sales_device_tables` | Sustituir | Usa `transferencias_stock`, `venta_detalles` y `producto_variantes`; existen tablas canónicas ya pobladas/configuradas. |
| `2026_11_01_015106_create_electronic_billing_tables` | Sustituir | Usa `documento_electronico_detalles`; el esquema canónico usa `detalles_documentos_electronicos`. |
| `2026_11_01_015107_create_finance_accounting_tables` | Sustituir | Usa `cuentas_cobrar`, `cuentas_pagar`, `plan_cuentas` y `asiento_lineas`; el esquema canónico usa nombres plurales. |
| `2026_11_01_015108_create_logistics_returns_tables` | Sustituir | Usa nombres singulares y `detalle_pedidos`; colisionaría semánticamente con tablas canónicas. |
| `2026_11_01_015109_create_support_legal_integration_tables` | Sustituir | Usa `tickets_soporte`; el esquema canónico usa `casos_soporte` y tablas de eventos distintas. |
| `2026_11_01_015110_add_commerce_integration_columns` | Excluir del update | Intenta alterar tablas legacy inexistentes; trasladar columnas requiere matriz por tabla canónica. |
| `2026_11_01_015111_backfill_main_organization_and_stock` | Excluir del update | Es un backfill de datos y referencia `producto_variantes`; requiere plan de datos separado y autorización. |
| `2026_11_01_015112_link_commerce_enterprise_foreign_keys` | Sustituir | Agrega FK sobre tablas legacy; debe reemplazarse por verificación de FK canónicas. |
| `2026_11_01_015113_standardize_audit_events` | Adaptar | El esquema tiene columnas de auditoría, pero triggers/tablas objetivo no coinciden exactamente. |

## Responsables y SQLite

El integrador debe incorporar el parche documentado en `technology_cell_mariadb_shared_patch_responsables_sqlite.md`: `$addChecks` debe retornar cuando `DB::getDriverName() !== 'mysql'`. MariaDB mantiene la validación `information_schema`; SQLite puede ejecutar la creación Blueprint sin consultar metadatos MariaDB.

## Pruebas

La base aislada autorizada `tc_cell_sac_validacion_20261105` ya fue creada desde el dump. El preflight, `mysqlcheck` y el CRUD transaccional empresa/sede/almacén/producto/variante/compra/detalle fueron aprobados. Para ejecutar la suite Pest MariaDB faltan `TECHNOLOGY_CELL_QA_PIPE`, `TECHNOLOGY_CELL_QA_ROOT` y una instancia named-pipe exclusiva; no se deben inventar ni reutilizar credenciales.

El script `technology_cell_mariadb_relationship_preflight.sql` comprobó 20 relaciones críticas de organización, catálogo, compras, ventas, inventario, RBAC, pagos y facturación en la base activa y en la aislada; todas devolvieron `OK`.

