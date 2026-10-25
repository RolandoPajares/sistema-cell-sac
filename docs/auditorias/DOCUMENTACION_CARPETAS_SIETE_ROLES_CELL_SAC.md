# Documentación de carpetas de los siete roles — CELL SAC

**Fecha:** 2026-10-24  
**Rama:** `Develop`  
**Commit base:** `44c8a22 Refactor frontend de los 7 roles`

## 1. Objetivo

Se documentaron en español los archivos reservados para futuras ampliaciones de los siete roles del frontend.

No se agregaron funcionalidades ficticias, estilos visuales, eventos JavaScript, listeners, rutas ni pantallas nuevas.

## 2. Responsables

| Rol | Integrante responsable |
|---|---|
| Administrador | Integrante 1 |
| Compras y Logística | Integrante 2 |
| Marketing | Integrante 2 |
| Ventas Mayoristas | Integrante 3 |
| Ventas Minoristas | Integrante 3 |
| Cliente Mayorista | Integrante 4 |
| Cliente Minorista | Integrante 4 |

## 3. Archivos documentados

Cada rol tiene los siguientes archivos reservados:

```text
1  resources/css/paneles/{rol}/index.css
1  resources/js/modulos/paneles/{rol}/index.js
3  resources/views/paneles/{rol}/{secciones,componentes,modulos}/.gitkeep
3  resources/css/paneles/{rol}/{secciones,componentes,modulos}/.gitkeep
3  resources/js/modulos/paneles/{rol}/{secciones,componentes,modulos}/.gitkeep
```

Total verificado: **77 archivos documentados**.

- 7 `index.css`.
- 7 `index.js`.
- 21 `.gitkeep` de vistas.
- 21 `.gitkeep` de CSS.
- 21 `.gitkeep` de JavaScript.

Cada archivo contiene al menos dos líneas no vacías de documentación.

## 4. Comentarios de los siete `index.css`

Los `index.css` no contienen reglas visuales. Funcionan como puntos de entrada para futuros estilos exclusivos.

### Administrador

Archivo: `resources/css/paneles/administrador/index.css`

```css
/* CELL SAC | ADMINISTRADOR | RESPONSABLE: INTEGRANTE 1 */
/* Importar aquí CSS de nuevas secciones y módulos del panel; no duplicar dashboard.css ni modificar estilos compartidos. */
```

### Compras y Logística

Archivo: `resources/css/paneles/compras-logistica/index.css`

```css
/* CELL SAC | COMPRAS Y LOGÍSTICA | RESPONSABLE: INTEGRANTE 2 */
/* Importar aquí estilos exclusivos de compras, inventario, proveedores, logística y perfil; no modificar CSS global. */
```

### Marketing

Archivo: `resources/css/paneles/marketing/index.css`

```css
/* CELL SAC | MARKETING | RESPONSABLE: INTEGRANTE 2 */
/* Importar aquí estilos exclusivos de campañas, publicidad, indicadores, perfil y módulos de Marketing. */
```

### Ventas Mayoristas

Archivo: `resources/css/paneles/ventas-mayoristas/index.css`

```css
/* CELL SAC | VENTAS MAYORISTAS | RESPONSABLE: INTEGRANTE 3 */
/* Importar aquí CSS de cotizaciones, pedidos, reportes visuales, perfil y módulos exclusivos de Ventas Mayoristas. */
```

### Ventas Minoristas

Archivo: `resources/css/paneles/ventas-minoristas/index.css`

```css
/* CELL SAC | VENTAS MINORISTAS | RESPONSABLE: INTEGRANTE 3 */
/* Importar aquí CSS de ventas, pedidos, indicadores, perfil y módulos exclusivos de Ventas Minoristas. */
```

### Cliente Mayorista

Archivo: `resources/css/paneles/cliente-mayorista/index.css`

```css
/* CELL SAC | CLIENTE MAYORISTA | RESPONSABLE: INTEGRANTE 4 */
/* Importar aquí estilos de compras, cotizaciones, recomendaciones, perfil y módulos exclusivos del cliente mayorista. */
```

### Cliente Minorista

Archivo: `resources/css/paneles/cliente-minorista/index.css`

```css
/* CELL SAC | CLIENTE MINORISTA | RESPONSABLE: INTEGRANTE 4 */
/* Importar aquí estilos del catálogo, carrito, pedidos, perfil y módulos exclusivos del cliente minorista. */
```

Los cuatro `dashboard.css` existentes no fueron duplicados ni importados nuevamente.

## 5. Comentarios de los siete `index.js`

Los `index.js` contienen únicamente comentarios. No registran eventos ni ejecutan funciones.

| Archivo | Documentación incorporada |
|---|---|
| `resources/js/modulos/paneles/administrador/index.js` | Identifica al Administrador, al Integrante 1 y reserva scripts de secciones y módulos sin duplicar `panel.js`. |
| `resources/js/modulos/paneles/compras-logistica/index.js` | Identifica Compras y Logística, al Integrante 2 y reserva scripts de compras, inventario, proveedores, logística y perfil. |
| `resources/js/modulos/paneles/marketing/index.js` | Identifica Marketing, al Integrante 2 y reserva scripts de campañas, publicidad, indicadores y perfil. |
| `resources/js/modulos/paneles/ventas-mayoristas/index.js` | Identifica Ventas Mayoristas, al Integrante 3 y reserva scripts de cotizaciones, pedidos, reportes y perfil. |
| `resources/js/modulos/paneles/ventas-minoristas/index.js` | Identifica Ventas Minoristas, al Integrante 3 y reserva scripts de ventas, pedidos, indicadores y perfil. |
| `resources/js/modulos/paneles/cliente-mayorista/index.js` | Identifica Cliente Mayorista, al Integrante 4 y reserva scripts de compras, cotizaciones, recomendaciones y perfil. |
| `resources/js/modulos/paneles/cliente-minorista/index.js` | Identifica Cliente Minorista, al Integrante 4 y reserva scripts de catálogo, carrito, pedidos y perfil. |

Todos indican que las importaciones futuras deben limitarse al rol y evitar listeners globales duplicados.

## 6. Documentación de las carpetas Blade

Cada rol contiene:

```text
resources/views/paneles/{rol}/secciones/.gitkeep
resources/views/paneles/{rol}/componentes/.gitkeep
resources/views/paneles/{rol}/modulos/.gitkeep
```

Los comentarios quedaron personalizados así:

| Rol | `secciones/` | `componentes/` | `modulos/` |
|---|---|---|---|
| Administrador | Vistas Blade de nuevas secciones del dashboard. | Componentes reutilizables solo del panel. | Subcarpetas de módulos administrativos exclusivos. |
| Compras y Logística | Indicadores, inventario y abastecimiento. | Componentes propios de compras y logística. | Módulos exclusivos de compras o logística. |
| Marketing | Campañas, publicidad, métricas y resúmenes. | Componentes propios de campañas y publicidad. | Funcionalidades exclusivas de campañas o publicidad. |
| Ventas Mayoristas | Indicadores, cotizaciones y resúmenes comerciales. | Componentes propios del rol, sin duplicar el cotizador compartido. | Módulos de ventas mayoristas. |
| Ventas Minoristas | Ventas, pedidos, indicadores y actividad reciente. | Componentes propios del panel. | Funcionalidades exclusivas de ventas minoristas. |
| Cliente Mayorista | Compras, cotizaciones y recomendaciones. | Componentes propios del cliente mayorista. | Funcionalidades exclusivas del cliente. |
| Cliente Minorista | Catálogo, carrito, pedidos y resumen del cliente. | Componentes propios del cliente minorista. | Funcionalidades exclusivas del rol. |

Cada comentario también indica que las rutas, autorizaciones, consultas y contratos de backend deben coordinarse con el líder.

## 7. Documentación de las carpetas CSS futuras

Para cada rol se crearon y documentaron:

```text
resources/css/paneles/{rol}/secciones/.gitkeep
resources/css/paneles/{rol}/componentes/.gitkeep
resources/css/paneles/{rol}/modulos/.gitkeep
```

Los comentarios de cada archivo indican:

- El integrante responsable.
- El tipo de estilos que debe guardarse allí.
- Que los archivos deben importarse desde `../index.css`.
- Que los selectores deben limitarse al rol.
- Que no deben duplicarse estilos globales ni modificar la cascada compartida.

La especialización de los comentarios corresponde a cada rol:

- Administrador: secciones, componentes y módulos de administración.
- Compras y Logística: inventario, abastecimiento, proveedores y compras.
- Marketing: campañas, publicidad, métricas y módulos de Marketing.
- Ventas Mayoristas: cotizaciones, indicadores, reportes y ventas mayoristas.
- Ventas Minoristas: ventas, pedidos e indicadores minoristas.
- Cliente Mayorista: compras, cotizaciones y recomendaciones.
- Cliente Minorista: catálogo, carrito, pedidos y tienda del cliente.

## 8. Documentación de las carpetas JavaScript futuras

Para cada rol se crearon y documentaron:

```text
resources/js/modulos/paneles/{rol}/secciones/.gitkeep
resources/js/modulos/paneles/{rol}/componentes/.gitkeep
resources/js/modulos/paneles/{rol}/modulos/.gitkeep
```

Los comentarios de cada archivo indican:

- El integrante responsable.
- El tipo de interacciones exclusivas del rol.
- Que los archivos deben importarse desde `../index.js`.
- Que debe comprobarse el contexto antes de registrar eventos.
- Que no deben duplicarse funciones de `panel.js`, encabezado, menú ni componentes compartidos.

## 9. Archivos funcionales conservados

No se modificaron en esta tarea:

- `dashboard.blade.php` de los siete roles.
- `menu.blade.php` de los siete roles.
- Las catorce vistas de perfil.
- Layouts Blade.
- Componentes compartidos en `resources/views/components/`.
- CSS global y CSS compartido.
- JavaScript global.
- `resources/js/nucleo/cargador-recursos-panel.js`.
- `resources/js/panel.js`.
- `resources/css/panel.css`.
- Rutas, controladores, modelos, servicios, middleware y permisos.

Los componentes compartidos siguen bajo responsabilidad del líder y no deben copiarse dentro de los roles.

## 10. Verificaciones realizadas

### Archivos y contenido

Resultado real:

```text
EXPECTED_DOCUMENTED_FILES=77
MISSING=0
FILES_WITH_LESS_THAN_TWO_LINES=0
```

### Vite

Comando:

```text
npm run build
```

Resultado: correcto.

- Vite `8.3.3`.
- 40 módulos transformados.
- Siete entradas CSS por rol detectadas.
- Siete entradas JavaScript por rol detectadas.
- Siete importaciones dinámicas en el manifest.

Los archivos CSS y JavaScript sin código funcional producen chunks mínimos. Esto es esperado y no altera el diseño.

Persisten dos advertencias legacy de imágenes no resueltas en tiempo de build:

- `/assets/cell-legacy/img/marca/logo.jpeg`.
- `/assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png`.

No fueron causadas por esta documentación.

### Blade

```text
php artisan view:cache
```

Resultado: correcto.

### Rutas

```text
php artisan route:list --except-vendor --json
```

Resultado: **129 rutas**.

### Pruebas

```text
php artisan test --compact
```

Resultado:

```text
56 tests passed
321 assertions
```

La configuración de pruebas mantiene SQLite en memoria. No se ejecutaron operaciones contra MySQL de XAMPP.

### Integridad del diff

```text
git diff --check
```

Resultado: correcto.

## 11. Estado de Git

No se realizó commit, push ni merge.

Los archivos nuevos de esta documentación deben incluirse en el siguiente commit junto con las entradas y carpetas correspondientes:

```text
resources/css/paneles/*/index.css
resources/css/paneles/*/{secciones,componentes,modulos}/.gitkeep
resources/js/modulos/paneles/*/index.js
resources/js/modulos/paneles/*/{secciones,componentes,modulos}/.gitkeep
resources/views/paneles/*/{secciones,componentes,modulos}/.gitkeep
docs/auditorias/DOCUMENTACION_CARPETAS_SIETE_ROLES_CELL_SAC.md
```

También existen informes y cambios previos no comprometidos. Deben revisarse antes de crear el commit.

## 12. Protección de XAMPP y MySQL

No se modificaron:

- XAMPP.
- MySQL.
- Puerto `15327`.
- `.env`.
- `config/database.php`.
- `phpunit.xml`.
- Migraciones, seeders, tablas o datos.

CELL SAC continúa utilizando MySQL de XAMPP como base principal. SQLite se utilizó exclusivamente para las pruebas automatizadas aisladas.
