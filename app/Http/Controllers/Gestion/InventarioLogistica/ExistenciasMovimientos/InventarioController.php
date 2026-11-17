<?php

namespace App\Http\Controllers\Gestion\InventarioLogistica\ExistenciasMovimientos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\InventarioLogistica\ExistenciasMovimientos\AjustarInventarioRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\MovimientoInventario;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use App\Servicios\Gestion\InventarioLogistica\ServicioPresentacionInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase InventarioController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class InventarioController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioInventario $inventoryService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request): View
    {
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Obtiene en $alcance la informacion de la solicitud o del usuario actual.
        $alcance = $this->inventoryService->alcanceConsulta($request->user());
        $movementQuery = fn (): Builder => $this->authorization->scopeRecordsFor(
            MovimientoInventario::query(),
            $request->user(),
            'gestion.inventario.consultar',
        );
        // Guarda en $products el resultado necesario para continuar esta operacion.
        $products = $this->inventoryService->productosParaConsulta($alcance, $filters, true)
            ->with(['category', 'suppliers:id,nombre', 'variants:id,producto_id,codigo_sku,nombre_color,estado'])
            ->orderBy('nombre')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        // Guarda en $resumen el resultado necesario para continuar esta operacion.
        $resumen = $this->inventoryService->resumenConsulta($alcance);
        // Guarda en $movementsToday el resultado necesario para continuar esta operacion.
        $movementsToday = $movementQuery()->whereDate('creado_en', today())->count();
        // Guarda en $movementsYesterday el resultado necesario para continuar esta operacion.
        $movementsYesterday = $movementQuery()->whereDate('creado_en', today()->subDay())->count();
        // Obtiene en $latestMovements la informacion de la solicitud o del usuario actual.
        $latestMovements = $this->latestMovements($products->getCollection(), $request->user());
        // Guarda en $verHistorialMovimientos el resultado necesario para continuar esta operacion.
        $verHistorialMovimientos = $request->routeIs('management.inventory.movements');
        // Guarda en $filtrosMovimientos el resultado necesario para continuar esta operacion.
        $filtrosMovimientos = $verHistorialMovimientos ? $this->filtrosMovimientos($request) : [];
        // Guarda en $consultaMovimientos el resultado necesario para continuar esta operacion.
        $consultaMovimientos = $movementQuery()
            ->with([
                'product:id,nombre,marca,etiqueta',
                'user:id,nombre',
                'almacen:id,nombre',
                'variante:id,producto_id,codigo_sku,nombre_color',
            ])
            ->orderByDesc('creado_en')
            ->orderByDesc('id');

        // Comprueba la condicion $verHistorialMovimientos antes de ejecutar este bloque.
        if ($verHistorialMovimientos) {
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->aplicarFiltrosMovimientos($consultaMovimientos, $filtrosMovimientos);
        }

        // Guarda en $movimientosRecientes el resultado necesario para continuar esta operacion.
        $movimientosRecientes = $verHistorialMovimientos
            ? $consultaMovimientos->paginate(25)->withQueryString()
            : $consultaMovimientos->limit(5)->get();

        $productosSinExistencias = $this->inventoryService->productosParaConsulta($alcance, ['estado' => 'sin_existencias'], true)
            ->with(['suppliers:id,nombre'])
            ->orderBy('existencias_alcance')
            ->orderBy('nombre')
            ->limit(4)
            ->get();
        // Guarda en $presentacion el resultado necesario para continuar esta operacion.
        $presentacion = app(ServicioPresentacionInventario::class);
        // Guarda en $todayLabel el resultado necesario para continuar esta operacion.
        $todayLabel = $presentacion->etiquetaHoy();
        $productPresentation = $products->getCollection()->mapWithKeys(fn (Producto $product): array => [$product->id => $presentacion->producto($product, $latestMovements->get($product->id))])->all();
        $alertPresentation = $productosSinExistencias->mapWithKeys(fn (Producto $product): array => [$product->id => $presentacion->estadoStock((int) $product->existencias_alcance)])->all();
        // Guarda en $recentMovementCollection el resultado necesario para continuar esta operacion.
        $recentMovementCollection = $verHistorialMovimientos ? $movimientosRecientes->getCollection() : $movimientosRecientes;
        $movementPresentation = $recentMovementCollection->mapWithKeys(fn (MovimientoInventario $movement): array => [$movement->id => $presentacion->filaMovimiento($movement)])->all();

        // Devuelve la vista 'gestion.inventario-logistica.existencias-movimientos.listar-existencias' con los datos preparados por este controlador.
        return view('gestion.inventario-logistica.existencias-movimientos.listar-existencias', [
            'products' => $products,
            'categories' => Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'suppliers' => Proveedor::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'almacenes' => $this->inventoryService->almacenesParaConsulta($alcance),
            'movementProducts' => $this->inventoryService->productosParaConsulta($alcance, [], true)
                ->with('variants:id,producto_id,codigo_sku,nombre_color,estado')
                ->orderBy('nombre')
                ->get(),
            'latestMovements' => $latestMovements,
            'productosSinExistencias' => $productosSinExistencias,
            'productPresentation' => $productPresentation,
            'alertPresentation' => $alertPresentation,
            'movementPresentation' => $movementPresentation,
            'inventoryQueryParameters' => $request->except(['page', 'por_pagina']),
            'todayLabel' => $todayLabel,
            'perPageOptions' => [7, 8, 10, 20, 50],

            'recentMovements' => $movimientosRecientes,
            'verHistorialMovimientos' => $verHistorialMovimientos,
            'filtrosMovimientos' => $filtrosMovimientos,
            'tiposMovimiento' => $verHistorialMovimientos
                ? $movementQuery()->distinct()->orderBy('tipo_movimiento')->pluck('tipo_movimiento')
                : collect(),
            'almacenesMovimiento' => $verHistorialMovimientos ? $this->inventoryService->almacenesParaConsulta($alcance) : collect(),
            'hayFiltrosMovimientos' => collect($filtrosMovimientos)->contains(fn (mixed $valor): bool => $valor !== null && $valor !== ''),
            'filters' => $filters,
            'activeProducts' => $resumen['productos'],
            'sinExistencias' => $resumen['sin_existencias'],
            'movementsToday' => $movementsToday,
            'movementsTrend' => $this->porcentajeTendencia($movementsToday, $movementsYesterday),
            'inventoryValue' => $resumen['valor_catalogo'],
        ]);
    }

    /**
     * Ejecuta la operación adjust definida por la clase InventarioController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function adjust(AjustarInventarioRequest $request, Producto $product): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Guarda en $movimiento el resultado necesario para continuar esta operacion.
        $movimiento = isset($data['almacen_id'])
            ? $this->inventoryService->registrarMovimientoEnAlmacen(
                $product,
                isset($data['variante_id']) ? (int) $data['variante_id'] : null,
                (int) $data['almacen_id'],
                $data['tipo_movimiento'],
                (int) $data['cantidad'],
                $data['notas'],
                $request->user(),
            )
            : $this->inventoryService->ajustar($product, $data['tipo_movimiento'], (int) $data['cantidad'], $data['notas'], $request->user());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', $movimiento === null
            ? 'El conteo coincide con las existencias registradas; no se generó un movimiento.'
            : 'Movimiento de inventario registrado.');
    }

    /**
     * Ejecuta la operación adjustLegacy definida por la clase InventarioController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function adjustLegacy(AjustarInventarioRequest $request): RedirectResponse
    {
        // Guarda en $product el resultado necesario para continuar esta operacion.
        $product = Producto::findOrFail((int) $request->input('producto_id'));

        return $this->adjust($request, $product);
    }

    /**
     * @return array{buscar:?string,categoria:?int,estado:?string,proveedor:?int,por_pagina:int}
     */
    private function filtros(Request $request): array
    {
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim($request->string('buscar')->toString());
        // Guarda en $status el resultado necesario para continuar esta operacion.
        $status = trim($request->string('estado')->toString());
        // Guarda en $perPage el resultado necesario para continuar esta operacion.
        $perPage = $request->integer('por_pagina', 7);

        return [
            'buscar' => $search !== '' ? $search : null,
            'categoria' => $request->integer('categoria') ?: null,
            'estado' => in_array($status, ['con_existencias', 'sin_existencias'], true) ? $status : null,
            'proveedor' => $request->integer('proveedor') ?: null,
            'por_pagina' => in_array($perPage, [7, 8, 10, 20, 50], true) ? $perPage : 7,
        ];
    }

    /** @return array{buscar:?string,tipo:?string,almacen:?int} */
    private function filtrosMovimientos(Request $request): array
    {
        // Guarda en $datos el resultado necesario para continuar esta operacion.
        $datos = $request->validate([
            'mov_buscar' => ['nullable', 'string', 'max:100'],
            'mov_tipo' => ['nullable', 'string', 'max:30'],
            'mov_almacen' => ['nullable', 'integer', 'min:1'],
        ]);

        return [
            'buscar' => filled($datos['mov_buscar'] ?? null) ? trim((string) $datos['mov_buscar']) : null,
            'tipo' => filled($datos['mov_tipo'] ?? null) ? (string) $datos['mov_tipo'] : null,
            'almacen' => filled($datos['mov_almacen'] ?? null) ? (int) $datos['mov_almacen'] : null,
        ];
    }

    /**
     * @param  Builder<MovimientoInventario>  $consulta
     * @param  array{buscar:?string,tipo:?string,almacen:?int}  $filtros
     */
    private function aplicarFiltrosMovimientos(Builder $consulta, array $filtros): void
    {
        // Comprueba la condicion $filtros['tipo'] !== null antes de ejecutar este bloque.
        if ($filtros['tipo'] !== null) {
            $consulta->where('tipo_movimiento', $filtros['tipo']);
        }

        // Comprueba la condicion $filtros['almacen'] !== null antes de ejecutar este bloque.
        if ($filtros['almacen'] !== null) {
            $consulta->where('almacen_id', $filtros['almacen']);
        }

        // Comprueba la condicion $filtros['buscar'] === null antes de ejecutar este bloque.
        if ($filtros['buscar'] === null) {
            return;
        }

        // Guarda en $coincidencia el resultado necesario para continuar esta operacion.
        $coincidencia = '%'.$filtros['buscar'].'%';
        $consulta->where(static function (Builder $consulta) use ($coincidencia): void {
            $consulta->where('notas', 'like', $coincidencia)
                ->orWhere('documento_tipo', 'like', $coincidencia)
                ->orWhere('documento_id', 'like', $coincidencia)
                ->orWhereHas('product', static function (Builder $consultaProducto) use ($coincidencia): void {
                    $consultaProducto->where('nombre', 'like', $coincidencia)
                        ->orWhere('marca', 'like', $coincidencia)
                        ->orWhere('etiqueta', 'like', $coincidencia);
                })
                ->orWhereHas('variante', static function (Builder $consultaVariante) use ($coincidencia): void {
                    $consultaVariante->where('codigo_sku', 'like', $coincidencia)
                        ->orWhere('nombre_color', 'like', $coincidencia);
                });
        });
    }

    /**
     * @param  Collection<int, Producto>  $products
     * @return Collection<int, MovimientoInventario>
     */
    private function latestMovements(Collection $products, User $user): Collection
    {
        // Comprueba la condicion $products->isEmpty() antes de ejecutar este bloque.
        if ($products->isEmpty()) {
            return collect();
        }

        return $this->authorization->scopeRecordsFor(
            MovimientoInventario::query(),
            $user,
            'gestion.inventario.consultar',
        )
            ->with('user:id,nombre')
            ->whereIn('producto_id', $products->modelKeys())
            ->latest('creado_en')
            ->get()
            ->groupBy('producto_id')
            ->map(static fn (Collection $movements): ?MovimientoInventario => $movements->first());
    }

    /**
     * Ejecuta la operación trendPercent definida por la clase InventarioController.
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
}
