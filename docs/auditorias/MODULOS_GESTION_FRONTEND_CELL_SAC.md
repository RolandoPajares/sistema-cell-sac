# Auditoría de módulos de Gestión frontend — CELL SAC

Fecha: 2026-10-27

## Alcance

Se completó una ampliación controlada para el panel administrativo. Los módulos nuevos siguen siendo frontend, excepto Devoluciones y garantías, que cuenta con backend administrativo y una migración propia; Soporte y reclamos ahora tiene una ruta de presentación administrativa para poder visualizar su interfaz. No se modificaron XAMPP, `.env`, configuración de base de datos ni las tablas existentes.

Los módulos nuevos no se anuncian como operativos: sus vistas muestran estados vacíos profesionales y mensajes de integración pendiente. El formulario de Soporte y reclamos sigue siendo visual y no simula guardados, actualizaciones o eliminaciones.

## Árbol anterior identificado

Ya existían y se conservaron estas áreas de Gestión:

- `auditoria`
- `campanias`
- `categorias`
- `clientes`
- `compras`
- `cotizaciones`
- `cupones`
- `inventario`
- `productos`
- `proveedores`
- `reportes`
- `segmentos`
- `usuarios`
- `ventas`

Pedidos continúa resolviéndose mediante la ruta administrativa existente y sus vistas de comercio/reportes actuales; no se creó una segunda carpeta para duplicarlo.

También se verificó que Publicidad ya está cubierta por la vista/controlador de publicidad inteligente, por lo que no se duplicó ese módulo.

## Árbol final agregado

```text
resources/
├── css/
│   └── gestion/
│       └── modulos-pendientes.css
├── js/
│   └── modulos/
│       └── gestion/
│           └── modulos-pendientes.js
└── views/
    └── gestion/
        ├── componentes/
        │   ├── modulo-pendiente.blade.php
        │   ├── modulo-pendiente-crud-formulario.blade.php
        │   ├── modulo-pendiente-eliminar.blade.php
        │   ├── modulo-pendiente-formulario.blade.php
        │   └── modulo-pendiente-ver.blade.php
        ├── analitica-clientes/
        │   └── listar.blade.php
        ├── configuracion-comercial/
        │   └── listar.blade.php
        ├── configuracion-empresa/
        │   └── listar.blade.php
        ├── configuracion-general/
        │   └── listar.blade.php
        ├── configuracion-seguridad/
        │   └── listar.blade.php
        ├── devoluciones-garantias/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── envios-entregas/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── facturacion/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── integraciones/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── metricas-marketing/
        │   └── listar.blade.php
        ├── notificaciones/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── pagos/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── reembolsos/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        ├── reportes-financieros/
        │   └── listar.blade.php
        ├── respaldos-mantenimiento/
        │   └── listar.blade.php
        ├── roles-permisos/
        │   ├── crear.blade.php
        │   ├── editar.blade.php
        │   ├── eliminar.blade.php
        │   ├── formulario.blade.php
        │   ├── listar.blade.php
        │   └── ver.blade.php
        └── soporte-reclamos/
            ├── crear.blade.php
            ├── editar.blade.php
            ├── eliminar.blade.php
            ├── formulario.blade.php
            ├── listar.blade.php
            └── ver.blade.php
```

## Separación de responsabilidades

Cada módulo nuevo mantiene su plantilla `listar.blade.php` como HTML/Blade mínimo: layout, sección de contenido e inclusión del componente. La configuración PHP de cada pantalla se encuentra separada en `datos.php` dentro de su propia carpeta.

Los recursos de presentación e interacción permanecen fuera de las vistas:

- PHP: archivos `datos.php` por módulo para títulos, indicadores, gráficos, columnas y campos.
- Blade/HTML: vistas del listado y fragmentos de modales sin estilos ni scripts incrustados.
- CSS: `resources/css/gestion/modulos-pendientes.css`.
- JavaScript: `resources/js/modulos/gestion/modulos-pendientes.js`.

## Archivos existentes modificados

Solo se modificaron las entradas administrativas autorizadas:

- `resources/css/paneles/administrador/index.css`
  - Importa `resources/css/gestion/modulos-pendientes.css`.
- `resources/js/modulos/paneles/administrador/index.js`
  - Importa `resources/js/modulos/gestion/modulos-pendientes.js`.

No se modificaron `panel.css`, `panel.js`, Vite, cargadores compartidos, layouts, menús de otros roles ni archivos backend.

## Módulos nuevos y responsables propuestos

Todos los módulos nuevos quedan preparados para revisión del Integrante 1 / líder, con coordinación backend pendiente:

| Módulo | Interfaz preparada | Propietario propuesto | Ruta |
| --- | --- | --- | --- |
| Envíos y entregas | Lista + CRUD visual | Integrante 1 / coordinación operaciones | Pendiente |
| Devoluciones y garantías | Lista + CRUD administrativo | Integrante 1 / coordinación operaciones | Implementado |
| Soporte y reclamos | Tablero CRM + CRUD visual | Integrante 1 / coordinación CRM | Presentación implementada |
| Pagos | Lista + CRUD visual | Integrante 1 / coordinación finanzas | Pendiente |
| Facturación | Lista + CRUD visual | Integrante 1 / coordinación finanzas | Pendiente |
| Reembolsos | Lista + CRUD visual | Integrante 1 / coordinación finanzas | Pendiente |
| Reportes financieros | Panel de consulta | Integrante 1 / coordinación finanzas | Pendiente |
| Analítica de clientes | Panel de consulta | Integrante 1 / coordinación CRM | Pendiente |
| Métricas de marketing | Panel de consulta | Integrante 1 / coordinación marketing | Pendiente |
| Roles y permisos | Lista + CRUD visual no operativo | Integrante 1 | Pendiente; no altera RBAC |
| Notificaciones | Lista + CRUD visual | Integrante 1 | Pendiente |
| Integraciones | Lista + CRUD visual | Integrante 1 | Pendiente |
| Respaldos y mantenimiento | Panel de consulta | Integrante 1 | Pendiente |
| Datos de empresa | Panel de consulta | Integrante 1 | Pendiente |
| Configuración comercial | Panel de consulta | Integrante 1 | Pendiente |
| Configuración de seguridad | Panel de consulta | Integrante 1 | Pendiente; no altera seguridad |
| Configuración general | Panel de consulta | Integrante 1 | Pendiente |

## Módulos existentes conservados

Se conservaron sin recrear ni modificar sus implementaciones:

- Productos
- Categorías
- Inventario
- Pedidos
- Ventas
- Compras
- Cotizaciones
- Clientes
- Proveedores
- Campañas
- Publicidad inteligente
- Cupones
- Segmentos
- Usuarios
- Auditoría
- Reportes comerciales

## CRUD visual preparado

Los siguientes módulos tienen fragmentos separados para crear, editar, ver, eliminar y compartir formulario:

- Envíos y entregas
- Devoluciones y garantías
- Soporte y reclamos
- Pagos
- Facturación
- Reembolsos
- Roles y permisos
- Notificaciones
- Integraciones

Los formularios se abren dentro de elementos `dialog` centrados sobre el listado, con scroll interno, cierre por botón X, cierre por Cancelar y confirmación visual para eliminación. No tienen `action`, no usan rutas ficticias y no modifican registros.

## Paneles de consulta

Los módulos de análisis, mantenimiento y configuración tienen solamente el panel de consulta apropiado:

- Reportes financieros
- Analítica de clientes
- Métricas de marketing
- Respaldos y mantenimiento
- Datos de empresa
- Configuración comercial
- Configuración de seguridad
- Configuración general

No se inventaron acciones de eliminación para reportes, auditoría o configuración.

## Diseño estadístico agregado

Todos los módulos nuevos ahora incluyen una composición visual consistente con Clientes, Proveedores y Campañas:

- Cuatro indicadores estadísticos por módulo, con iconos, color semántico y texto de integración pendiente.
- Un gráfico de tendencia por periodo con ejes, cuadrícula, leyenda temporal y estado vacío.
- Un gráfico de distribución tipo dona con leyenda específica del dominio.
- Buscador, filtro de estado, tabla adaptable, paginación visual y panel de estado de integración.
- Acciones de creación, consulta, edición y eliminación en modales para módulos CRUD.
- Acción de exportación visual para paneles de consulta, sin descargar datos ficticios.

Las gráficas se presentan como componentes preparados, pero no muestran valores simulados. Cuando se conecte cada módulo, sus indicadores, series y leyendas deberán recibir datos reales desde el controlador autorizado.

## Diseño especializado de Envíos y entregas

El módulo `envios-entregas` ya no utiliza solamente el tablero genérico. Cuenta con una interfaz logística específica inspirada en la referencia administrativa:

- Ruta visual de Administración / Operaciones comerciales / Envíos y entregas.
- Acciones de Exportar, Generar guías y Nuevo envío.
- Indicadores de despachos pendientes, tránsito, entregas e incidencias.
- Filtros de pedido, fecha, transportista y estado en una sola fila.
- Tabla Control de envíos con columnas preparadas para guía, pedido, destinatario, transportista y fechas.
- Paneles inferiores de Alertas de entrega, Próximos despachos y Rendimiento logístico.
- Diseño responsive y CSS acotado a `.envios-entregas-page`.

La vista conserva el estado vacío porque no existe todavía una ruta o endpoint autorizado para traer envíos reales.

## Diseño especializado de Devoluciones y garantías

El módulo `devoluciones-garantias` utiliza el tablero operativo reutilizable y ahora presenta una composición alineada con la referencia administrativa:

- Ruta visual de Administración / Operaciones comerciales / Devoluciones y garantías.
- Acciones de Exportar, Ver políticas y Nueva solicitud.
- Indicadores de solicitudes del mes, evaluación, casos resueltos y casos fuera de plazo.
- Filtros de fecha, tipo y estado dentro de una sola barra adaptable.
- Tabla Solicitudes de devolución y garantía con datos reales, filtros GET, paginación de ocho registros y estado vacío cuando no hay resultados.
- Paneles inferiores de Casos prioritarios, Próximos vencimientos y Resolución de casos.
- Formularios emergentes reutilizados para crear, editar, ver y eliminar, con CSRF y endpoints administrativos reales.
- Diseño responsive acotado a `.gestion-operacion-page` y cargado dentro del área de contenido.

El módulo ya cuenta con backend administrativo exclusivo:

- `DevolucionGarantiaController` lista, filtra, pagina ocho registros, exporta CSV, crea, actualiza y elimina solicitudes.
- `GuardarDevolucionGarantiaRequest` y `ActualizarDevolucionGarantiaRequest` validan los campos y requieren el rol `administrador`.
- `DevolucionGarantia` relaciona pedido, cliente, producto y usuario creador.
- La migración `2026_10_28_203424_create_devoluciones_garantias_table.php` creó la tabla `devoluciones_garantias` sin alterar tablas existentes.
- La ruta funcional es `management.returns` (`/gestion/devoluciones-garantias`) y está protegida por `auth` y `role:administrador`.
- El estado vacío solo se muestra cuando la consulta real no devuelve registros.

## Diseño especializado de Soporte y reclamos

El módulo `soporte-reclamos` cuenta con un tablero CRM propio inspirado en la referencia administrativa:

- Ruta visual de Administración / Relaciones comerciales / Soporte y reclamos.
- Acciones de Exportar, Ver reportes y Nuevo ticket.
- Cuatro indicadores para tickets registrados, abiertos, en atención y resueltos/prioridad alta.
- Filtros de búsqueda, tipo, prioridad, estado y fecha en una sola barra adaptable.
- Tabla Centro de soporte y reclamos con estado vacío profesional y paginación visual.
- Paneles inferiores de Alertas de atención, Actividad reciente y Calidad de atención.
- Modales de crear, editar, ver y eliminar reutilizados desde los componentes administrativos existentes.
- CSS aislado en `resources/css/gestion/soporte-reclamos/soporte-reclamos.css`.

La ruta `management.support` solo presenta la interfaz; no crea tablas ni registra tickets hasta una futura integración backend.

## Integración CSS y JavaScript

- El CSS nuevo está en `resources/css/gestion/modulos-pendientes.css`.
- Todos sus selectores están acotados a `.gestion-pendiente-page`.
- El JavaScript nuevo está en `resources/js/modulos/gestion/modulos-pendientes.js`.
- Solo inicializa elementos que contienen `data-pending-module`.
- Marca cada raíz con `data-pending-module-ready` para evitar eventos duplicados.
- Usa un único listener por módulo, soporta `dialog`, teclado nativo de cierre y no toca el menú lateral compartido.
- La carga se hace únicamente desde `resources/js/modulos/paneles/administrador/index.js` y el CSS desde `resources/css/paneles/administrador/index.css`.

La repetición visual se centralizó porque todos los módulos nuevos comparten el mismo patrón de listado vacío, integración pendiente y modal. Esto evita CSS/JS duplicado sin introducir selectores globales.

## Rutas y datos

Las rutas existentes se conservaron. Las rutas funcionales actuales incluyen:

- `management.dashboard`
- `management.products`
- `management.categories`
- `management.inventory`
- `management.orders`
- `management.sales`
- `management.customers`
- `management.suppliers`
- `management.campaigns`
- `management.users`
- `management.reports`
- `management.audit`
- `management.purchases`
- `management.coupons`
- `management.segments`
- `smart.ads`

Además, Devoluciones y garantías dispone de estas rutas nuevas, todas bajo `auth` y `role:administrador`:

- `management.returns` — listado, filtros y paginación.
- `management.returns.store` — creación.
- `management.returns.update` — edición.
- `management.returns.destroy` — eliminación.
- `management.returns.export` — exportación CSV.

Soporte y reclamos dispone de `management.support`, bajo `auth` y `role:administrador`, únicamente para mostrar su interfaz administrativa.

Los demás módulos nuevos continúan siendo interfaces frontend pendientes de endpoint. Sus valores de ejemplo usan “—” y “Sin datos integrados”; no representan cifras de MySQL.

## Comprobaciones ejecutadas

- `php -l`: correcto en modelo, requests, controlador y migración de Devoluciones y garantías.
- `php -l`: correcto en `SoporteReclamoController`.
- `php artisan route:list --name=management.returns`: correcto; se registraron cinco rutas administrativas.
- `php artisan route:list --name=management.support`: correcto; se registró la ruta de presentación del módulo.
- `php artisan migrate:status`: correcto; la migración de Devoluciones y garantías quedó aplicada en el lote 4.
- `php artisan view:cache`: correcto; Blade compiló las vistas.
- `git diff --check`: mantiene dos avisos preexistentes fuera de este cambio: CRLF en Compras y una línea vacía final en el layout compartido.
- `php artisan view:cache`: correcto; Blade compiló las vistas.
- `npm run build`: correcto con Vite 8.3.3; la primera ejecución aislada falló por `EPERM` del entorno y la segunda ejecución autorizada completó correctamente.
- Vite mostró únicamente dos avisos legacy de imágenes que permanecen para resolverse en runtime:
  - `/assets/cell-legacy/img/marca/logo.jpeg`
  - `/assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png`
- Se inspeccionó `git status --short`; se conservaron los cambios locales preexistentes y no se eliminaron archivos ajenos.
- No se realizó comprobación visual en navegador autenticado; la ruta ya está registrada y requiere iniciar sesión como Administrador.

## Protección del trabajo colaborativo

- No se tocaron los seis roles restantes.
- No se tocaron sus dashboards, menús, CSS ni JavaScript.
- No se tocaron los layouts compartidos, cargadores, Vite ni la matriz RBAC.
- No se eliminaron ni movieron vistas existentes.
- No se ejecutaron operaciones destructivas de Git.
- No se realizó commit, push, merge ni rebase.

## Protección de XAMPP y alcance de base de datos

No se modificaron XAMPP, sus servicios, el puerto 15327, `.env` ni `config/database.php`. Para habilitar el backend solicitado se agregó y ejecutó únicamente la migración de la nueva tabla `devoluciones_garantias`; no se modificaron tablas existentes, no se insertaron registros, no se ejecutaron seeders y no se cambiaron modelos existentes.

## Revisión del líder antes del commit

El líder debe revisar:

1. Los nombres de los módulos nuevos y su ownership.
2. La estrategia de una hoja de estilos y un comportamiento JS compartidos pero acotados.
3. El momento de crear endpoints reales para Soporte y reclamos.
4. La matriz de permisos antes de publicar Roles y permisos.
5. Las vistas nuevas desde las rutas que se creen en la fase backend.
6. Los avisos de imágenes legacy de Vite si se decide corregirlos en una tarea separada.
7. La migración y el flujo CRUD de Devoluciones y garantías antes del commit.

