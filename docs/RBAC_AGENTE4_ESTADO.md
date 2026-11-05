# Estado RBAC — Agente 4

## Implementado en `agent/rbac`

- CRUD operativo de usuarios: alta, edición, activación/desactivación, correo único, validación de contraseña y auditoría.
- Asignación global o por sede desde la interfaz, con validación de sede activa, alcance y delegación.
- CRUD de roles personalizados y matriz de permisos basada en el catálogo persistido.
- Protección del rol administrador y del último administrador global.
- Prevención de desactivación de roles con asignaciones activas globales o por sede.
- Prevención de cambios de alcance con asignaciones activas incompatibles.
- Prevención de asignar o revocar roles sobre cuentas fuera del alcance delegable del actor.
- Menús y pantallas protegidos por autorización backend; las pantallas RBAC indispensables no pueden deshabilitarse.
- Formularios con CSRF de Laravel y salida escapada por Blade.

## Pruebas ejecutadas

- `php -l` sobre controladores, Form Requests y pruebas modificadas: correcto.
- `git diff --check`: correcto.
- `composer install --no-interaction --prefer-dist`: correcto; instaló las versiones de `composer.lock` y generó `vendor/autoload.php`.
- `php artisan test tests/Unit/RbacAuthorizationTest.php`: 12/12, 30 aserciones.
- Batería combinada de autorización, 403, sesiones y asignaciones: 22/22, 151 aserciones.
- `php artisan test tests/Unit/EsquemaEspanolRbacTest.php`: 34/34, 539 aserciones.
- Suite unitaria combinada `RbacAuthorizationTest` + `EsquemaEspanolRbacTest`: 46/46, 569 aserciones.
- `vendor/bin/pint --test` sobre controladores, requests y seguridad RBAC: correcto.
- `npm run build`: correcto; generó `public/build/manifest.json` para renderizado de vistas.
- `php artisan test --filter=RbacDinamicoTest`: ejecutado; los casos Feature no llegan a RBAC porque la migración compartida consulta `information_schema.table_constraints` usando conexión SQLite.

## Pendientes externos

1. Ejecutar la suite Feature contra la instancia MariaDB aislada coordinada por el Agente 2; las instancias temporales detectadas actualmente no están activas.
2. Verificar allí los flujos de creación de rol, asignación, revocación, aislamiento empresa/sede, CSRF y auditoría.
3. Integrar los commits de esta rama y repetir las pruebas conjuntas; no se ejecutaron migraciones ni escrituras sobre la base activa.

## Commits

- `8dfe79d7a3a6697dc8e2b39b199cdb3d94f44e25` — flujos gráficos de usuarios y sedes.
- `9255e020b61dd73ee90a51dd96029c89415f8fe1` — integridad de asignaciones de roles.
- `c7bb3e0451cb6690fa58a1de760536b56cc6c202` — límites de administración RBAC.
- `c5fbf8bd870db74e875cfedf25a32d52cf81875d` — fallback seguro de sede única.
