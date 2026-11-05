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
- `php artisan test --filter=RbacDinamicoTest`: no pudo iniciar porque el worktree no contiene `vendor/autoload.php`.

## Pendientes externos

1. Instalar las dependencias Composer del worktree o proporcionar el `vendor` autorizado.
2. Ejecutar la suite focalizada y la suite completa contra la base aislada del Agente 2.
3. Verificar en entorno aislado los flujos de creación de rol, asignación, revocación, aislamiento empresa/sede, CSRF y auditoría.
4. Integrar los commits de esta rama y repetir las pruebas conjuntas; no se ejecutaron migraciones ni escrituras sobre la base activa.

## Commits

- `8dfe79d7a3a6697dc8e2b39b199cdb3d94f44e25` — flujos gráficos de usuarios y sedes.
- `9255e020b61dd73ee90a51dd96029c89415f8fe1` — integridad de asignaciones de roles.
- `c7bb3e0451cb6690fa58a1de760536b56cc6c202` — límites de administración RBAC.
