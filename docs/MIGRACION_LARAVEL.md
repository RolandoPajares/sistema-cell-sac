# Mapa de migración CELL SAC

## Alcance y fuentes

- Fuente de referencia: `Sistema_Web_CELL_SAC` (aplicación PHP propia).
- Destino ejecutable: `sistema-cell-sac` (Laravel 13, PHP 8.3+).
- La fuente no se modifica ni es necesaria para ejecutar el destino.
- La base de datos de referencia contiene 20 tablas de negocio y datos de catálogo; el destino conserva sus nombres en español para facilitar la transición.

## Equivalencias arquitectónicas

| Aplicación anterior | Destino Laravel | Decisión |
| --- | --- | --- |
| `app/Controladores` | `app/Http/Controllers` | Controladores HTTP delgados y orientados a casos de uso. |
| `app/Servicios` | `app/Services` | Reglas transaccionales de pedidos, inventario, compras, cotizaciones y recomendaciones. |
| `app/DAO` + PDO | Eloquent y Query Builder | No se copia el acceso PDO propio; se mantienen consultas explícitas solo cuando aportan claridad. |
| `app/Modelos` | `app/Models` | Modelos Eloquent con relaciones y casts. |
| `app/Validacion` | `app/Http/Requests` | Validación de entrada y autorización contextual. |
| `app/Middleware` | middleware web de Laravel + `EnsureRole` | CSRF, sesión, autenticación y throttling quedan en Laravel. |
| `app/Nucleo/Enrutamiento` | `routes/web.php` | Rutas con middleware, nombres y model binding. |
| vistas PHP y plantillas | `resources/views` Blade | Layouts compartidos, componentes parciales y escaping automático. |
| `public/assets/css` | `public/assets/cell-legacy/css` | Se conserva el diseño original; se cargan hojas por contexto. |
| `public/assets/js` | `resources/js/legacy` | Se conservan scripts funcionales y se cargan mediante Vite. |
| SQL de estructura | `database/migrations` | Esquema adaptado sin migraciones destructivas. |

## Auditoría funcional

El origen contiene catálogo, autenticación, cuenta, carrito, checkout, pedidos, panel administrativo, usuarios, categorías, clientes, proveedores, productos, inventario, campañas, reportes, auditoría, comercio inteligente, portal mayorista y contacto.

El destino ya tenía implementados catálogo, autenticación, carrito, checkout, pedidos, inventario, productos, proveedores, compras, cotizaciones, campañas, cupones, segmentos, reportes, auditoría y recomendaciones. Esta integración completa las páginas informativas/contacto, optimizador, publicidad inteligente, portal mayorista, categorías, clientes, navegación por rol y administración de pedidos/cuenta.

## Componentes que no se trasladan

- El framework propio (`app/Nucleo`, `public/index.php` anterior y `Enrutador`) porque Laravel ya resuelve el ciclo HTTP.
- Los DAO, gestores de sesión/CSRF y helpers de URLs porque duplicarían Eloquent, middleware y helpers Blade.
- SQL de consultas incrustado en vistas y cálculos de presentación complejos; se convierten a modelos, servicios y controladores.
- Credenciales, `.env` y configuraciones del proyecto anterior.

## Criterios de seguridad

- Las mutaciones web usan CSRF de Laravel y métodos HTTP semánticos.
- Las entradas se validan con Form Requests.
- Las vistas usan escaping Blade; las rutas internas se generan con `route()`.
- Los roles se centralizan en `User::ROLES`, `RolePermissions` y middleware/policies.
- Los cambios de inventario, checkout, compras y cancelación de pedidos permanecen dentro de transacciones con bloqueo pesimista.

