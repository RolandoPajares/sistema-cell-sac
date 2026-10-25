# CELL SAC — Independencia de frontend por rol

Fecha de auditoría: 2026-10-24  
Rama revisada: `Develop`  
Objetivo: separar menús y perfiles por rol, y documentar una estrategia segura para CSS y JavaScript por rol.

## 1. Resultado ejecutivo

La separación de los menús laterales y perfiles quedó implementada para los siete roles. Cada rol dispone ahora de:

```text
resources/views/paneles/{rol}/dashboard.blade.php
resources/views/paneles/{rol}/menu.blade.php
resources/views/paneles/{rol}/perfil/mostrar.blade.php
resources/views/paneles/{rol}/perfil/editar.blade.php
```

La estructura común del sidebar permanece en `resources/views/layouts/panel/menu-lateral.blade.php`. El HTML repetitivo de los enlaces se conserva una sola vez en `resources/views/components/panel/menu-enlaces.blade.php`.

La autorización no fue duplicada ni trasladada a las vistas. `App\Soporte\PermisosRoles`, el composer de `AppServiceProvider`, el middleware, las Policies y los controladores permanecen como responsables del acceso. Los archivos `menu.blade.php` únicamente seleccionan las rutas ya recibidas y autorizadas para su rol.

No se modificaron XAMPP, MySQL, `.env`, `config/database.php`, migraciones, modelos, servicios, rutas, CSS ni JavaScript. Solo se actualizó la referencia de vista del administrador en `CuentaController` y su aserción de prueba para apuntar a la nueva ubicación.

## 2. Árbol real final del área de paneles

```text
resources/views/paneles/
├── administrador/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   └── perfil/
│       ├── editar.blade.php
│       └── mostrar.blade.php
├── compras-logistica/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   └── perfil/
│       ├── editar.blade.php
│       └── mostrar.blade.php
├── marketing/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   └── perfil/
│       ├── editar.blade.php
│       └── mostrar.blade.php
├── ventas-mayoristas/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   └── perfil/
│       ├── editar.blade.php
│       └── mostrar.blade.php
├── ventas-minoristas/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   └── perfil/
│       ├── editar.blade.php
│       └── mostrar.blade.php
├── cliente-mayorista/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   └── perfil/
│       ├── editar.blade.php
│       └── mostrar.blade.php
└── cliente-minorista/
    ├── dashboard.blade.php
    ├── menu.blade.php
    └── perfil/
        ├── editar.blade.php
        └── mostrar.blade.php
```

Las 14 vistas que estaban en `resources/views/cuenta/perfiles/{rol}/` fueron trasladadas íntegramente a `resources/views/paneles/{rol}/perfil/`. Se conservó el componente compartido `components/perfil/pagina.blade.php`, por lo que no se duplicó el HTML de formularios ni la lógica de presentación.

## 3. Archivos creados y modificados

### Creados

```text
resources/views/paneles/administrador/menu.blade.php
resources/views/paneles/compras-logistica/menu.blade.php
resources/views/paneles/marketing/menu.blade.php
resources/views/paneles/ventas-mayoristas/menu.blade.php
resources/views/paneles/ventas-minoristas/menu.blade.php
resources/views/paneles/cliente-mayorista/menu.blade.php
resources/views/paneles/cliente-minorista/menu.blade.php
resources/views/paneles/administrador/perfil/editar.blade.php
resources/views/paneles/administrador/perfil/mostrar.blade.php
resources/views/paneles/cliente-mayorista/perfil/editar.blade.php
resources/views/paneles/cliente-mayorista/perfil/mostrar.blade.php
resources/views/paneles/cliente-minorista/perfil/editar.blade.php
resources/views/paneles/cliente-minorista/perfil/mostrar.blade.php
resources/views/paneles/compras-logistica/perfil/editar.blade.php
resources/views/paneles/compras-logistica/perfil/mostrar.blade.php
resources/views/paneles/marketing/perfil/editar.blade.php
resources/views/paneles/marketing/perfil/mostrar.blade.php
resources/views/paneles/ventas-mayoristas/perfil/editar.blade.php
resources/views/paneles/ventas-mayoristas/perfil/mostrar.blade.php
resources/views/paneles/ventas-minoristas/perfil/editar.blade.php
resources/views/paneles/ventas-minoristas/perfil/mostrar.blade.php
```

### Modificados

```text
resources/views/layouts/panel/menu-lateral.blade.php
app/Http/Controllers/Cuenta/CuentaController.php
tests/Feature/Paneles/DashboardControllerTest.php
```

El cambio del layout se limita al mapa de rol a vista. El controlador solo cambia el nombre de la vista de edición de perfil del administrador. Se conservaron el encabezado de marca, el `<aside>`, el `<nav>`, las clases, los IDs, los atributos `data-*`, los formularios, el enlace de cuenta y el formulario de cierre de sesión.

### Conservado como componente compartido

```text
resources/views/components/panel/menu-enlaces.blade.php
```

Este componente contiene el único renderer común de enlaces. Mantiene `Route::has`, `request()->routeIs`, `route()`, las clases de estado activo y el escape Blade de las etiquetas.

### Eliminados por duplicidad

Se retiraron únicamente los siete archivos de menú que estaban en `resources/views/components/panel/menus/` después de sustituirlos por sus equivalentes en `resources/views/paneles/{rol}/menu.blade.php`. No se eliminó ningún dashboard, módulo, ruta o funcionalidad.

También se eliminaron las 14 copias antiguas de `resources/views/cuenta/perfiles/{rol}/` después de comprobar que no quedaban referencias a `cuenta.perfiles.*`. El contenido de las 14 vistas nuevas coincide con el respaldo previo.

## 4. Propietarios de archivos

| Propietario | Archivos o área permitida |
|---|---|
| Líder | `resources/views/layouts/`, `resources/views/components/`, `vite.config.js`, integración Vite, rutas y backend |
| Integrante 1 | `resources/views/paneles/administrador/`, CSS y JS específicos de Administrador cuando existan |
| Integrante 2 | `resources/views/paneles/compras-logistica/`, `resources/views/paneles/marketing/` y sus recursos propios |
| Integrante 3 | `resources/views/paneles/ventas-mayoristas/`, `resources/views/paneles/ventas-minoristas/` y sus recursos propios |
| Integrante 4 | `resources/views/paneles/cliente-mayorista/`, `resources/views/paneles/cliente-minorista/` y sus recursos propios |

Un integrante no debe modificar `menu-lateral.blade.php`, los componentes comunes, `panel.css`, `panel.js` ni recursos de otro integrante.

## 5. Integración real del sidebar

El flujo actual es:

```text
layouts/administracion.blade.php
    └── layouts/panel/menu-lateral.blade.php
            ├── paneles.administrador.menu
            ├── paneles.compras-logistica.menu
            ├── paneles.marketing.menu
            ├── paneles.ventas-mayoristas.menu
            ├── paneles.ventas-minoristas.menu
            ├── paneles.cliente-mayorista.menu
            └── paneles.cliente-minorista.menu
                    └── components.panel.menu-enlaces
```

Los enlaces recibidos por el layout son construidos por el composer de `layouts.administracion` en `App\Providers\AppServiceProvider.php`. Para Administrador se utiliza `PermisosRoles::administratorNavigation`; para los demás roles se utiliza `PermisosRoles::navigation`.

La vista específica filtra solamente por nombres de ruta previamente autorizados. No puede conceder permisos nuevos. El renderer común vuelve a comprobar que la ruta exista antes de generar el enlace.

## 6. Correspondencia de menús

| Rol | Vista de menú | Rutas mostradas actualmente |
|---|---|---|
| Administrador | `paneles/administrador/menu.blade.php` | `management.dashboard`, productos, categorías, inventario, pedidos, clientes, proveedores, campañas, usuarios y reportes |
| Compras y Logística | `paneles/compras-logistica/menu.blade.php` | inicio, dashboard, recomendaciones, comparador, asistente, optimizador, inventario, proveedores y compras |
| Marketing | `paneles/marketing/menu.blade.php` | inicio, dashboard, recomendaciones, comparador, asistente, optimizador, campañas, publicidad, cupones, segmentos, reportes y auditoría |
| Ventas Mayoristas | `paneles/ventas-mayoristas/menu.blade.php` | inicio, dashboard, recomendaciones, comparador, asistente, optimizador y cotizaciones |
| Ventas Minoristas | `paneles/ventas-minoristas/menu.blade.php` | inicio, dashboard, pedidos, recomendaciones, comparador, asistente, optimizador e inventario |
| Cliente Mayorista | `paneles/cliente-mayorista/menu.blade.php` | inicio, dashboard, recomendaciones, comparador, asistente, optimizador, portal mayorista y cotizaciones |
| Cliente Minorista | `paneles/cliente-minorista/menu.blade.php` | inicio, catálogo, dashboard, pedidos, recomendaciones, comparador, asistente y optimizador |

La lista conserva el orden y las opciones que ya entregaba la navegación central. No se agregaron rutas ficticias.

## 7. Vistas y componentes compartidos

Las vistas de dashboard siguen usando los componentes existentes:

```text
resources/views/components/comunes/dashboard-cliente.blade.php
resources/views/components/comunes/dashboard-rol-interno.blade.php
resources/views/components/perfil/pagina.blade.php
resources/views/components/panel/menu-enlaces.blade.php
```

Esto evita siete copias del HTML de tarjetas, formularios, tablas, sesiones o enlaces. Las nuevas secciones exclusivas de un rol deben crearse dentro de su propia carpeta `paneles/{rol}/secciones/` cuando realmente sean necesarias.

## 8. Estado real de CSS

El árbol actual contiene estos archivos específicos de paneles:

```text
resources/css/paneles/administrador/dashboard.css
resources/css/paneles/compras-logistica/dashboard.css
resources/css/paneles/cliente-mayorista/dashboard.css
resources/css/paneles/cliente-minorista/dashboard.css
```

`resources/css/panel.css` importa actualmente esos cuatro archivos junto con los estilos compartidos. Marketing y los dos roles de ventas no tienen reglas CSS exclusivas comprobadas; utilizan estilos comunes, por lo que no se crearon archivos vacíos.

Los selectores propios existentes utilizan principalmente prefijos como `admin-`, `rol-cliente-mayorista` y `rol-cliente-minorista`. Se detectaron algunos selectores no completamente acotados en `compras-logistica/dashboard.css`, por ejemplo `.btn-sm`, `.btn-outline`, `.modulo-listado-real` y `.module-workspace.single`. No se modificaron porque podrían cambiar pantallas existentes.

### Convención futura

Los estilos exclusivos deben ubicarse en:

```text
resources/css/paneles/{rol}/dashboard.css
resources/css/paneles/{rol}/perfil.css
resources/css/paneles/{rol}/menu.css
```

Los selectores deben iniciar con un alcance del rol o de la pantalla, por ejemplo `body.rol-marketing .marketing-card`. No deben utilizar nombres genéricos como `.card`, `.button` o `.table` sin un alcance existente.

## 9. Estado real de JavaScript

No existen actualmente scripts exclusivos por rol. El bundle administrativo compartido es:

```text
resources/js/panel.js
```

Este importa:

```text
resources/js/layouts/panel/menu-lateral.js
resources/js/layouts/panel/encabezado.js
resources/js/componentes/comunes/confirmacion.js
resources/js/componentes/comunes/visibilidad-contrasenas.js
resources/js/componentes/inteligencia/asistente/asistente-panel.js
resources/js/modulos/gestion/index.js
```

No se crearon `dashboard.js`, `perfil.js` ni `menu.js` por rol porque no se encontró una interacción exclusiva que justificara esos archivos. Crear scripts vacíos aumentaría la complejidad sin aislar funcionalidad real.

Cuando exista una interacción exclusiva, el script debe comprobar primero su contexto. La estrategia recomendada es usar un selector o atributo de contenedor ya acordado por el líder y salir inmediatamente si no existe. No debe registrar listeners globales que puedan ejecutarse en otros dashboards.

## 10. Estrategia Vite y limitación actual

La configuración real de `vite.config.js` tiene cuatro entradas:

```text
resources/css/tienda.css
resources/css/panel.css
resources/js/panel.js
resources/js/tienda.js
```

El layout administrativo carga `panel.css` y `panel.js` mediante:

```blade
@vite(['resources/css/panel.css', 'resources/js/panel.js'])
```

Por tanto:

1. Los archivos CSS ya importados por `panel.css` se compilan.
2. Un nuevo CSS o JS por rol no queda disponible automáticamente solo por existir en una carpeta.
3. Añadir nuevas entradas requiere una modificación controlada de `vite.config.js` y una integración de carga en el layout o en la vista del rol.
4. Esa modificación pertenece al líder porque afecta el manifest, la carga y potencialmente todos los paneles.

No se modificó `vite.config.js`, `panel.css`, `panel.js` ni el layout para evitar una carga duplicada o un cambio de orden. La independencia completa de nuevos assets por rol requiere una tarea posterior del líder con una prueba de producción del manifest.

## 11. Reglas de colaboración con GitHub

Antes de trabajar:

```text
git pull
git checkout -b feature/panel-{rol}-{descripcion}
```

Cada integrante debe limitar sus cambios a su carpeta de rol. No debe editar archivos compartidos para resolver un problema local. Si necesita una ruta, permiso, campo de base de datos, entrada Vite o componente común, debe abrir una solicitud al líder.

El líder revisará los Pull Requests y comprobará:

- rutas existentes y autorizadas;
- no duplicación de RBAC;
- selectores CSS acotados;
- listeners JavaScript limitados al rol;
- no modificación de otros roles;
- compilación Blade y Vite;
- pruebas de las pantallas afectadas.

No se realizaron commits, merges ni pushes automáticamente.

## 12. Validación ejecutada

| Verificación | Resultado |
|---|---|
| `php artisan view:cache` | Correcto: plantillas Blade compiladas, incluidos los 14 perfiles nuevos |
| `php artisan test --compact` | Correcto: 56 pruebas, 321 aserciones |
| `php artisan route:list --except-vendor --json` | 129 rutas |
| `git diff --check -- resources/views` | Sin errores de espacios o parches |
| `npm run build` | Correcto con Vite 8.3.3 y 25 módulos transformados |
| Referencias antiguas `components.panel.menus.*` | No quedan referencias |
| Perfil administrador por la ruta `dashboard` | Correcto: prueba Feature actualizada |
| Render HTTP individual de los otros 13 wrappers de perfil | Pendiente: no tienen rutas directas actualmente |
| Comparación visual en navegador | Pendiente |

Vite conserva dos advertencias ya existentes sobre imágenes en `/assets/cell-legacy/img/...`; se dejan sin cambios porque se resuelven en runtime y no son consecuencia de esta separación.

Las pruebas automatizadas comprueban la carga de dashboards y la suite general, pero no sustituyen una comparación visual de los siete menús en escritorio y móvil.

## 13. Protección y recuperación

Antes del cambio se guardó una copia recuperable de `menu-lateral.blade.php` en:

```text
C:\Users\HP\AppData\Local\Temp\cell-sac-frontend-role-backup-20261024\menu-lateral.blade.php
```

Las 14 vistas originales de perfil se respaldaron en:

```text
C:\Users\HP\AppData\Local\Temp\cell-sac-profiles-backup-20261024\perfiles\
```

No se ejecutaron comandos SQL, migraciones, seeders ni operaciones sobre XAMPP o MySQL.

## 14. Pendientes del líder

### Alta

- Diseñar y probar la carga de entradas CSS/JS por rol en Vite sin duplicar `panel.css` ni `panel.js`.
- Ejecutar comparación visual de los siete dashboards en escritorio y móvil.

### Media

- Acotar los selectores genéricos existentes en `resources/css/paneles/compras-logistica/dashboard.css`, únicamente después de comparar el DOM y las capturas.
- Definir un atributo o contenedor estable para que futuros scripts por rol comprueben su contexto.

### Baja

- Crear archivos CSS o JS adicionales solo cuando exista una funcionalidad exclusiva real que los necesite.

## Conclusión

La independencia de vistas, menús y perfiles por rol está implementada sin duplicar el render común ni alterar permisos, rutas o diseño. La independencia futura de CSS y JavaScript está preparada como convención de carpetas, pero la carga de assets nuevos sigue dependiendo de la integración Vite del líder. Esa limitación se mantiene explícita para evitar declarar como funcional un recurso que no esté incluido en el manifest.
