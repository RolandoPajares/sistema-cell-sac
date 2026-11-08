# Registro de cambios de refactorización y correcciones

Fecha de registro: 2026-11-08

Este documento registra únicamente los cambios comprobados durante las intervenciones realizadas sobre el repositorio actual. El worktree contiene otros cambios previos de usuario y de otros agentes que no se atribuyen a este registro.

## 1. Navegación dinámica del dashboard

### Problema

`resources/views/paneles/dashboard.blade.php` contenía una lista fija de accesos rápidos con rutas administrativas como productos, inventario, compras y reportes.

### Corrección

- `app/Http/Controllers/Paneles/Administrador/DashboardController.php`
  - Se inyectó `ServicioMenuDinamico`.
  - Se agregó la preparación de `quickLinks` a partir de los grupos de menú autorizados.
  - Solo se exponen elementos habilitados, con ruta válida y filtrados por el usuario actual.
- `resources/views/paneles/dashboard.blade.php`
  - Se eliminó el arreglo fijo de rutas.
  - La vista consume `$quickLinks` preparado por el controlador.
  - Se eliminó un bloque antiguo protegido por `@if(false)` que nunca se ejecutaba.

### Resultado

Los accesos rápidos administrativos siguen el menú dinámico existente y no agregan una segunda fuente de navegación.

## 2. Preparación del detalle público de producto

### Problema

`resources/views/publico/catalogo/detalle.blade.php` calculaba dentro de Blade la categoría, imágenes públicas, precios, descuento, tipo visual y datos de variantes.

### Corrección

- `app/Http/Controllers/Publico/Catalogo/CatalogoController.php`
  - Se trasladó la preparación de esos datos al método `show`.
  - Se conservaron los métodos existentes de disponibilidad e imágenes del modelo/servicio.
- `resources/views/publico/catalogo/detalle.blade.php`
  - Se eliminó el bloque de preparación `@php`.
  - La vista conserva el renderizado, formularios, atributos `data-*` y componentes visuales existentes.

### Resultado

La vista no ejecuta cálculos comerciales ni llamadas estáticas al modelo para preparar la pantalla.

## 3. Preparación del perfil de cuenta

### Problema

`resources/views/cuenta/perfil/mostrar.blade.php` resolvía autorización, rutas de cuenta, preferencias, datos del cliente, URL de fotografía, porcentaje de completitud e iniciales.

### Corrección

- `app/Http/Controllers/Cuenta/CuentaController.php`
  - Se trasladó la preparación de los datos derivados al método `profile`.
  - Se mantuvo la carga de la relación `customer`.
  - Se mantuvo la selección de rutas para usuarios administradores y no administradores.
- `resources/views/cuenta/perfil/mostrar.blade.php`
  - Se eliminaron los bloques `@php` de preparación.
  - Se conservaron formularios, rutas, estructura visual y comportamiento existente.

## 4. Confirmaciones y diálogos compartidos

### Problema

Algunas vistas financieras y comerciales utilizaban `onclick` u `onsubmit` inline para confirmaciones y para abrir detalles de recepción.

### Corrección

- `resources/js/modulos/gestion/dialogos-panel.js`
  - Se agregó soporte para `[data-purchase-reception-open]`.
  - La apertura de los detalles de recepción se realiza mediante delegación JavaScript.
- `resources/views/gestion/comercial/compras/listar.blade.php`
  - Se reemplazó el `onclick` inline por `data-purchase-reception-open`.
  - Las confirmaciones de anulación y recepción utilizan `data-confirm`.
- `resources/views/gestion/finanzas/reembolsos/detalle.blade.php`
  - Las confirmaciones de aprobar y anular utilizan `data-confirm`.
- `resources/views/gestion/finanzas/pagos/detalle.blade.php`
  - La confirmación de efectivo contado y anulación utiliza `data-confirm`.
- `resources/views/gestion/finanzas/aperturas-cierres/detalle.blade.php`
  - La confirmación del arqueo y cierre utiliza `data-confirm`.

### Resultado

Estas vistas reutilizan `resources/js/componentes/comunes/confirmacion.js` y `resources/js/modulos/gestion/dialogos-panel.js`, sin crear un segundo sistema de confirmaciones.

## 5. Correcciones de autorización y RBAC registradas previamente

Estas correcciones ya existían en el historial de trabajo realizado antes de este registro:

- `app/Servicios/ServicioAutorizacion.php`
  - Se corrigió la resolución de sede activa para evitar la recursión entre `activeSiteId()` y `allowsGlobal()`.
- `app/Http/Controllers/Gestion/Seguridad/RolesPermisosController.php`
  - Se mantuvo reservado únicamente el rol de código `administrador`.
  - Se protegió la asignación de permisos `gestion.*` a roles de alcance propio.
- `tests/Feature/RbacDinamicoTest.php`
  - Se ajustó la preparación de datos RBAC para probar roles y permisos dinámicos.
- `tests/Feature/SeguridadDelegacionRecorridoTest.php`
  - Se verificó el recorrido de creación de rol, asignación, acceso y revocación.

No se modificaron migraciones, seeders, tablas empresariales, datos reales, puertos, configuración de XAMPP ni credenciales.

## 6. Verificaciones ejecutadas

### Pruebas PHP

- Suite focalizada dashboard, catálogo y RBAC: 32 pruebas aprobadas y 236 aserciones.
- Suite ampliada ejecutada anteriormente: 52 pruebas aprobadas y 2,767 aserciones.
- Pint ejecutado sobre los archivos PHP modificados: aprobado.

### Vite

- `npm run build`: aprobado.
- Vite transformó 56 módulos.
- Permanecen advertencias conocidas de tres recursos `assets/cell-legacy`, que se resuelven durante runtime.

### Git

- No se ejecutaron `git reset`, `git clean`, commits, push ni merge.
- El worktree contiene numerosos cambios previos ajenos a esta intervención.
- `git diff --check` no mostró errores propios en los archivos corregidos.

## 7. Limitaciones

- No se realizó validación visual con navegador porque no había una herramienta de navegador disponible.
- Persisten algunos `onchange="this.form.submit()"` en formularios de paginación. Se conservaron porque representan comportamiento simple de formularios y una extracción masiva podría alterar la navegación.
- La carpeta `docs/arquitectura/resources/` estaba vacía en el estado actual del worktree. No se reconstruyeron documentos anteriores con información especulativa.
- No se ejecutaron operaciones comerciales, pagos, compras, cambios de stock ni modificaciones contra la base empresarial real.

## 8. Estado final

La navegación administrativa corregida utiliza el RBAC dinámico existente. Las responsabilidades principales retiradas de las vistas fueron trasladadas a controladores y componentes JavaScript existentes. La validación automatizada y la compilación fueron satisfactorias, pero la validación visual y la recuperación de la documentación canónica eliminada permanecen pendientes.
