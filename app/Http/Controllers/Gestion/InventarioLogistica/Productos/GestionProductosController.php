<?php

namespace App\Http\Controllers\Gestion\InventarioLogistica\Productos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\ActualizarProductoRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\GuardarConfiguracionTiendaRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\GuardarImagenVarianteRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\GuardarPrecioMayoristaRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\GuardarProductoRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\GuardarVarianteProductoRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\ImportarPreciosMayoristasRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Productos\ImportarProductosRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use App\Models\Compras\Proveedor;
use App\Models\Organizacion\Empresa;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\Productos\ServicioConfiguracionTienda;
use App\Servicios\Gestion\InventarioLogistica\Productos\ServicioGestionProductos;
use App\Servicios\Gestion\InventarioLogistica\Productos\ServicioPreciosMayoristas;
use App\Servicios\Gestion\InventarioLogistica\Productos\ServicioVariantesProducto;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use App\Servicios\Gestion\InventarioLogistica\ServicioPresentacionInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

/**
 * Define la responsabilidad de la clase GestionProductosController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GestionProductosController extends Controller
{
    /**
     * Ejecuta la operación preciosCanales definida por la clase GestionProductosController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $servicioPrecios  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function preciosCanales(Request $request, ServicioInventario $inventario, ServicioAutorizacion $autorizacion, ServicioPreciosMayoristas $servicioPrecios): View
    {
        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
            'marca' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'canal' => ['nullable', 'in:minorista,mayorista'],
        ]);
        // Obtiene en $alcance la informacion de la solicitud o del usuario actual.
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.consultar');
        // Guarda en $consultaBase el resultado necesario para continuar esta operacion.
        $consultaBase = $inventario->productosParaConsulta($alcance, [], true, true);
        // Guarda en $consultaFiltrada el resultado necesario para continuar esta operacion.
        $consultaFiltrada = $inventario->productosParaConsulta($alcance, [
            'buscar' => $filtros['buscar'] ?? null,
            'categoria' => isset($filtros['categoria']) ? (int) $filtros['categoria'] : null,
            'estado' => $filtros['estado'] ?? null,
        ], true, true)->when($filtros['marca'] ?? null, static fn (Builder $consulta, string $marca): Builder => $consulta->where('productos.marca', $marca));
        // Guarda en $paginacion el resultado necesario para continuar esta operacion.
        $paginacion = (clone $consultaFiltrada)->with([
            'category:id,nombre',
            'variants:id,producto_id,codigo_sku',
        ])->orderBy('productos.nombre')->orderBy('productos.id')->paginate(20)->withQueryString();
        // Prepara en $empresa la consulta que obtiene o modifica los registros necesarios.
        $empresa = Empresa::query()->findOrFail($alcance['empresa_id']);
        // Guarda en $preciosMayoristasDisponibles el resultado necesario para continuar esta operacion.
        $preciosMayoristasDisponibles = Schema::hasTable('precios_mayoristas');
        // Guarda en $preciosMayoristas el resultado necesario para continuar esta operacion.
        $preciosMayoristas = $servicioPrecios->preciosVigentes(
            (int) $alcance['empresa_id'],
            $paginacion->getCollection()->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
        );
        // Guarda en $productos el resultado necesario para continuar esta operacion.
        $productos = $paginacion->getCollection()->map(static function (Producto $producto) use ($empresa, $preciosMayoristas, $preciosMayoristasDisponibles): array {
            // Guarda en $precioBase el resultado necesario para continuar esta operacion.
            $precioBase = (float) $producto->precio;
            // Guarda en $precioMinorista el resultado necesario para continuar esta operacion.
            $precioMinorista = (float) ($producto->precio_oferta ?? $producto->precio);
            // Guarda en $existenciasDisponibles el resultado necesario para continuar esta operacion.
            $existenciasDisponibles = max(0, (float) $producto->existencias_alcance - (float) $producto->existencias_reservadas);

            return [
                'id' => (int) $producto->getKey(),
                'nombre' => $producto->nombre,
                'sku' => $producto->variants->pluck('codigo_sku')->filter()->implode(', ') ?: 'Sin SKU registrado',
                'categoria' => $producto->category?->nombre ?? 'Sin categoría',
                'marca' => $producto->marca,
                'base' => $empresa->moneda_base.' '.number_format($precioBase, 2, '.', ','),
                'minorista' => $empresa->moneda_base.' '.number_format($precioMinorista, 2, '.', ','),
                'mayorista' => ! $preciosMayoristasDisponibles ? 'No definido' : (($precioMayorista = $preciosMayoristas->get($producto->getKey()))
                    ? $empresa->moneda_base.' '.number_format((float) $precioMayorista->precio, 2, '.', ',')
                    : 'Sin precio registrado'),
                'stock' => rtrim(rtrim(number_format($existenciasDisponibles, 3, '.', ''), '0'), '.'),
                'estado' => $producto->activo ? 'Activo' : 'Inactivo',
                'icono' => 'bi-box-seam',
                'activo' => (bool) $producto->activo,
                'estado_codigo' => $producto->activo ? 'activo' : 'inactivo',
                'categoria_id' => (int) $producto->categoria_id,
                'en_oferta' => $producto->precio_oferta !== null && (float) $producto->precio_oferta < $precioBase,
            ];
        })->all();
        // Guarda en $productosFiltrados el resultado necesario para continuar esta operacion.
        $productosFiltrados = (clone $consultaBase)->count();
        // Guarda en $promedioBase el resultado necesario para continuar esta operacion.
        $promedioBase = (float) (clone $consultaBase)->avg('productos.precio');
        // Prepara en $consultaPromedioVigente la consulta que obtiene o modifica los registros necesarios.
        $consultaPromedioVigente = DB::query()->fromSub(
            (clone $consultaBase)->select(['productos.precio', 'productos.precio_oferta']),
            'precios_filtrados',
        )->selectRaw('AVG(COALESCE(precios_filtrados.precio_oferta, precios_filtrados.precio)) AS promedio');
        // Guarda en $promedioVigente el resultado necesario para continuar esta operacion.
        $promedioVigente = (float) ($consultaPromedioVigente->first()?->promedio ?? 0);
        // Guarda en $ofertas el resultado necesario para continuar esta operacion.
        $ofertas = (clone $consultaBase)->whereNotNull('productos.precio_oferta')
            ->whereColumn('productos.precio_oferta', '<', 'productos.precio')->count();
        $resumenMayorista = ['cantidad' => 0, 'promedio' => 0.0];
        // Comprueba la condicion $preciosMayoristasDisponibles antes de ejecutar este bloque.
        if ($preciosMayoristasDisponibles) {
            // Prepara en $preciosVigentes la consulta que obtiene o modifica los registros necesarios.
            $preciosVigentes = DB::table('precios_mayoristas as precio')
                ->selectRaw('precio.producto_id, MAX(precio.id) AS id')
                ->where('precio.empresa_id', $alcance['empresa_id'])->whereNull('precio.variante_id')
                ->where('precio.vigente_desde', '<=', now())
                ->where(fn ($consulta) => $consulta->whereNull('precio.vigente_hasta')->orWhere('precio.vigente_hasta', '>=', now()))
                ->groupBy('precio.producto_id');
            // Guarda en $productosAlcance el resultado necesario para continuar esta operacion.
            $productosAlcance = (clone $consultaBase)->select('productos.id')->distinct();
            // Prepara en $resumenMayorista la consulta que obtiene o modifica los registros necesarios.
            $resumenMayorista = DB::query()->fromSub($productosAlcance, 'productos_alcance')
                ->joinSub($preciosVigentes, 'precios_vigentes', 'precios_vigentes.producto_id', '=', 'productos_alcance.id')
                ->join('precios_mayoristas as precio_actual', 'precio_actual.id', '=', 'precios_vigentes.id')
                ->selectRaw('COUNT(*) AS cantidad, COALESCE(AVG(precio_actual.precio), 0) AS promedio')->first();
        }

        // Devuelve la vista 'gestion.ecommerce-marketing.precios-venta.listar' con los datos preparados por este controlador.
        return view('gestion.ecommerce-marketing.precios-venta.listar', [
            'productos' => $productos,
            'paginacion' => $paginacion,
            'filtros' => $filtros,
            'categorias' => Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'marcas' => (clone $consultaBase)->whereNotNull('productos.marca')->where('productos.marca', '<>', '')
                ->distinct()->orderBy('productos.marca')->pluck('productos.marca'),
            'monedaBase' => $empresa->moneda_base,
            'productosConPrecio' => $productosFiltrados,
            'promedioBase' => $empresa->moneda_base.' '.number_format($promedioBase, 2, '.', ','),
            'promedioMinorista' => $empresa->moneda_base.' '.number_format($promedioVigente, 2, '.', ','),
            'productosEnOferta' => $ofertas,
            'preciosMayoristasDisponibles' => $preciosMayoristasDisponibles,
            'puedeActualizarPrecioMayorista' => $preciosMayoristasDisponibles && $autorizacion->allowsGlobal($request->user(), 'gestion.productos.actualizar'),
            'totalPreciosMayoristas' => (int) $resumenMayorista->cantidad,
            'promedioMayorista' => $preciosMayoristasDisponibles
                ? $empresa->moneda_base.' '.number_format((float) $resumenMayorista->promedio, 2, '.', ',')
                : 'No definido',
            'puedeExportar' => $autorizacion->allows($request->user(), 'gestion.productos.exportar'),
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $servicioPrecios  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function guardarPrecioMayorista(GuardarPrecioMayoristaRequest $request, ServicioPreciosMayoristas $servicioPrecios): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $servicioPrecios->registrar($request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.products.preview.prices')->with('status', 'El precio mayorista quedó registrado en MariaDB.');
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $servicioPrecios  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function importarPreciosMayoristas(ImportarPreciosMayoristasRequest $request, ServicioPreciosMayoristas $servicioPrecios): RedirectResponse
    {
        // Obtiene en $cantidad la informacion de la solicitud o del usuario actual.
        $cantidad = $servicioPrecios->importarCsv($request->file('archivo'), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.products.preview.prices')->with('status', $cantidad.' precios mayoristas quedaron registrados en MariaDB.');
    }

    /**
     * Ejecuta la operación configuracionTienda definida por la clase GestionProductosController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $servicioConfiguracion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function configuracionTienda(Request $request, ServicioInventario $inventario, ServicioAutorizacion $autorizacion, ServicioConfiguracionTienda $servicioConfiguracion): View
    {
        // Obtiene en $alcance la informacion de la solicitud o del usuario actual.
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.consultar');
        // Prepara en $empresa la consulta que obtiene o modifica los registros necesarios.
        $empresa = Empresa::query()->findOrFail($alcance['empresa_id']);
        // Prepara en $productosActivos la consulta que obtiene o modifica los registros necesarios.
        $productosActivos = Producto::query()->where('empresa_id', $empresa->id)->where('activo', true);
        // Prepara en $categoriasActivas la consulta que obtiene o modifica los registros necesarios.
        $categoriasActivas = Categoria::query()->where('empresa_id', $empresa->id)->where('activo', true)->count();
        // Prepara en $mediosPago la consulta que obtiene o modifica los registros necesarios.
        $mediosPago = DB::table('medios_pago')->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        // Guarda en $configuracionTiendaDisponible el resultado necesario para continuar esta operacion.
        $configuracionTiendaDisponible = Schema::hasTable('configuraciones_tienda');
        // Guarda en $configuracionTienda el resultado necesario para continuar esta operacion.
        $configuracionTienda = $servicioConfiguracion->consultar((int) $empresa->id);
        // Guarda en $puedeActualizarConfiguracion el resultado necesario para continuar esta operacion.
        $puedeActualizarConfiguracion = $configuracionTiendaDisponible
            && $autorizacion->allowsGlobal($request->user(), 'gestion.configuracion.actualizar');
        // Guarda en $productosVista el resultado necesario para continuar esta operacion.
        $productosVista = (clone $productosActivos)->with('category:id,nombre')->orderBy('nombre')->limit(2)->get();
        // Guarda en $sinConfiguracion el resultado necesario para continuar esta operacion.
        $sinConfiguracion = $configuracionTiendaDisponible ? 'Sin valor configurado en MariaDB.' : 'No existe una entidad de configuración de tienda en el esquema actual.';
        // Agrupa en $secciones los datos que se utilizaran en la siguiente operacion.
        $secciones = [
            ['clave' => 'general', 'titulo' => 'General', 'icono' => 'bi-gear', 'resumen' => 'Información de la empresa', 'filas' => [
                ['Nombre comercial', $empresa->nombre_comercial ?: $empresa->razon_social],
                ['Razón social', $empresa->razon_social],
                ['RUC', $empresa->ruc ?: 'Sin RUC registrado'],
                ['Moneda base', $empresa->moneda_base],
                ['Estado', $empresa->activo ? 'Activa' : 'Inactiva'],
            ]],
            ['clave' => 'apariencia', 'titulo' => 'Apariencia', 'icono' => 'bi-image', 'resumen' => 'Identidad visual', 'filas' => [
                ['Logo de tienda', $sinConfiguracion], ['Banner principal', $sinConfiguracion], ['Color de tienda', 'El diseño actual se conserva; no hay preferencia persistente en MariaDB.'],
            ]],
            ['clave' => 'pagos', 'titulo' => 'Pagos', 'icono' => 'bi-credit-card', 'resumen' => 'Medios de pago registrados', 'filas' => [
                ['Medios activos', (string) $mediosPago->count()], ['Opciones', $mediosPago->isEmpty() ? 'No hay medios de pago activos' : $mediosPago->pluck('nombre')->implode(', ')],
                ['Confirmación externa', 'No habilitada en este módulo.'],
            ]],
            ['clave' => 'envios', 'titulo' => 'Envíos', 'icono' => 'bi-truck', 'resumen' => 'Configuración de entregas', 'filas' => [
                ['Cobertura', $sinConfiguracion], ['Tarifas', $sinConfiguracion], ['Seguimiento', 'Consultar en el módulo de envíos existente.'],
            ]],
            ['clave' => 'notificaciones', 'titulo' => 'Notificaciones', 'icono' => 'bi-envelope', 'resumen' => 'Avisos a clientes', 'filas' => [
                ['Plantillas', $sinConfiguracion], ['Correo transaccional', $sinConfiguracion],
            ]],
            ['clave' => 'seo', 'titulo' => 'SEO y Analítica', 'icono' => 'bi-bar-chart', 'resumen' => 'Visibilidad y medición', 'filas' => [
                ['Título SEO', $sinConfiguracion], ['Descripción SEO', $sinConfiguracion], ['Analítica', $sinConfiguracion],
            ]],
            ['clave' => 'integraciones', 'titulo' => 'Integraciones', 'icono' => 'bi-link-45deg', 'resumen' => 'Servicios conectados', 'filas' => [
                ['Configuración de integraciones', $sinConfiguracion], ['Pasarelas externas', 'No implementadas en esta etapa.'],
            ]],
        ];

        // Recorre $secciones as &$seccion y procesa cada elemento de la coleccion.
        foreach ($secciones as &$seccion) {
            // Recorre $seccion['filas'] as $indiceFila => &$fila y procesa cada elemento de la coleccion.
            foreach ($seccion['filas'] as $indiceFila => &$fila) {
                // Guarda en $valorConfigurado el resultado necesario para continuar esta operacion.
                $valorConfigurado = match ([$seccion['clave'], $indiceFila]) {
                    ['general', 0] => data_get($configuracionTienda, 'general.nombre_tienda'),
                    ['apariencia', 2] => data_get($configuracionTienda, 'apariencia.color_principal'),
                    ['envios', 1] => data_get($configuracionTienda, 'envios.costo_estandar') === null
                        ? null
                        : $empresa->moneda_base.' '.number_format((float) data_get($configuracionTienda, 'envios.costo_estandar'), 2),
                    ['notificaciones', 0] => data_get($configuracionTienda, 'notificaciones.confirmacion_pedido'),
                    ['notificaciones', 1] => data_get($configuracionTienda, 'notificaciones.correo_remitente'),
                    ['seo', 0] => data_get($configuracionTienda, 'seo.titulo'),
                    ['seo', 1] => data_get($configuracionTienda, 'seo.descripcion'),
                    ['seo', 2] => data_get($configuracionTienda, 'seo.analitica_id'),
                    default => null,
                };
                // Comprueba la condicion $valorConfigurado !== null antes de ejecutar este bloque.
                if ($valorConfigurado !== null) {
                    $fila[1] = $valorConfigurado;
                }
            }
            unset($fila);
        }
        unset($seccion);

        // Devuelve la vista 'gestion.ecommerce-marketing.catalogo-publico.listar' con los datos preparados por este controlador.
        return view('gestion.ecommerce-marketing.catalogo-publico.listar', [
            'empresa' => $empresa,
            'mediosPago' => $mediosPago,
            'configuracionTienda' => $configuracionTienda,
            'nombreTienda' => data_get($configuracionTienda, 'general.nombre_tienda', $empresa->nombre_comercial ?: $empresa->razon_social),
            'configuracionTiendaDisponible' => $configuracionTiendaDisponible,
            'puedeActualizarConfiguracion' => $puedeActualizarConfiguracion,
            'secciones' => $secciones,
            'productosActivosCount' => (clone $productosActivos)->count(),
            'categoriasActivasCount' => $categoriasActivas,
            'productosVista' => $productosVista,
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $servicioConfiguracion  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function guardarConfiguracionTienda(GuardarConfiguracionTiendaRequest $request, ServicioConfiguracionTienda $servicioConfiguracion): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $servicioConfiguracion->guardar($request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.products.preview.store_settings')->with('status', 'La configuración de tienda quedó guardada en MariaDB.');
    }

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioInventario $inventario, ServicioPresentacionInventario $presentacion): View
    {
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Obtiene en $alcance la informacion de la solicitud o del usuario actual.
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.consultar');
        // Guarda en $resumen el resultado necesario para continuar esta operacion.
        $resumen = $inventario->resumenProductos($alcance);
        // Guarda en $now el resultado necesario para continuar esta operacion.
        $now = now();
        // Guarda en $currentMonthStart el resultado necesario para continuar esta operacion.
        $currentMonthStart = $now->copy()->startOfMonth();
        // Guarda en $currentMonthEnd el resultado necesario para continuar esta operacion.
        $currentMonthEnd = $now->copy()->endOfMonth();
        // Guarda en $previousMonthStart el resultado necesario para continuar esta operacion.
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        // Guarda en $previousMonthEnd el resultado necesario para continuar esta operacion.
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();
        // Prepara en $productosEmpresa la consulta que obtiene o modifica los registros necesarios.
        $productosEmpresa = Producto::query()->where('empresa_id', $alcance['empresa_id']);
        // Guarda en $productosAgregadosEsteMes el resultado necesario para continuar esta operacion.
        $productosAgregadosEsteMes = (clone $productosEmpresa)
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        // Guarda en $productosAgregadosMesAnterior el resultado necesario para continuar esta operacion.
        $productosAgregadosMesAnterior = (clone $productosEmpresa)
            ->where('activo', true)
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();
        // Prepara en $categoryCount la consulta que obtiene o modifica los registros necesarios.
        $categoryCount = Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->count();
        // Guarda en $idProductoEditar el resultado necesario para continuar esta operacion.
        $idProductoEditar = $request->integer('editar');
        // Guarda en $consultaProductos el resultado necesario para continuar esta operacion.
        $consultaProductos = $this->consultaProductos($filters, $alcance, $inventario);
        // Comprueba la condicion $idProductoEditar > 0 antes de ejecutar este bloque.
        if ($idProductoEditar > 0) {
            $consultaProductos->where('productos.id', $idProductoEditar);
        }
        // Guarda en $paginaProductos el resultado necesario para continuar esta operacion.
        $paginaProductos = $consultaProductos
            ->with([
                'category' => function (BelongsTo $relacion) use ($alcance): void {
                    $relacion->where('empresa_id', $alcance['empresa_id']);
                },
                'suppliers' => function (BelongsToMany $relacion) use ($alcance): void {
                    $relacion->where('empresa_id', $alcance['empresa_id']);
                },
                'variants:id,producto_id,codigo_sku,codigo_barras,nombre_color,codigo_color,estado,controla_serie',
                'variants.images',
            ])
            ->orderByDesc('productos.id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        // Guarda en $idsProductos el resultado necesario para continuar esta operacion.
        $idsProductos = $paginaProductos->getCollection()
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        // Guarda en $saldosPorVariante el resultado necesario para continuar esta operacion.
        $saldosPorVariante = $inventario->saldosPorVariante($alcance, $idsProductos);

        // Recorre $paginaProductos->getCollection() as $producto y procesa cada elemento de la coleccion.
        foreach ($paginaProductos->getCollection() as $producto) {
            // Recorre $producto->variants as $variante y procesa cada elemento de la coleccion.
            foreach ($producto->variants as $variante) {
                // Guarda en $saldo el resultado necesario para continuar esta operacion.
                $saldo = $saldosPorVariante->get($variante->id);
                $variante->setAttribute('existencias_alcance', (float) ($saldo->cantidad_fisica ?? 0));
                $variante->setAttribute('existencias_reservadas_alcance', (float) ($saldo->cantidad_reservada ?? 0));
            }
        }

        // Guarda en $productPresentation el resultado necesario para continuar esta operacion.
        $productPresentation = $paginaProductos->getCollection()
            ->mapWithKeys(fn (Producto $producto): array => [
                $producto->id => $presentacion->productoCatalogo($producto),
            ])
            ->all();

        // Devuelve la vista 'gestion.inventario-logistica.productos.listar' con los datos preparados por este controlador.
        return view('gestion.inventario-logistica.productos.listar', [
            'products' => $paginaProductos,
            'exportParameters' => $request->query(),
            'productPresentation' => $productPresentation,
            'categories' => Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Schema::hasTable('marcas')
                ? Marca::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->pluck('nombre')
                : Producto::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->whereNotNull('marca')->where('marca', '<>', '')->distinct()->orderBy('marca')->pluck('marca'),
            'suppliers' => Proveedor::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $filters,
            'idProductoEditar' => $idProductoEditar,
            'activeProducts' => $resumen['productos_activos'],
            'productsTrendPercent' => $this->porcentajeTendencia($productosAgregadosEsteMes, $productosAgregadosMesAnterior),
            'productsWithStock' => $resumen['productos_con_existencias'],
            'outOfStock' => $resumen['productos_sin_existencias'],
            'categoryCount' => $categoryCount,
            'inventoryValue' => $resumen['valor_inventario'],
            'offerCount' => $resumen['productos_en_oferta'],
        ]);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, string $format, ServicioInventario $inventario, ?ExportadorTabular $exporter = null): Response|StreamedResponse
    {
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        $filtrosExportacion = $request->validate(['marca' => ['nullable', 'string', 'max:80']]);
        // Obtiene en $alcance la informacion de la solicitud o del usuario actual.
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.exportar');
        // Guarda en $products el resultado necesario para continuar esta operacion.
        $products = $this->consultaProductos($filters, $alcance, $inventario)->orderBy('productos.id');
        // Comprueba la condicion ($filtrosExportacion['marca'] ?? null) !== null antes de ejecutar este bloque.
        if (($filtrosExportacion['marca'] ?? null) !== null) {
            $products->where('productos.marca', $filtrosExportacion['marca']);
        }

        return match ($format) {
            'csv' => $this->descargarCsv($products),
            'excel' => $this->descargarReporteCorporativo($products->get(), 'excel', $filters, $alcance, $request, $exporter ?? new ExportadorTabular),
            'pdf' => $this->descargarReporteCorporativo($products->get(), 'pdf', $filters, $alcance, $request, $exporter ?? new ExportadorTabular),
            default => abort(404),
        };
    }

    public function importTemplate(ExportadorTabular $exporter): Response|StreamedResponse
    {
        // Agrupa en $columns los datos que se utilizaran en la siguiente operacion.
        $columns = [
            'marca', 'nombre', 'categoria_id', 'precio', 'precio_original', 'descuento',
            'precio_oferta', 'url_imagen', 'existencias', 'almacenamiento', 'color',
            'etiqueta', 'descripcion', 'activo', 'proveedor_id',
        ];

        return $exporter->download('excel', 'plantilla-importacion-productos', $columns, [array_fill(0, count($columns), null)], [
            'title' => 'Plantilla de importación de productos',
            'description' => 'Completa una fila por producto. No cambies los nombres de las columnas.',
        ]);
    }

    public function import(
        ImportarProductosRequest $request,
        ServicioInventario $inventario,
        ServicioGestionProductos $productos,
    ): RedirectResponse {
        // Obtiene en $alcance la informacion de la solicitud o del usuario actual.
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.crear');
        // Agrupa en $columns los datos que se utilizaran en la siguiente operacion.
        $columns = [
            'marca', 'nombre', 'categoria_id', 'precio', 'precio_original', 'descuento',
            'precio_oferta', 'url_imagen', 'existencias', 'almacenamiento', 'color',
            'etiqueta', 'descripcion', 'activo', 'proveedor_id',
        ];
        // Guarda en $file el resultado necesario para continuar esta operacion.
        $file = $request->file('archivo');
        // Guarda en $path el resultado necesario para continuar esta operacion.
        $path = $file->getRealPath();
        // Guarda en $temporaryCsv el resultado necesario para continuar esta operacion.
        $temporaryCsv = null;

        // Comprueba la condicion strtolower($file->getClientOriginalExtension()) === 'xlsx' antes de ejecutar este bloque.
        if (strtolower($file->getClientOriginalExtension()) === 'xlsx') {
            // Guarda en $temporaryCsv el resultado necesario para continuar esta operacion.
            $temporaryCsv = tempnam(sys_get_temp_dir(), 'tc-products-');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($temporaryCsv === false, 422, 'No se pudo preparar el archivo Excel.');
            // Guarda en $output el resultado necesario para continuar esta operacion.
            $output = fopen($temporaryCsv, 'wb');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($output === false, 422, 'No se pudo preparar el archivo Excel.');
            fputcsv($output, $columns);
            // Recorre $this->leerFilasXlsx($file) as $row y procesa cada elemento de la coleccion.
            foreach ($this->leerFilasXlsx($file) as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            // Guarda en $path el resultado necesario para continuar esta operacion.
            $path = $temporaryCsv;
        }

        // Guarda en $handle el resultado necesario para continuar esta operacion.
        $handle = fopen($path, 'rb');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($handle === false, 422, 'No se pudo leer el archivo de productos.');
        // Guarda en $header el resultado necesario para continuar esta operacion.
        $header = fgetcsv($handle);
        // Comprueba la condicion $header !== $columns antes de ejecutar este bloque.
        if ($header !== $columns) {
            fclose($handle);
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->eliminarArchivoTemporal($temporaryCsv);
            throw ValidationException::withMessages(['archivo' => 'El archivo debe contener exactamente la plantilla de productos.']);
        }

        // Agrupa en $rows los datos que se utilizaran en la siguiente operacion.
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Comprueba la condicion $row === [] || array_filter($row, static fn (mixed $value): bool => trim((string) $value) !== '') === [] antes de ejecutar este bloque.
            if ($row === [] || array_filter($row, static fn (mixed $value): bool => trim((string) $value) !== '') === []) {
                continue;
            }
            // Comprueba la condicion count($row) !== count($columns) antes de ejecutar este bloque.
            if (count($row) !== count($columns)) {
                fclose($handle);
                // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                $this->eliminarArchivoTemporal($temporaryCsv);
                throw ValidationException::withMessages(['archivo' => 'Todas las filas deben tener las columnas de la plantilla.']);
            }
            $rows[] = array_combine($columns, array_map(static fn (mixed $value): string => trim((string) $value), $row));
        }
        fclose($handle);
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->eliminarArchivoTemporal($temporaryCsv);

        // Comprueba la condicion $rows === [] antes de ejecutar este bloque.
        if ($rows === []) {
            throw ValidationException::withMessages(['archivo' => 'El archivo no contiene productos para importar.']);
        }
        // Comprueba la condicion count($rows) > 2000 antes de ejecutar este bloque.
        if (count($rows) > 2000) {
            throw ValidationException::withMessages(['archivo' => 'La importación admite como máximo 2,000 productos por archivo.']);
        }

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($rows, $alcance, $productos, $request): void {
            // Recorre $rows as $index => $row y procesa cada elemento de la coleccion.
            foreach ($rows as $index => $row) {
                // Guarda en $validator el resultado necesario para continuar esta operacion.
                $validator = Validator::make($row, [
                    'marca' => ['required', 'string', 'max:80'],
                    'nombre' => ['required', 'string', 'max:160'],
                    'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('empresa_id', $alcance['empresa_id'])],
                    'precio' => ['required', 'numeric', 'min:0'],
                    'precio_original' => ['nullable', 'numeric', 'min:0'],
                    'descuento' => ['nullable', 'string', 'max:20'],
                    'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio'],
                    'url_imagen' => ['nullable', 'string', 'max:255'],
                    'existencias' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
                    'almacenamiento' => ['nullable', 'string', 'max:80'],
                    'color' => ['nullable', 'string', 'max:80'],
                    'etiqueta' => ['nullable', 'string', 'max:80'],
                    'descripcion' => ['nullable', 'string'],
                    'activo' => ['nullable', 'boolean'],
                    'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('empresa_id', $alcance['empresa_id'])],
                ]);
                // Comprueba la condicion $validator->fails() antes de ejecutar este bloque.
                if ($validator->fails()) {
                    throw ValidationException::withMessages(['archivo' => 'Fila '.($index + 2).': '.$validator->errors()->first()]);
                }

                // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
                $validated = $validator->validated();
                $supplierId = isset($validated['proveedor_id']) && $validated['proveedor_id'] !== ''
                    ? (int) $validated['proveedor_id']
                    : null;
                $productos->crear($this->atributos($validated), $supplierId, $request->user(), (string) $request->ip());
            }
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.products')->with('status', count($rows).' productos importados correctamente.');
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
     */
    public function store(
        GuardarProductoRequest $request,
        ServicioGestionProductos $productos,
    ): RedirectResponse {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $validated = $request->validated();
        // Guarda en $supplierId el resultado necesario para continuar esta operacion.
        $supplierId = $validated['proveedor_id'] ?? null;
        // Guarda en $data el resultado necesario para continuar esta operacion.
        $data = $this->atributos($validated);
        // Guarda en $rutaImagenGuardada el resultado necesario para continuar esta operacion.
        $rutaImagenGuardada = null;

        // Comprueba la condicion $request->hasFile('imagen') antes de ejecutar este bloque.
        if ($request->hasFile('imagen')) {
            // Guarda en $rutaImagenGuardada el resultado necesario para continuar esta operacion.
            $rutaImagenGuardada = $request->file('imagen')->store('productos', 'public');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($rutaImagenGuardada === false, 500, 'No se pudo guardar la imagen del producto.');
            $data['url_imagen'] = 'storage/'.$rutaImagenGuardada;
        }

        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            $productos->crear($data, $supplierId !== null ? (int) $supplierId : null, $request->user(), (string) $request->ip());
        } catch (Throwable $excepcion) {
            // Comprueba la condicion is_string($rutaImagenGuardada) antes de ejecutar este bloque.
            if (is_string($rutaImagenGuardada)) {
                Storage::disk('public')->delete($rutaImagenGuardada);
            }

            throw $excepcion;
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()
            ->with('status', (int) ($validated['existencias'] ?? 0) > 0
                ? 'Producto guardado con sus existencias iniciales.'
                : 'Producto guardado. Registra las existencias desde el módulo Inventario.')
            ->with('open_product_modal', $request->boolean('continue'));
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $productos  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarProductoRequest $request, Producto $product, ServicioGestionProductos $productos): RedirectResponse
    {
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->authorize('update', $product);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $validated = $request->validated();
        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'precio_oferta', 'existencias', 'url_imagen']);
        // Guarda en $data el resultado necesario para continuar esta operacion.
        $data = $this->atributos($validated);
        // Guarda en $rutaImagenGuardada el resultado necesario para continuar esta operacion.
        $rutaImagenGuardada = null;

        // Comprueba la condicion $request->hasFile('imagen') antes de ejecutar este bloque.
        if ($request->hasFile('imagen')) {
            // Guarda en $rutaImagenGuardada el resultado necesario para continuar esta operacion.
            $rutaImagenGuardada = $request->file('imagen')->store('productos', 'public');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($rutaImagenGuardada === false, 500, 'No se pudo guardar la imagen del producto.');
            $data['url_imagen'] = 'storage/'.$rutaImagenGuardada;
        }

        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            $productos->actualizar($product, $data, $validated, $before, $request->user(), (string) $request->ip());
        } catch (Throwable $excepcion) {
            // Comprueba la condicion is_string($rutaImagenGuardada) antes de ejecutar este bloque.
            if (is_string($rutaImagenGuardada)) {
                Storage::disk('public')->delete($rutaImagenGuardada);
            }

            throw $excepcion;
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Producto actualizado.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->authorize('delete', $product);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($product, $audit): void {
            $product->update(['activo' => false]);
            $audit->record(request()->user()->id, 'product.deactivated', 'productos', $product->id, ['activo' => true], ['activo' => false], request()->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Producto desactivado; su historial comercial se conserva.');
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
     */
    public function registrarVariante(
        GuardarVarianteProductoRequest $request,
        Producto $product,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        // Guarda en $usuario el resultado necesario para continuar esta operacion.
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $variantes->registrar($product, $request->validated(), $usuario, $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Variante registrada.')->with('open_variant_manager', $product->id);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @return mixed Resultado de la operación.
     */
    public function actualizarVariante(
        GuardarVarianteProductoRequest $request,
        Producto $product,
        VarianteProducto $variante,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        // Guarda en $usuario el resultado necesario para continuar esta operacion.
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $variantes->actualizar($product, $variante, $request->validated(), $usuario, $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Variante actualizada.')->with('open_variant_manager', $product->id);
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @return mixed Resultado de la operación.
     */
    public function desactivarVariante(
        Request $request,
        Producto $product,
        VarianteProducto $variante,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        // Guarda en $usuario el resultado necesario para continuar esta operacion.
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $variantes->desactivar($product, $variante, $usuario, $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Variante desactivada.')->with('open_variant_manager', $product->id);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
     */
    public function registrarImagenVariante(
        GuardarImagenVarianteRequest $request,
        Producto $product,
        VarianteProducto $variante,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        // Guarda en $usuario el resultado necesario para continuar esta operacion.
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        // Guarda en $archivo el resultado necesario para continuar esta operacion.
        $archivo = $request->file('imagen');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($archivo instanceof UploadedFile, 422, 'Selecciona una imagen válida.');

        $variantes->registrarImagen(
            $product,
            $variante,
            $archivo,
            $request->boolean('imagen_principal'),
            $usuario,
            $request->ip(),
        );

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()
            ->with('status', 'Imagen de variante registrada.')
            ->with('open_variant_manager', $product->id)
            ->with('abrir_editor_variante', $variante->id);
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @return mixed Resultado de la operación.
     */
    public function eliminarImagenVariante(
        Request $request,
        Producto $product,
        VarianteProducto $variante,
        int $imagen,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        // Guarda en $usuario el resultado necesario para continuar esta operacion.
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $variantes->eliminarImagen($product, $variante, $imagen, $usuario, $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()
            ->with('status', 'Imagen de variante eliminada.')
            ->with('open_variant_manager', $product->id)
            ->with('abrir_editor_variante', $variante->id);
    }

    /** @param array<string, mixed> $data */
    private function atributos(array $data): array
    {
        // Guarda en $category el resultado necesario para continuar esta operacion.
        $category = Categoria::findOrFail($data['categoria_id']);
        $data['categoria'] = $category->nombre;
        unset($data['proveedor_id'], $data['imagen'], $data['continue']);

        return $data;
    }

    /** @return array<int, array<int, string>> */
    private function leerFilasXlsx(UploadedFile $file): array
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(class_exists(ZipArchive::class), 422, 'El servidor no tiene habilitada la lectura de archivos Excel.');
        // Guarda en $zip el resultado necesario para continuar esta operacion.
        $zip = new ZipArchive;
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($zip->open($file->getRealPath()) === true, 422, 'El archivo Excel no se puede abrir.');
        // Guarda en $sheet el resultado necesario para continuar esta operacion.
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(is_string($sheet), 422, 'El archivo Excel no contiene una hoja válida.');

        // Guarda en $dom el resultado necesario para continuar esta operacion.
        $dom = new \DOMDocument;
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(@$dom->loadXML($sheet) === true, 422, 'La hoja Excel no tiene un formato válido.');
        // Guarda en $namespace el resultado necesario para continuar esta operacion.
        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        // Agrupa en $rows los datos que se utilizaran en la siguiente operacion.
        $rows = [];
        // Recorre $dom->getElementsByTagNameNS($namespace, 'row') as $rowNode y procesa cada elemento de la coleccion.
        foreach ($dom->getElementsByTagNameNS($namespace, 'row') as $rowNode) {
            // Agrupa en $values los datos que se utilizaran en la siguiente operacion.
            $values = [];
            // Recorre $rowNode->getElementsByTagNameNS($namespace, 'c') as $cell y procesa cada elemento de la coleccion.
            foreach ($rowNode->getElementsByTagNameNS($namespace, 'c') as $cell) {
                preg_match('/^([A-Z]+)/', (string) $cell->getAttribute('r'), $match);
                // Guarda en $column el resultado necesario para continuar esta operacion.
                $column = 0;
                // Recorre str_split($match[1] ?? 'A') as $letter y procesa cada elemento de la coleccion.
                foreach (str_split($match[1] ?? 'A') as $letter) {
                    // Guarda en $column el resultado necesario para continuar esta operacion.
                    $column = ($column * 26) + ord($letter) - 64;
                }
                // Guarda en $valueNode el resultado necesario para continuar esta operacion.
                $valueNode = $cell->getElementsByTagNameNS($namespace, 't')->item(0)
                    ?: $cell->getElementsByTagNameNS($namespace, 'v')->item(0);
                $values[$column - 1] = $valueNode?->textContent ?? '';
            }
            ksort($values);
            $rows[] = array_pad(array_values($values), 15, '');
        }

        return array_slice($rows, 1);
    }

    private function eliminarArchivoTemporal(?string $path): void
    {
        // Comprueba la condicion $path !== null && file_exists($path) antes de ejecutar este bloque.
        if ($path !== null && file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Ejecuta la operación usuarioConAccesoProducto definida por la clase GestionProductosController.
     *
     * @return mixed Resultado de la operación.
     */
    private function usuarioConAccesoProducto(
        Request $request,
        Producto $product,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
    ): User {
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);

        // Guarda en $permiso el resultado necesario para continuar esta operacion.
        $permiso = $autorizacion->permissionForRoute($request);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($permiso !== null, 403);
        // Guarda en $alcance el resultado necesario para continuar esta operacion.
        $alcance = $inventario->alcanceConsulta($usuario, $permiso);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless((int) $product->empresa_id === $alcance['empresa_id'], 404);

        return $usuario;
    }

    /**
     * @param  array{buscar:?string,categoria:?int,stock:?string,estado:?string,oferta:bool,por_pagina:int}  $filters
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Builder<Producto>
     */
    private function consultaProductos(array $filters, array $alcance, ServicioInventario $inventario): Builder
    {
        return $inventario->productosParaConsulta($alcance, $filters, true, true);
    }

    /**
     * @return array{buscar:?string,categoria:?int,stock:?string,estado:?string,oferta:bool,por_pagina:int}
     */
    private function filtros(Request $request): array
    {
        // Guarda en $stock el resultado necesario para continuar esta operacion.
        $stock = trim((string) $request->input('stock', ''));
        // Guarda en $status el resultado necesario para continuar esta operacion.
        $status = trim((string) $request->input('estado', ''));
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim((string) $request->input('buscar', ''));
        // Guarda en $perPage el resultado necesario para continuar esta operacion.
        $perPage = $request->integer('por_pagina', 8);

        return [
            'buscar' => $search !== '' ? $search : null,
            'categoria' => $request->integer('categoria') ?: null,
            'stock' => in_array($stock, ['sin_stock', 'con_stock'], true) ? $stock : null,
            'estado' => in_array($status, ['activo', 'inactivo'], true) ? $status : null,
            'oferta' => $request->boolean('oferta'),
            'por_pagina' => in_array($perPage, [8, 10, 20, 50], true) ? $perPage : 8,
        ];
    }

    /**
     * Ejecuta la operación trendPercent definida por la clase GestionProductosController.
     *
     * @param  mixed  $current  Parámetro recibido por la operación.
     * @param  mixed  $previous  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function porcentajeTendencia(int $current, int $previous): ?float
    {
        // Comprueba la condicion $previous === 0 antes de ejecutar este bloque.
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  Builder<Producto>  $query
     */
    private function descargarCsv(Builder $query): StreamedResponse
    {
        // Guarda en $products el resultado necesario para continuar esta operacion.
        $products = $query->cursor();

        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
        return response()->streamDownload(function () use ($products): void {
            // Guarda en $output el resultado necesario para continuar esta operacion.
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'producto', 'marca', 'categoria', 'precio', 'stock', 'estado']);

            // Recorre $products as $product y procesa cada elemento de la coleccion.
            foreach ($products as $product) {
                fputcsv($output, $this->filaExportacion($product));
            }

            fclose($output);
        }, 'catalogo-productos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Collection<int, Producto>  $products
     * @param  array{buscar:?string,categoria:?int,stock:?string,estado:?string,oferta:bool,por_pagina:int}  $filters
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     */
    private function descargarReporteCorporativo(Collection $products, string $format, array $filters, array $alcance, Request $request, ExportadorTabular $exporter): Response
    {
        $rows = $products->map(fn (Producto $product): array => $this->filaExportacion($product))->all();
        $stockTotal = array_sum(array_map(static fn (array $row): int => (int) ($row[5] ?? 0), $rows));
        $outOfStockCount = count(array_filter($rows, static fn (array $row): bool => (int) ($row[5] ?? 0) === 0));
        // Guarda en $stockLabel el resultado necesario para continuar esta operacion.
        $stockLabel = match ($filters['stock']) {
            'sin_stock' => 'Sin stock',
            'con_stock' => 'Con existencias',
            default => 'Todos',
        };
        // Guarda en $statusLabel el resultado necesario para continuar esta operacion.
        $statusLabel = match ($filters['estado']) {
            'activo' => 'Activos',
            'inactivo' => 'Inactivos',
            default => 'Todos',
        };
        // Agrupa en $columns los datos que se utilizaran en la siguiente operacion.
        $columns = ['ID', 'Producto', 'Marca', 'Categoría', 'Precio de venta PEN', 'Stock', 'Estado'];
        // Agrupa en $report los datos que se utilizaran en la siguiente operacion.
        $report = [
            'title' => 'REPORTE DE PRODUCTOS',
            'subtitle' => 'Catálogo de productos con los filtros de consulta aplicados.',
            'module' => 'Inventario y Logística / Productos',
            'report_name' => 'Reporte de productos',
            'period' => 'Sin período temporal; catálogo actual',
            'filters' => [
                'Búsqueda' => $filters['buscar'] ?? 'Todos',
                'Categoría' => $filters['categoria'] === null ? 'Todas' : 'ID '.$filters['categoria'],
                'Stock' => $stockLabel,
                'Estado' => $statusLabel,
            ],
            'generated_by' => (string) ($request->user()?->nombre ?? 'No disponible'),
            'role' => (string) ($request->user()?->rol ?? 'No disponible'),
            'scope' => $alcance['sede_id'] === null
                ? 'Empresa '.$alcance['empresa_id']
                : 'Sede '.$alcance['sede_id'].' · empresa '.$alcance['empresa_id'],
            'indicators' => [
                ['label' => 'Productos incluidos', 'value' => count($rows)],
                ['label' => 'Existencias reportadas', 'value' => $stockTotal],
                ['label' => 'Productos sin stock', 'value' => $outOfStockCount],
            ],
            'column_types' => [0 => 'integer', 4 => 'currency', 5 => 'integer'],
            'summary_row' => [
                'label' => 'Productos del conjunto filtrado: '.count($rows),
                'values' => ['', '', '', '', '', $stockTotal, ''],
            ],
            'observations' => ['El precio corresponde al precio de venta vigente; no es un costo contable ni un valor confirmado de inventario.'],
        ];

        return $exporter->download($format, 'productos', $columns, $rows, $report);
    }

    /**
     * @param  Collection<int, Producto>  $products
     */
    private function descargarExcel(Collection $products): Response
    {
        $rows = $products->map(fn (Producto $product): string => '<tr>'.implode('', array_map(
            fn (string|int|float $value): string => '<td>'.htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</td>',
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->filaExportacion($product),
        )).'</tr>')->implode('');
        // Guarda en $html el resultado necesario para continuar esta operacion.
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif}table{border-collapse:collapse}th,td{border:1px solid #d9e3f0;padding:7px}th{background:#eaf2ff;color:#10234c;text-align:left}</style></head><body><h1>Catálogo de productos</h1><table><thead><tr><th>ID</th><th>Producto</th><th>Marca</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th></tr></thead><tbody>'.$rows.'</tbody></table></body></html>';

        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="catalogo-productos.xls"',
        ]);
    }

    /**
     * @param  Collection<int, Producto>  $products
     */
    private function descargarPdf(Collection $products): Response
    {
        // Agrupa en $header los datos que se utilizaran en la siguiente operacion.
        $header = [
            'CATALOGO DE PRODUCTOS',
            'Generado: '.now()->format('d/m/Y H:i'),
            '',
            'ID | Producto | Marca | Categoria | Precio | Stock | Estado',
            str_repeat('-', 108),
        ];
        // Guarda en $productLines el resultado necesario para continuar esta operacion.
        $productLines = $products->map(function (Producto $product): string {
            // Guarda en $row el resultado necesario para continuar esta operacion.
            $row = $this->filaExportacion($product);

            return implode(' | ', [
                $row[0],
                mb_substr((string) $row[1], 0, 30),
                mb_substr((string) $row[2], 0, 18),
                mb_substr((string) $row[3], 0, 18),
                'S/ '.$row[4],
                $row[5],
                $row[6],
            ]);
        })->all();
        // Guarda en $chunks el resultado necesario para continuar esta operacion.
        $chunks = array_chunk($productLines, 31);
        // Agrupa en $pages los datos que se utilizaran en la siguiente operacion.
        $pages = [];

        // Recorre $chunks ?: [[]] as $index => $chunk y procesa cada elemento de la coleccion.
        foreach ($chunks ?: [[]] as $index => $chunk) {
            $pages[] = array_merge(
                $index === 0 ? $header : ['CATALOGO DE PRODUCTOS (continuacion)', '', $header[3], $header[4]],
                $chunk,
            );
        }

        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
        return response($this->construirDocumentoPdf($pages), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="catalogo-productos.pdf"',
        ]);
    }

    /**
     * @return array<int, string|int|float>
     */
    private function filaExportacion(Producto $product): array
    {
        // Guarda en $existencias el resultado necesario para continuar esta operacion.
        $existencias = (float) ($product->existencias_alcance ?? 0);

        return [
            $product->id,
            $product->nombre,
            $product->marca,
            $product->categoria ?: 'Sin categoría',
            number_format((float) $product->effective_price, 2, '.', ''),
            $existencias,
            $product->activo ? ($existencias <= 0 ? 'Sin stock' : ($product->precio_oferta ? 'Oferta' : 'Activo')) : 'Inactivo',
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $pages
     */
    private function construirDocumentoPdf(array $pages): string
    {
        // Agrupa en $objects los datos que se utilizaran en la siguiente operacion.
        $objects = [];
        // Agrupa en $pageObjectIds los datos que se utilizaran en la siguiente operacion.
        $pageObjectIds = [];
        // Agrupa en $contentObjectIds los datos que se utilizaran en la siguiente operacion.
        $contentObjectIds = [];
        // Guarda en $nextObjectId el resultado necesario para continuar esta operacion.
        $nextObjectId = 4;

        // Recorre $pages as $page y procesa cada elemento de la coleccion.
        foreach ($pages as $page) {
            $pageObjectIds[] = $nextObjectId++;
            $contentObjectIds[] = $nextObjectId++;
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id.' 0 R', $pageObjectIds));
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageObjectIds).' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        // Recorre $pages as $index => $page y procesa cada elemento de la coleccion.
        foreach ($pages as $index => $page) {
            // Guarda en $content el resultado necesario para continuar esta operacion.
            $content = '';
            // Guarda en $y el resultado necesario para continuar esta operacion.
            $y = 560;

            // Recorre $page as $lineIndex => $line y procesa cada elemento de la coleccion.
            foreach ($page as $lineIndex => $line) {
                $fontSize = $lineIndex === 0 ? 14 : 8;
                $content .= "BT\n/F1 {$fontSize} Tf\n30 {$y} Td\n(".$this->escaparTextoPdf($line).") Tj\nET\n";
                $y -= 15;
            }

            // Guarda en $contentObjectId el resultado necesario para continuar esta operacion.
            $contentObjectId = $contentObjectIds[$index];
            // Guarda en $pageObjectId el resultado necesario para continuar esta operacion.
            $pageObjectId = $pageObjectIds[$index];
            $objects[$contentObjectId] = '<< /Length '.strlen($content)." >>\nstream\n{$content}endstream";
            $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObjectId.' 0 R >>';
        }

        ksort($objects);
        // Guarda en $pdf el resultado necesario para continuar esta operacion.
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        // Agrupa en $offsets los datos que se utilizaran en la siguiente operacion.
        $offsets = [0];

        // Recorre $objects as $id => $object y procesa cada elemento de la coleccion.
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n{$object}\nendobj\n";
        }

        // Guarda en $xrefOffset el resultado necesario para continuar esta operacion.
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        // Repite este bloque usando la configuracion del ciclo $id = 1; $id <= count($objects); $id++.
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf('%010d 00000 n \n', $offsets[$id]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    /**
     * Escapa texto para el documento PDF generado por la clase GestionProductosController.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function escaparTextoPdf(string $value): string
    {
        // Guarda en $encoded el resultado necesario para continuar esta operacion.
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded === false ? $value : $encoded);
    }
}
