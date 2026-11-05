# Requisitos de ejecución integrada QA

Este documento es un checklist operativo para el integrador. No sustituye resultados de pruebas.

## MariaDB aislada

La instancia debe cumplir simultáneamente:

- MariaDB `10.4.32-MariaDB`.
- `skip_networking=1`.
- Named pipe con patrón `tc_ventas_[a-f0-9]{32}`.
- Directorio temporal bajo `%TEMP%\technology_cell_ventas_pipe_*`.
- `@@datadir` bajo `%TEMP%\technology_cell_mariadb_10432_*\datos`.
- Base temporal con patrón `tc_ventas_laravel_[a-z0-9]+`.
- Sin reutilizar la base empresarial ni `tests/database.sqlite`.

Variables requeridas para el proceso Pest:

```text
TECHNOLOGY_CELL_QA_PIPE
TECHNOLOGY_CELL_QA_ROOT
TECHNOLOGY_CELL_QA_DATABASE
```

Antes de ejecutar escrituras, el Agente 2 debe confirmar que la instancia está libre y que solo QA la usará durante la ejecución.

## Comandos

Desde el worktree asignado:

```powershell
$env:APP_KEY='base64:CLAVE_EFIMERA_SOLO_PARA_EL_PROCESO'
php artisan test tests/Unit/IntegracionMariaDbTechnologyCellTest.php --colors=never
php artisan test tests/Unit/MariaMigracionesReentradaAisladaTest.php --colors=never
php artisan test tests/Unit/MariaPedidosTransferenciasAisladaTest.php --colors=never
php artisan test tests/Unit/MariaFinanzasAisladaTest.php --colors=never
```

Las suites aisladas validan identidad del servidor, esquema, transacciones y rollback. Los tests que crean datos deben ejecutarse con la base descartable recién creada por el runner; nunca contra `md_tecnologia_digital_cell`.

## ORM/DAO artesanal

El Agente 1 debe entregar antes de ejecutar:

1. Clases DAO/entidades y namespaces definitivos.
2. Contrato de conexión PDO y configuración de errores.
3. Tablas, claves y relaciones usadas por cada DAO.
4. Excepciones esperadas y reglas transaccionales.
5. Una operación de lectura y una de escritura reproducibles.

QA validará consultas preparadas, mapeo fila-entidad, relaciones, CRUD, rollback ante excepción y rechazo de SQL injection en la misma base descartable.

## RBAC integrado

El Agente 4 debe entregar fixtures finales para:

- administrador autorizado;
- usuario sin permiso con HTTP 403;
- alcance por empresa y sede;
- asignación y revocación de rol;
- sesión y CSRF;
- entrada XSS y escalamiento de privilegios;
- registro de auditoría.

El actual bloqueo Feature se reproduce porque la migración `2026_11_05_120100_create_responsables_organizacionales_table.php` consulta `information_schema.table_constraints` mientras PHPUnit usa SQLite. La solución debe coordinarse con el propietario de esa migración o ejecutarse en MariaDB aislada.

## Playwright

Requisitos antes de añadir ejecución real:

- URL de una instancia levantada desde este worktree, no `localhost:18080` si apunta a otra versión.
- Credenciales QA sintéticas y alcance autorizado.
- Base MariaDB aislada disponible.
- Dependencia Playwright y navegador instalados en el entorno de ejecución.
- Permiso explícito para levantar/detener la instancia aislada.

Flujos mínimos: login, dashboard, un CRUD real, búsqueda/filtro/paginación, desactivación, validación, 403, consola sin errores y exportación aplicable.

## Estado actual

| Bloque | Estado | Responsable de desbloqueo |
|---|---|---|
| Tests Unit y seguridad frontend/API | APROBADO | QA |
| Feature sobre SQLite | BLOQUEADO | Propietario de migración / integrador |
| Feature sobre MariaDB aislada | NO EJECUTADO | Agente 2 |
| ORM/DAO artesanal | PENDIENTE DE CONTRATO | Agente 1 |
| RBAC integrado final | PENDIENTE DE FIXTURES | Agente 4 |
| Playwright | PENDIENTE DE ENTORNO | Integrador |
