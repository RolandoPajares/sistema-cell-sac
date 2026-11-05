# Matriz de cobertura QA — evidencia de la rama `agent/documentacion-pruebas`

Fecha de corte: 2026-11-05  
Commits de evidencia: `aa776e6`, `dee2e36`

Esta matriz registra únicamente resultados observados en esta rama. No declara como funcional una característica que no pudo ejecutarse.

| Área | Prueba o alcance | Evidencia | Estado | Incidencia |
|---|---|---:|---|---|
| Front Controller | `ArquitecturaMvcTest.php` — bootstrap y delegación de solicitudes | 1 caso, incluido en 2.629 aserciones | APROBADA | Ninguna |
| MVC / rutas | Controladores importados por `routes/web.php` tienen archivo PSR-4 | 1 caso | APROBADA | Ninguna |
| MVC / vistas | Blade sin `DB::`, PDO, mysqli ni SQL directo | 1 caso | APROBADA | Ninguna |
| MVC / servicios | Servicios sin renderizado directo de vistas | 1 caso | APROBADA | Ninguna |
| Seguridad web | Formularios mutantes Blade incluyen CSRF | 1 caso | APROBADA | Los formularios GET se excluyen correctamente |
| Seguridad web | Grupo `/gestion` exige autenticación, cuenta activa, sesión y permiso de ruta | 1 caso | APROBADA | Ninguna |
| Seguridad HTTP | Operaciones mutantes principales no están declaradas como GET | 1 caso | APROBADA | Ninguna |
| Seguridad frontend | Escape HTML en asistente, ventas, CSV y descripción de producto | `SeguridadFrontendTest.php` — 6 casos, 147 aserciones | APROBADA | Ninguna |
| Cliente HTTP | CSRF, `Accept: application/json` y errores no-2xx | 1 caso frontend | APROBADA | Ninguna |
| JavaScript seguro | Ausencia de `eval()` y `document.write` | 1 caso frontend | APROBADA | Ninguna |
| API catálogo | Lectura versionada, rate limiting, recursos JSON y producto activo | `ApiContractTest.php` — 3 casos, 166 aserciones junto con frontend | APROBADA | Ninguna |
| RBAC / vistas administrativas | `RbacDinamicoTest.php`, `ViewsTest.php`, `DispositivosControllerTest.php` | 29 casos intentados | BLOQUEADA | El setup SQLite falla al ejecutar `information_schema.table_constraints` de la migración de responsables |
| Unit / regresión | Suite `tests/Unit` | 126 casos | APROBADA | 118 aprobados, 3.688 aserciones; 8 omitidos por ausencia de MariaDB aislada |
| MariaDB aislada | Suites MariaDB aisladas seleccionadas | 6 casos | NO EJECUTADA | 6 omitidos: no existen variables `TECHNOLOGY_CELL_QA_*` para una instancia aislada |
| Playwright | Login, CRUD, consola y respuestas HTTP | — | NO EJECUTADA | No hay suite ni dependencia Playwright en este worktree; tampoco se levantó servidor aislado |

## Comandos reproducibles

```text
php artisan test tests/Unit/ArquitecturaMvcTest.php tests/Unit/SeguridadEstructuralTest.php --colors=never
php artisan test tests/Feature/RbacDinamicoTest.php tests/Feature/Gestion/ViewsTest.php tests/Feature/Paneles/Administrador/DispositivosControllerTest.php --colors=never
php artisan test tests/Unit --colors=never
php artisan test tests/Unit/MariaMigracionesReentradaAisladaTest.php tests/Unit/MariaPedidosTransferenciasAisladaTest.php tests/Unit/MariaFinanzasAisladaTest.php tests/Unit/IntegracionMariaDbTechnologyCellTest.php --colors=never
```

## Pendientes de coordinación

1. Ejecutar la regresión RBAC/vistas sobre MariaDB o adaptar el setup de prueba en coordinación con el agente responsable de la migración `2026_11_05_120100_create_responsables_organizacionales_table.php`.
2. Proporcionar una instancia MariaDB descartable mediante variables `TECHNOLOGY_CELL_QA_*`; no usar la base empresarial.
3. Definir y autorizar una instancia de navegador aislada antes de incorporar Playwright.

La suite Unit se ejecutó con una `APP_KEY` efímera de proceso y con `public/build/manifest.json` generado localmente; no se modificó `.env`.
