<?php

namespace App\Http\Controllers\Gestion\Productos;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarProductoRequest;
use App\Http\Requests\GuardarImagenVarianteRequest;
use App\Http\Requests\GuardarProductoRequest;
use App\Http\Requests\GuardarVarianteProductoRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use App\Models\Compras\Proveedor;
use App\Models\Organizacion\Empresa;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCatalogo;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioVariantesProducto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GestionProductosController extends Controller
{
    public function preciosCanales(Request $request, ServicioInventario $inventario): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
            'marca' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'canal' => ['nullable', 'in:minorista,mayorista'],
        ]);
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.consultar');
        $consultaBase = $inventario->productosParaConsulta($alcance, [], true, true);
        $consultaFiltrada = $inventario->productosParaConsulta($alcance, [
            'buscar' => $filtros['buscar'] ?? null,
            'categoria' => isset($filtros['categoria']) ? (int) $filtros['categoria'] : null,
            'estado' => $filtros['estado'] ?? null,
        ], true, true)->when($filtros['marca'] ?? null, static fn (Builder $consulta, string $marca): Builder => $consulta->where('productos.marca', $marca));
        $paginacion = (clone $consultaFiltrada)->with([
            'category:id,nombre',
            'variants:id,producto_id,codigo_sku',
        ])->orderBy('productos.nombre')->orderBy('productos.id')->paginate(20)->withQueryString();
        $empresa = Empresa::query()->findOrFail($alcance['empresa_id']);
        $productos = $paginacion->getCollection()->map(static function (Producto $producto) use ($empresa): array {
            $precioBase = (float) $producto->precio;
            $precioMinorista = (float) ($producto->precio_oferta ?? $producto->precio);
            $existenciasDisponibles = max(0, (float) $producto->existencias_alcance - (float) $producto->existencias_reservadas);

            return [
                'id' => (int) $producto->getKey(),
                'nombre' => $producto->nombre,
                'sku' => $producto->variants->pluck('codigo_sku')->filter()->implode(', ') ?: 'Sin SKU registrado',
                'categoria' => $producto->category?->nombre ?? 'Sin categoría',
                'marca' => $producto->marca,
                'base' => $empresa->moneda_base.' '.number_format($precioBase, 2, '.', ','),
                'minorista' => $empresa->moneda_base.' '.number_format($precioMinorista, 2, '.', ','),
                'mayorista' => 'No registrado en MariaDB',
                'stock' => rtrim(rtrim(number_format($existenciasDisponibles, 3, '.', ''), '0'), '.'),
                'estado' => $producto->activo ? 'Activo' : 'Inactivo',
                'icono' => 'bi-box-seam',
                'activo' => (bool) $producto->activo,
                'en_oferta' => $producto->precio_oferta !== null && (float) $producto->precio_oferta < $precioBase,
            ];
        })->all();
        $productosFiltrados = (clone $consultaBase)->count();
        $promedioBase = (float) (clone $consultaBase)->avg('productos.precio');
        $promedioVigente = (float) (clone $consultaBase)->selectRaw('AVG(COALESCE(productos.precio_oferta, productos.precio)) AS promedio')->value('promedio');
        $ofertas = (clone $consultaBase)->whereNotNull('productos.precio_oferta')
            ->whereColumn('productos.precio_oferta', '<', 'productos.precio')->count();

        return view('paneles.administrador.ecommerce-marketing.precios.listar', [
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
        ]);
    }

    public function index(Request $request, ServicioInventario $inventario): View
    {
        $filters = $this->filters($request);
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.consultar');
        $resumen = $inventario->resumenProductos($alcance);
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();
        $productosEmpresa = Producto::query()->where('empresa_id', $alcance['empresa_id']);
        $productosAgregadosEsteMes = (clone $productosEmpresa)
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $productosAgregadosMesAnterior = (clone $productosEmpresa)
            ->where('activo', true)
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();
        $categoryCount = Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->count();
        $paginaProductos = $this->productQuery($filters, $alcance, $inventario)
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
        $idsProductos = $paginaProductos->getCollection()
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        $saldosPorVariante = $inventario->saldosPorVariante($alcance, $idsProductos);

        foreach ($paginaProductos->getCollection() as $producto) {
            foreach ($producto->variants as $variante) {
                $saldo = $saldosPorVariante->get($variante->id);
                $variante->setAttribute('existencias_alcance', (float) ($saldo->cantidad_fisica ?? 0));
                $variante->setAttribute('existencias_reservadas_alcance', (float) ($saldo->cantidad_reservada ?? 0));
            }
        }

        return view('gestion.productos.listar', [
            'products' => $paginaProductos,
            'categories' => Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Producto::query()
                ->where('empresa_id', $alcance['empresa_id'])
                ->where('activo', true)
                ->whereNotNull('marca')
                ->where('marca', '<>', '')
                ->distinct()
                ->orderBy('marca')
                ->pluck('marca'),
            'suppliers' => Proveedor::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $filters,
            'activeProducts' => $resumen['productos_activos'],
            'productsTrendPercent' => $this->trendPercent($productosAgregadosEsteMes, $productosAgregadosMesAnterior),
            'productsWithStock' => $resumen['productos_con_existencias'],
            'outOfStock' => $resumen['productos_sin_existencias'],
            'categoryCount' => $categoryCount,
            'inventoryValue' => $resumen['valor_inventario'],
            'offerCount' => $resumen['productos_en_oferta'],
        ]);
    }

    public function export(Request $request, string $format, ServicioInventario $inventario, ?ExportadorTabular $exporter = null): Response|StreamedResponse
    {
        $filters = $this->filters($request);
        $alcance = $inventario->alcanceConsulta($request->user(), 'gestion.productos.exportar');
        $products = $this->productQuery($filters, $alcance, $inventario)->orderBy('productos.id');

        return match ($format) {
            'csv' => $this->downloadCsv($products),
            'excel' => $this->downloadCorporateReport($products->get(), 'excel', $filters, $alcance, $request, $exporter ?? new ExportadorTabular),
            'pdf' => $this->downloadCorporateReport($products->get(), 'pdf', $filters, $alcance, $request, $exporter ?? new ExportadorTabular),
            default => abort(404),
        };
    }

    public function store(
        GuardarProductoRequest $request,
        RegistradorAuditoria $audit,
        ServicioInventario $inventory,
        ServicioAutorizacion $authorization,
        ServicioCatalogo $catalogo,
    ): RedirectResponse {
        $validated = $request->validated();
        $supplierId = $validated['proveedor_id'] ?? null;
        $data = $this->attributes($validated);
        $rutaImagenGuardada = null;

        if ($request->hasFile('imagen')) {
            $rutaImagenGuardada = $request->file('imagen')->store('productos', 'public');
            abort_if($rutaImagenGuardada === false, 500, 'No se pudo guardar la imagen del producto.');
            $data['url_imagen'] = 'storage/'.$rutaImagenGuardada;
        }

        try {
            DB::transaction(function () use ($data, $supplierId, $request, $audit, $inventory, $authorization, $catalogo): void {
                $product = Producto::create($data + [
                    'activo' => true,
                    'creado_en' => now(),
                    ...$authorization->operationScopeAttributes($request->user(), 'productos'),
                ]);
                $product->suppliers()->sync($supplierId ? [$supplierId] : []);
                $inventory->registerNewProduct($product, $request->user());
                $catalogo->sincronizarImagenPortada($product);
                $audit->record($request->user()->id, 'product.created', 'productos', $product->id, null, $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'existencias', 'url_imagen']), $request->ip());
            }, 3);
        } catch (Throwable $excepcion) {
            if (is_string($rutaImagenGuardada)) {
                Storage::disk('public')->delete($rutaImagenGuardada);
            }

            throw $excepcion;
        }

        return back()
            ->with('status', 'Producto guardado. Registra las existencias desde el módulo Inventario.')
            ->with('open_product_modal', $request->boolean('continue'));
    }

    public function update(ActualizarProductoRequest $request, Producto $product, RegistradorAuditoria $audit, ServicioCatalogo $catalogo): RedirectResponse
    {
        $this->authorize('update', $product);
        $validated = $request->validated();
        $before = $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'precio_oferta', 'url_imagen']);
        $data = $this->attributes($validated);
        $rutaImagenGuardada = null;

        if ($request->hasFile('imagen')) {
            $rutaImagenGuardada = $request->file('imagen')->store('productos', 'public');
            abort_if($rutaImagenGuardada === false, 500, 'No se pudo guardar la imagen del producto.');
            $data['url_imagen'] = 'storage/'.$rutaImagenGuardada;
        }

        try {
            DB::transaction(function () use ($product, $data, $validated, $request, $audit, $before, $catalogo): void {
                $product->update($data);

                if (array_key_exists('proveedor_id', $validated)) {
                    $product->suppliers()->sync($validated['proveedor_id'] ? [$validated['proveedor_id']] : []);
                }

                $catalogo->sincronizarImagenPortada($product);
                $audit->record($request->user()->id, 'product.updated', 'productos', $product->id, $before, $product->only(array_keys($before)), $request->ip());
            }, 3);
        } catch (Throwable $excepcion) {
            if (is_string($rutaImagenGuardada)) {
                Storage::disk('public')->delete($rutaImagenGuardada);
            }

            throw $excepcion;
        }

        return back()->with('status', 'Producto actualizado.');
    }

    public function deactivate(Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->update(['activo' => false]);
        $audit->record(request()->user()->id, 'product.deactivated', 'productos', $product->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Producto desactivado; su historial comercial se conserva.');
    }

    public function registrarVariante(
        GuardarVarianteProductoRequest $request,
        Producto $product,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $variantes->registrar($product, $request->validated(), $usuario, $request->ip());

        return back()->with('status', 'Variante registrada.')->with('open_variant_manager', $product->id);
    }

    public function actualizarVariante(
        GuardarVarianteProductoRequest $request,
        Producto $product,
        VarianteProducto $variante,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $variantes->actualizar($product, $variante, $request->validated(), $usuario, $request->ip());

        return back()->with('status', 'Variante actualizada.')->with('open_variant_manager', $product->id);
    }

    public function desactivarVariante(
        Request $request,
        Producto $product,
        VarianteProducto $variante,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $variantes->desactivar($product, $variante, $usuario, $request->ip());

        return back()->with('status', 'Variante desactivada.')->with('open_variant_manager', $product->id);
    }

    public function registrarImagenVariante(
        GuardarImagenVarianteRequest $request,
        Producto $product,
        VarianteProducto $variante,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $archivo = $request->file('imagen');
        abort_unless($archivo instanceof UploadedFile, 422, 'Selecciona una imagen válida.');

        $variantes->registrarImagen(
            $product,
            $variante,
            $archivo,
            $request->boolean('imagen_principal'),
            $usuario,
            $request->ip(),
        );

        return back()
            ->with('status', 'Imagen de variante registrada.')
            ->with('open_variant_manager', $product->id)
            ->with('abrir_editor_variante', $variante->id);
    }

    public function eliminarImagenVariante(
        Request $request,
        Producto $product,
        VarianteProducto $variante,
        int $imagen,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
        ServicioVariantesProducto $variantes,
    ): RedirectResponse {
        $usuario = $this->usuarioConAccesoProducto($request, $product, $inventario, $autorizacion);
        $variantes->eliminarImagen($product, $variante, $imagen, $usuario, $request->ip());

        return back()
            ->with('status', 'Imagen de variante eliminada.')
            ->with('open_variant_manager', $product->id)
            ->with('abrir_editor_variante', $variante->id);
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        $category = Categoria::findOrFail($data['categoria_id']);
        $data['categoria'] = $category->nombre;
        unset($data['proveedor_id'], $data['imagen'], $data['continue']);

        return $data;
    }

    private function usuarioConAccesoProducto(
        Request $request,
        Producto $product,
        ServicioInventario $inventario,
        ServicioAutorizacion $autorizacion,
    ): User {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);

        $permiso = $autorizacion->permissionForRoute($request);
        abort_unless($permiso !== null, 403);
        $alcance = $inventario->alcanceConsulta($usuario, $permiso);
        abort_unless((int) $product->empresa_id === $alcance['empresa_id'], 404);

        return $usuario;
    }

    /**
     * @param  array{buscar:?string,categoria:?int,stock:?string,estado:?string,oferta:bool,por_pagina:int}  $filters
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Builder<Producto>
     */
    private function productQuery(array $filters, array $alcance, ServicioInventario $inventario): Builder
    {
        return $inventario->productosParaConsulta($alcance, $filters, true, true);
    }

    /**
     * @return array{buscar:?string,categoria:?int,stock:?string,estado:?string,oferta:bool,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $stock = trim((string) $request->input('stock', ''));
        $status = trim((string) $request->input('estado', ''));
        $search = trim((string) $request->input('buscar', ''));
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

    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  Builder<Producto>  $query
     */
    private function downloadCsv(Builder $query): StreamedResponse
    {
        $products = $query->cursor();

        return response()->streamDownload(function () use ($products): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'producto', 'marca', 'categoria', 'precio', 'stock', 'estado']);

            foreach ($products as $product) {
                fputcsv($output, $this->exportRow($product));
            }

            fclose($output);
        }, 'catalogo-productos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Collection<int, Producto>  $products
     * @param  array{buscar:?string,categoria:?int,stock:?string,estado:?string,oferta:bool,por_pagina:int}  $filters
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     */
    private function downloadCorporateReport(Collection $products, string $format, array $filters, array $alcance, Request $request, ExportadorTabular $exporter): Response
    {
        $rows = $products->map(fn (Producto $product): array => $this->exportRow($product))->all();
        $stockTotal = array_sum(array_map(static fn (array $row): int => (int) ($row[5] ?? 0), $rows));
        $outOfStockCount = count(array_filter($rows, static fn (array $row): bool => (int) ($row[5] ?? 0) === 0));
        $stockLabel = match ($filters['stock']) {
            'sin_stock' => 'Sin stock',
            'con_stock' => 'Con existencias',
            default => 'Todos',
        };
        $statusLabel = match ($filters['estado']) {
            'activo' => 'Activos',
            'inactivo' => 'Inactivos',
            default => 'Todos',
        };
        $columns = ['ID', 'Producto', 'Marca', 'Categoría', 'Precio de venta PEN', 'Stock', 'Estado'];
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
    private function downloadExcel(Collection $products): Response
    {
        $rows = $products->map(fn (Producto $product): string => '<tr>'.implode('', array_map(
            fn (string|int|float $value): string => '<td>'.htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</td>',
            $this->exportRow($product),
        )).'</tr>')->implode('');
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif}table{border-collapse:collapse}th,td{border:1px solid #d9e3f0;padding:7px}th{background:#eaf2ff;color:#10234c;text-align:left}</style></head><body><h1>Catálogo de productos</h1><table><thead><tr><th>ID</th><th>Producto</th><th>Marca</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th></tr></thead><tbody>'.$rows.'</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="catalogo-productos.xls"',
        ]);
    }

    /**
     * @param  Collection<int, Producto>  $products
     */
    private function downloadPdf(Collection $products): Response
    {
        $header = [
            'CATALOGO DE PRODUCTOS',
            'Generado: '.now()->format('d/m/Y H:i'),
            '',
            'ID | Producto | Marca | Categoria | Precio | Stock | Estado',
            str_repeat('-', 108),
        ];
        $productLines = $products->map(function (Producto $product): string {
            $row = $this->exportRow($product);

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
        $chunks = array_chunk($productLines, 31);
        $pages = [];

        foreach ($chunks ?: [[]] as $index => $chunk) {
            $pages[] = array_merge(
                $index === 0 ? $header : ['CATALOGO DE PRODUCTOS (continuacion)', '', $header[3], $header[4]],
                $chunk,
            );
        }

        return response($this->buildPdfDocument($pages), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="catalogo-productos.pdf"',
        ]);
    }

    /**
     * @return array<int, string|int|float>
     */
    private function exportRow(Producto $product): array
    {
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
    private function buildPdfDocument(array $pages): string
    {
        $objects = [];
        $pageObjectIds = [];
        $contentObjectIds = [];
        $nextObjectId = 4;

        foreach ($pages as $page) {
            $pageObjectIds[] = $nextObjectId++;
            $contentObjectIds[] = $nextObjectId++;
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id.' 0 R', $pageObjectIds));
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageObjectIds).' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        foreach ($pages as $index => $page) {
            $content = '';
            $y = 560;

            foreach ($page as $lineIndex => $line) {
                $fontSize = $lineIndex === 0 ? 14 : 8;
                $content .= "BT\n/F1 {$fontSize} Tf\n30 {$y} Td\n(".$this->escapePdfText($line).") Tj\nET\n";
                $y -= 15;
            }

            $contentObjectId = $contentObjectIds[$index];
            $pageObjectId = $pageObjectIds[$index];
            $objects[$contentObjectId] = '<< /Length '.strlen($content)." >>\nstream\n{$content}endstream";
            $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObjectId.' 0 R >>';
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf('%010d 00000 n \n', $offsets[$id]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    private function escapePdfText(string $value): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded === false ? $value : $encoded);
    }
}
