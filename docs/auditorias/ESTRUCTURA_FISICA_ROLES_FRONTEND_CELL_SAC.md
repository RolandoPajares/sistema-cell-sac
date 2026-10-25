# Estructura física de frontend por roles — CELL SAC

**Fecha:** 2026-10-24  
**Rama:** `Develop`  
**Commit base:** `44c8a22 Refactor frontend de los 7 roles`

## 1. Alcance

Se completaron físicamente las carpetas de frontend de los siete roles sin mover ni recrear dashboards, menús, perfiles o componentes existentes.

Se agregaron únicamente:

- 7 entradas `index.css`.
- 7 entradas `index.js`.
- 21 archivos `.gitkeep` para conservar las carpetas Blade futuras.

Los archivos CSS y JavaScript nuevos contienen solamente comentarios de orientación. No agregan estilos, eventos, listeners, funciones ni comportamiento visual.

## 2. Árbol físico final de vistas por rol

```text
resources/views/paneles/
├── administrador/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   ├── perfil/
│   │   ├── editar.blade.php
│   │   └── mostrar.blade.php
│   ├── secciones/
│   │   └── .gitkeep
│   ├── componentes/
│   │   └── .gitkeep
│   └── modulos/
│       └── .gitkeep
├── compras-logistica/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   ├── perfil/
│   │   ├── editar.blade.php
│   │   └── mostrar.blade.php
│   ├── secciones/
│   │   └── .gitkeep
│   ├── componentes/
│   │   └── .gitkeep
│   └── modulos/
│       └── .gitkeep
├── marketing/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   ├── perfil/
│   │   ├── editar.blade.php
│   │   └── mostrar.blade.php
│   ├── secciones/
│   │   └── .gitkeep
│   ├── componentes/
│   │   └── .gitkeep
│   └── modulos/
│       └── .gitkeep
├── ventas-mayoristas/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   ├── perfil/
│   │   ├── editar.blade.php
│   │   └── mostrar.blade.php
│   ├── secciones/
│   │   └── .gitkeep
│   ├── componentes/
│   │   └── .gitkeep
│   └── modulos/
│       └── .gitkeep
├── ventas-minoristas/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   ├── perfil/
│   │   ├── editar.blade.php
│   │   └── mostrar.blade.php
│   ├── secciones/
│   │   └── .gitkeep
│   ├── componentes/
│   │   └── .gitkeep
│   └── modulos/
│       └── .gitkeep
├── cliente-mayorista/
│   ├── dashboard.blade.php
│   ├── menu.blade.php
│   ├── perfil/
│   │   ├── editar.blade.php
│   │   └── mostrar.blade.php
│   ├── secciones/
│   │   └── .gitkeep
│   ├── componentes/
│   │   └── .gitkeep
│   └── modulos/
│       └── .gitkeep
└── cliente-minorista/
    ├── dashboard.blade.php
    ├── menu.blade.php
    ├── perfil/
    │   ├── editar.blade.php
    │   └── mostrar.blade.php
    ├── secciones/
    │   └── .gitkeep
    ├── componentes/
    │   └── .gitkeep
    └── modulos/
        └── .gitkeep
```

## 3. Árbol físico final de CSS por rol

```text
resources/css/paneles/
├── administrador/
│   ├── dashboard.css              # Existente, conservado
│   └── index.css                  # Nuevo punto de entrada
├── compras-logistica/
│   ├── dashboard.css              # Existente, conservado
│   └── index.css                  # Nuevo punto de entrada
├── marketing/
│   └── index.css                  # Nuevo punto de entrada
├── ventas-mayoristas/
│   └── index.css                  # Nuevo punto de entrada
├── ventas-minoristas/
│   └── index.css                  # Nuevo punto de entrada
├── cliente-mayorista/
│   ├── dashboard.css              # Existente, conservado
│   └── index.css                  # Nuevo punto de entrada
└── cliente-minorista/
    ├── dashboard.css              # Existente, conservado
    └── index.css                  # Nuevo punto de entrada
```

Los cuatro `dashboard.css` existentes continúan cargándose desde `resources/css/panel.css`. Los nuevos `index.css` no los importan nuevamente.

No se crearon `dashboard.css` vacíos para Marketing, Ventas Mayoristas ni Ventas Minoristas.

## 4. Árbol físico final de JavaScript por rol

```text
resources/js/modulos/paneles/
├── administrador/
│   └── index.js
├── compras-logistica/
│   └── index.js
├── marketing/
│   └── index.js
├── ventas-mayoristas/
│   └── index.js
├── ventas-minoristas/
│   └── index.js
├── cliente-mayorista/
│   └── index.js
└── cliente-minorista/
    └── index.js
```

Cada `index.js` es un punto de entrada sin efectos secundarios. Los futuros módulos exclusivos se importarán desde el `index.js` del rol correspondiente.

## 5. Integración real del cargador

Se conservó el cargador existente en:

```text
resources/js/nucleo/cargador-recursos-panel.js
```

No se creó un segundo cargador y no se modificaron `vite.config.js`, `resources/js/panel.js`, `resources/css/panel.css` ni los layouts durante esta tarea.

El cargador utiliza:

```js
import.meta.glob('../../css/paneles/*/index.css');
import.meta.glob('../modulos/paneles/*/index.js');
```

La conversión de roles comprobada es:

| Rol almacenado | Carpeta física |
|---|---|
| `administrador` | `administrador` |
| `compras_logistica` | `compras-logistica` |
| `marketing` | `marketing` |
| `ventas_mayoristas` | `ventas-mayoristas` |
| `ventas_minoristas` | `ventas-minoristas` |
| `cliente_mayorista` | `cliente-mayorista` |
| `cliente_minorista` | `cliente-minorista` |

## 6. Resultado de Vite y manifest

Comando ejecutado:

```text
npm run build
```

Resultado: **correcto**.

- Vite `8.3.3`.
- 40 módulos transformados.
- 7 entradas CSS de rol en `public/build/manifest.json`.
- 7 entradas JavaScript de rol en `public/build/manifest.json`.
- 7 importaciones dinámicas desde el entry `resources/js/panel.js`.

El manifest registró estas entradas JavaScript como dinámicas:

```text
resources/js/modulos/paneles/administrador/index.js
resources/js/modulos/paneles/compras-logistica/index.js
resources/js/modulos/paneles/marketing/index.js
resources/js/modulos/paneles/ventas-mayoristas/index.js
resources/js/modulos/paneles/ventas-minoristas/index.js
resources/js/modulos/paneles/cliente-mayorista/index.js
resources/js/modulos/paneles/cliente-minorista/index.js
```

Como los siete CSS solo contienen comentarios y no reglas, Vite los consolidó en un chunk CSS mínimo compartido de `0.00 kB`. Esto no significa que no se hayan detectado: las siete claves aparecen en el manifest y cada una apunta al recurso generado. Cuando un integrante agregue reglas o importe un CSS de módulo, Vite podrá generar contenido adicional para ese chunk.

Las dos advertencias del build corresponden a imágenes legacy no resueltas en tiempo de compilación y no fueron introducidas por esta tarea:

- `/assets/cell-legacy/img/marca/logo.jpeg`.
- `/assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png`.

## 7. Validación Laravel

### Blade

```text
php artisan view:cache
```

Resultado: correcto.

### Rutas

```text
php artisan route:list --except-vendor --json
```

Resultado: **129 rutas**, sin cambios de URL, nombre, método HTTP o middleware.

### Pruebas

```text
php artisan test --compact
```

Resultado real:

```text
56 tests passed
321 assertions
```

`phpunit.xml` mantiene `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:`. No se ejecutaron migraciones, seeders ni escrituras contra MySQL/XAMPP.

### Diff

```text
git diff --check
```

Resultado: correcto.

## 8. Archivos existentes conservados

Se conservaron sin cambios funcionales:

- Los siete `dashboard.blade.php`.
- Los siete `menu.blade.php`.
- Las catorce vistas de perfil.
- Los componentes compartidos.
- Los cuatro `dashboard.css` existentes.
- `resources/js/panel.js`.
- `resources/css/panel.css`.
- `resources/js/nucleo/cargador-recursos-panel.js`.
- Layouts, rutas, controladores, modelos, permisos y servicios.

## 9. Archivos creados

### Entradas CSS

```text
resources/css/paneles/administrador/index.css
resources/css/paneles/compras-logistica/index.css
resources/css/paneles/marketing/index.css
resources/css/paneles/ventas-mayoristas/index.css
resources/css/paneles/ventas-minoristas/index.css
resources/css/paneles/cliente-mayorista/index.css
resources/css/paneles/cliente-minorista/index.css
```

### Entradas JavaScript

```text
resources/js/modulos/paneles/administrador/index.js
resources/js/modulos/paneles/compras-logistica/index.js
resources/js/modulos/paneles/marketing/index.js
resources/js/modulos/paneles/ventas-mayoristas/index.js
resources/js/modulos/paneles/ventas-minoristas/index.js
resources/js/modulos/paneles/cliente-mayorista/index.js
resources/js/modulos/paneles/cliente-minorista/index.js
```

### Carpetas Blade versionadas

Para cada rol se crearon:

```text
secciones/.gitkeep
componentes/.gitkeep
modulos/.gitkeep
```

## 10. Distribución del equipo

| Integrante | Roles | Vistas | CSS | JavaScript |
|---|---|---|---|---|
| 1 | Administrador | `views/paneles/administrador` | `css/paneles/administrador` | `js/modulos/paneles/administrador` |
| 2 | Compras-Logística y Marketing | `views/paneles/compras-logistica`, `views/paneles/marketing` | Sus carpetas CSS | Sus carpetas JS |
| 3 | Ventas Mayoristas y Minoristas | `views/paneles/ventas-mayoristas`, `views/paneles/ventas-minoristas` | Sus carpetas CSS | Sus carpetas JS |
| 4 | Cliente Mayorista y Minorista | `views/paneles/cliente-mayorista`, `views/paneles/cliente-minorista` | Sus carpetas CSS | Sus carpetas JS |

Los módulos compartidos, layouts, rutas, backend, permisos y componentes transversales continúan bajo control del líder.

## 11. Estado de Git y siguiente commit

No se realizó commit, push ni merge.

Los nuevos archivos de esta tarea aparecen como no rastreados hasta que el líder los agregue al commit. También permanecen cambios no comprometidos de tareas anteriores, que deben revisarse antes de publicar:

```text
resources/css/paneles/*/index.css
resources/js/modulos/paneles/*/index.js
resources/views/paneles/*/{secciones,componentes,modulos}/.gitkeep
```

No se deben utilizar `git reset --hard`, `git clean`, force push ni comandos destructivos para preparar el commit.

## 12. Limitación visual

No se realizó una comparación visual de los siete dashboards en escritorio y móvil en esta ejecución. Los archivos nuevos no contienen reglas CSS ni código ejecutable, por lo que no introducen cambios visuales intencionales; aun así, la revisión visual manual en navegador queda pendiente como validación complementaria.

## 13. Protección de XAMPP y MySQL

No se modificaron:

- XAMPP.
- MySQL.
- El puerto `15327`.
- `.env`.
- `config/database.php`.
- `phpunit.xml`.
- Migraciones.
- Seeders.
- Tablas o registros.
- Credenciales o conexiones.

La aplicación continúa utilizando MySQL de XAMPP como base principal. SQLite en memoria se utilizó únicamente para las pruebas automatizadas aisladas.
