# CELL SAC — Estructura final del frontend por rol

Fecha: 2026-10-24  
Rama: `Develop`  
Alcance: organización del frontend y mecanismo de carga futura por rol.

## 1. Resultado

La estructura de vistas por rol queda consolidada sin mover nuevamente los dashboards, menús ni perfiles existentes.

Cada rol dispone de:

```text
resources/views/paneles/{rol}/
├── dashboard.blade.php
├── menu.blade.php
└── perfil/
    ├── editar.blade.php
    └── mostrar.blade.php
```

No se crearon carpetas vacías de `secciones`, `componentes`, `modulos`, CSS o JavaScript. Esas carpetas se crearán solamente cuando exista una funcionalidad real.

## 2. Árbol final de vistas por rol

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

Las vistas de Gestión y Público permanecen en sus ubicaciones originales. No se duplicaron dentro de los paneles.

## 3. Archivos modificados en esta integración

```text
resources/views/layouts/administracion.blade.php
resources/js/panel.js
resources/js/nucleo/cargador-recursos-panel.js
```

### `layouts/administracion.blade.php`

Se agregó el atributo técnico:

```blade
data-panel-role="{{ auth()->user()?->rol }}"
```

No modifica la apariencia ni los contratos HTML existentes. Permite que el cargador conozca el rol activo sin duplicar lógica de autorización en JavaScript.

### `panel.js`

Se agregó una sola importación del cargador compartido. No se duplicaron los listeners existentes de menú, encabezado, gestión o asistente.

### `cargador-recursos-panel.js`

Usa glob dinámico de Vite para buscar únicamente:

```text
resources/css/paneles/{rol}/index.css
resources/js/modulos/paneles/{rol}/index.js
```

El cargador transforma los roles con guion bajo a la carpeta con guion medio y carga solamente el rol autenticado.

Ejemplos:

```text
compras_logistica → compras-logistica
ventas_mayoristas → ventas-mayoristas
cliente_minorista → cliente-minorista
```

## 4. CSS actual y CSS futuro

CSS específicos existentes y conservados:

```text
resources/css/paneles/administrador/dashboard.css
resources/css/paneles/compras-logistica/dashboard.css
resources/css/paneles/cliente-mayorista/dashboard.css
resources/css/paneles/cliente-minorista/dashboard.css
```

Estos cuatro archivos continúan cargándose desde `resources/css/panel.css`. No se importan nuevamente mediante el cargador dinámico, por lo que no se duplican ni cambia su orden actual de cascada.

Los roles de Marketing, Ventas Mayoristas y Ventas Minoristas no tienen CSS exclusivo adicional comprobado actualmente. No se crearon archivos vacíos.

### Cómo agregar CSS exclusivo

Cuando un integrante necesite CSS exclusivo, debe crear dentro de su carpeta:

```text
resources/css/paneles/marketing/index.css
```

Ejemplo:

```css
@import './secciones/analisis-campanias.css';

body.rol-marketing .marketing-card {
    /* estilos limitados al rol */
}
```

Para los cuatro roles que ya poseen `dashboard.css`, no se debe volver a importarlo en `index.css`, porque ya lo carga `panel.css`. El `index.css` debe utilizarse para nuevos `perfil.css`, `menu.css`, `secciones` o `modulos`.

Los selectores deben estar acotados al rol o a un módulo específico. No se deben agregar selectores globales como `.card`, `.button`, `.table`, `.btn-sm` o `.btn-outline` sin contexto.

## 5. JavaScript actual y JavaScript futuro

Actualmente no existen scripts exclusivos por rol. El bundle global administrativo conserva:

```text
resources/js/panel.js
```

Sus módulos compartidos siguen siendo:

```text
resources/js/layouts/panel/menu-lateral.js
resources/js/layouts/panel/encabezado.js
resources/js/componentes/comunes/confirmacion.js
resources/js/componentes/comunes/visibilidad-contrasenas.js
resources/js/componentes/inteligencia/asistente/asistente-panel.js
resources/js/modulos/gestion/index.js
```

### Cómo agregar JavaScript exclusivo

Un integrante puede crear:

```text
resources/js/modulos/paneles/marketing/index.js
resources/js/modulos/paneles/marketing/dashboard.js
resources/js/modulos/paneles/marketing/secciones/analisis-campanias.js
```

El `index.js` del rol será el importador:

```js
import './dashboard.js';
import './secciones/analisis-campanias.js';
```

El cargador compartido detectará automáticamente `index.js` y lo ejecutará solamente cuando el usuario tenga el rol correspondiente.

Cada módulo debe comprobar que su pantalla existe antes de registrar eventos. Por ejemplo:

```js
const panel = document.querySelector('[data-marketing-dashboard]');

if (panel) {
    // Inicialización exclusiva de Marketing.
}
```

No se deben duplicar los listeners de `panel.js`, `menu-lateral.js`, `encabezado.js` ni los módulos de Gestión.

## 6. Entradas Vite actuales

Las entradas existentes se conservaron:

```text
resources/css/tienda.css
resources/css/panel.css
resources/js/panel.js
resources/js/tienda.js
```

No fue necesario modificar `vite.config.js`, `panel.css` ni agregar siete entradas independientes. El cargador se incluye dentro de `panel.js` y Vite detectará los futuros `index.css` e `index.js` mediante `import.meta.glob` en la siguiente compilación.

## 7. Dependencias de componentes compartidos

| Componente | Utilizado por | Responsable |
|---|---|---|
| `components/comunes/dashboard-cliente.blade.php` | Cliente Mayorista y Cliente Minorista | Líder |
| `components/comunes/dashboard-rol-interno.blade.php` | Compras y Logística, Marketing, Ventas Mayoristas y Ventas Minoristas | Líder |
| `components/perfil/pagina.blade.php` | Los 14 wrappers de perfil | Líder |
| `components/panel/menu-enlaces.blade.php` | Los siete menús por rol | Líder |
| `components/inteligencia/asistente/*` | Asistente público y paneles que lo incluyen | Líder |
| `components/inteligencia/comprador/*` | Comparador y recomendaciones públicas | Líder / equipo asignado según módulo |
| `components/inteligencia/cotizador/*` | Crear y listar cotizaciones | Integrante 3, con coordinación del líder |
| `components/publico/catalogo/*` | Catálogo público | Integrante 4, con coordinación del líder |
| `components/publico/publicidad.blade.php` | Layout público | Líder |

No se copiaron estos componentes dentro de los roles.

## 8. Responsabilidades del equipo

| Integrante | Carpetas principales |
|---|---|
| Integrante 1 | `views/paneles/administrador/`, `css/paneles/administrador/`, `js/modulos/paneles/administrador/`, Gestión de auditoría, categorías, clientes, productos y usuarios |
| Integrante 2 | Paneles de Compras y Marketing, sus CSS/JS, Gestión de compras, inventario, proveedores, campañas, cupones, segmentos y reportes |
| Integrante 3 | Paneles de Ventas Mayoristas y Minoristas, sus CSS/JS, cotizaciones y comercio público |
| Integrante 4 | Paneles de Cliente Mayorista y Minorista, sus CSS/JS, catálogo, inicio, inteligencia, asistente y páginas públicas |
| Líder | Layouts, componentes transversales, Vite, rutas, backend, permisos, `panel.css`, `panel.js`, `tienda.css` y `tienda.js` |

## 9. Menús y RBAC

Cada integrante puede modificar únicamente su propio:

```text
resources/views/paneles/{rol}/menu.blade.php
```

La autorización continúa centralizada en `App\Soporte\PermisosRoles` y en el composer de `AppServiceProvider`. Los menús no crean permisos nuevos ni sustituyen Middleware o Policies.

Los enlaces nuevos deben apuntar a rutas existentes y autorizadas. Si requieren backend, ruta, Policy o permiso, deben solicitar integración al líder.

## 10. Validaciones realizadas

| Verificación | Resultado |
|---|---|
| `php artisan view:cache` | Correcto |
| `php artisan route:list --except-vendor` | 129 rutas |
| `php artisan test --compact` | 56 pruebas, 321 aserciones, todas exitosas |
| `npm run build` | Correcto con Vite 8.3.3 y 26 módulos transformados |
| Manifest de producción | Entradas globales `panel.js`, `panel.css`, `tienda.js` y `tienda.css` presentes |
| CSS/JS por rol actuales | No existen `index.css` ni `index.js` exclusivos todavía |
| Duplicación de los cuatro CSS actuales | No se agregó una segunda carga |
| XAMPP/MySQL | No modificados |
| Comparación visual escritorio/móvil | Pendiente |

Las pruebas utilizan `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` en `phpunit.xml`, junto con `LazilyRefreshDatabase`. No se utilizó la base MySQL principal.

Vite conserva las advertencias existentes sobre imágenes legacy que se resuelven en runtime. No son errores introducidos por esta integración.

## 11. Estado de comprobación de recursos exclusivos

La infraestructura del cargador fue compilada correctamente. El manifest actual no contiene entradas de rol porque no existen todavía archivos reales `index.css` o `index.js` que incluir.

Por esa razón, la carga de un recurso exclusivo futuro queda comprobada estáticamente y mediante compilación, pero todavía no existe una pantalla exclusiva nueva que permita una prueba visual o de interacción de ese mecanismo.

## 12. Riesgos pendientes

### Alta

- Realizar una prueba de navegador cuando se agregue el primer `index.css` e `index.js` real de un rol.
- Comparar los siete dashboards en escritorio y móvil.

### Media

- Revisar posteriormente los selectores no acotados de `resources/css/paneles/compras-logistica/dashboard.css`, especialmente `.btn-sm`, `.btn-outline`, `.modulo-listado-real` y `.module-workspace.single`. No se modificaron para evitar regresiones visuales.

### Baja

- Crear módulos específicos solo cuando exista una funcionalidad real.

## 13. Protección del sistema

No se modificaron:

```text
.env
config/database.php
phpunit.xml
routes/
app/Models/
app/Servicios/
app/Http/Requests/
app/Http/Middleware/
app/Policies/
```

No se ejecutaron migraciones, seeders, SQL ni operaciones sobre XAMPP o MySQL.

## Conclusión

CELL SAC conserva sus cuatro entradas globales y ahora dispone de un punto de integración preparado para recursos exclusivos por rol. Los futuros integrantes solo necesitarán crear el `index.css` y/o `index.js` dentro de su propia carpeta de rol; no tendrán que editar repetidamente Vite, los layouts, `panel.css` ni `panel.js`.

La infraestructura está preparada y compilada, pero la comprobación visual de un asset exclusivo real queda pendiente hasta que exista el primer módulo frontend específico de un rol.
