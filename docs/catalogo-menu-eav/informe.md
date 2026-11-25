# Menú lateral dinámico multinivel mediante EAV

## Resumen

Se integró un panel lateral público que consulta opciones reales del catálogo mediante `fetch()`, conserva una pila de niveles, permite regresar sin recargar la página y enlaza los productos compatibles con la búsqueda existente.

## Diagnóstico y alcance

El proyecto ya tenía EAV mediante `atributos`, `atributo_valores`, `categoria_atributo` y `producto_atributo_valor`, además de `ServicioCatalogo` y filtros facetados. Se reutilizaron esas estructuras. No se crearon tablas EAV duplicadas.

## Inventario de cambios

Nuevos archivos:

- `database/migrations/2026_11_25_000001_add_hierarchy_to_categorias.php`
- `app/Http/Requests/Publico/Catalogo/MenuCatalogoRequest.php`
- `app/Servicios/Compartidos/Catalogo/ServicioNavegacionCatalogo.php`
- `resources/views/layouts/tienda/menu-catalogo.blade.php`
- `resources/js/publico/catalogo/menu.js`
- `resources/css/publico/catalogo/menu.css`

Archivos modificados:

- `app/Http/Controllers/Publico/Catalogo/CatalogoController.php`
- `app/Models/Catalogo/Categoria.php`
- `app/Models/Catalogo/Producto.php`
- `routes/web.php`
- `resources/views/layouts/tienda/encabezado.blade.php`
- `resources/views/layouts/tienda/principal.blade.php`
- `resources/js/layouts/tienda/principal.js`
- `resources/css/layouts/tienda/principal.css`
- `tests/Feature/Publico/CatalogoFacetasTest.php`
- `tests/RefreshSpanishTestDatabase.php`

El cambio local previo de `resources/js/publico/catalogo/index.js` fue preservado.

## Base de datos

Tablas involucradas: **5**: `categorias`, `productos`, `atributos`, `atributo_valores` y `producto_atributo_valor`. `categoria_atributo` ya existe y permanece disponible para configuración futura por categoría.

La migración agrega `categoria_padre_id` y `orden` a `categorias`, con clave foránea autorreferencial e índice. Es incremental y reversible. No se ejecutó sobre la base principal.

## Contrato y viaje de datos

```text
Botón Explorar
  -> menu.js
  -> GET /catalogo/menu
  -> MenuCatalogoRequest
  -> CatalogoController::menu
  -> ServicioNavegacionCatalogo
  -> ServicioCatalogo + EAV/Eloquent
  -> JSON con opciones, conteos y parámetros
  -> render seguro con textContent
```

Cada opción incluye `id`, `tipo`, `nombre`, `tiene_hijos`, `cantidad_productos` y, cuando corresponde, `parametros` o `url`. Los valores se validan en el servidor; el navegador no es una fuente de confianza.

## Seguridad y compatibilidad

- Consultas parametrizadas mediante Eloquent y Query Builder.
- Validación de categorías activas y valores EAV pertenecientes al atributo enviado.
- Renderizado de etiquetas con `textContent`, sin HTML recibido del cliente.
- La búsqueda asíncrona existente del catálogo no fue reemplazada.
- No se agregaron dependencias JavaScript.
- Se respeta `prefers-reduced-motion`.

## Verificación

- `php artisan test --compact tests/Feature/Publico/CatalogoFacetasTest.php tests/Feature/ApiCatalogoReadOnlyTest.php`: **4 passed, 20 assertions**.
- `php artisan test --compact tests/Feature/Gestion/ViewsTest.php tests/Feature/Publico tests/Feature/CellSacCommerceTest.php`: **30 passed, 194 assertions**.
- `node --check resources/js/publico/catalogo/menu.js`: correcto.
- `vendor/bin/pint --dirty --format agent`: ejecutado.
- `npm run build`: correcto; Vite reporta únicamente advertencias preexistentes de rutas de imágenes no resolubles en build.

## Limitaciones y siguientes pasos

La migración debe ejecutarse en un entorno autorizado antes de usar jerarquías padre-hijo en producción. La integración administrativa para editar la jerarquía y asignar atributos por categoría todavía debe planificarse como una fase separada para no alterar módulos ajenos.
