<?php

namespace App\Http\Controllers\Gestion\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\AjustarInventarioRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\MovimientoInventario;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
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
        $filters = $this->filters($request);
        $alcance = $this->inventoryService->alcanceConsulta($request->user());
        $movementQuery = fn (): Builder => $this->authorization->scopeRecordsFor(
            MovimientoInventario::query(),
            $request->user(),
            'gestion.inventario.consultar',
        );
        $products = $this->inventoryService->productosParaConsulta($alcance, $filters, true)
            ->with(['category', 'suppliers:id,nombre', 'variants:id,producto_id,codigo_sku,nombre_color,estado'])
            ->orderBy('nombre')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $resumen = $this->inventoryService->resumenConsulta($alcance);
        $movementsToday = $movementQuery()->whereDate('creado_en', today())->count();
        $movementsYesterday = $movementQuery()->whereDate('creado_en', today()->subDay())->count();
        $latestMovements = $this->latestMovements($products->getCollection(), $request->user());
        $verHistorialMovimientos = $request->routeIs('management.inventory.movements');
        $filtrosMovimientos = $verHistorialMovimientos ? $this->filtrosMovimientos($request) : [];
        $consultaMovimientos = $movementQuery()
            ->with([
                'product:id,nombre,marca,etiqueta',
                'user:id,nombre',
                'almacen:id,nombre',
                'variante:id,producto_id,codigo_sku,nombre_color',
            ])
            ->orderByDesc('creado_en')
            ->orderByDesc('id');

        if ($verHistorialMovimientos) {
            $this->aplicarFiltrosMovimientos($consultaMovimientos, $filtrosMovimientos);
        }

        $movimientosRecientes = $verHistorialMovimientos
            ? $consultaMovimientos->paginate(25)->withQueryString()
            : $consultaMovimientos->limit(5)->get();

        return view('gestion.inventario.existencias.listar', [
            'products' => $products,
            'categories' => Categoria::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'suppliers' => Proveedor::query()->where('empresa_id', $alcance['empresa_id'])->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'almacenes' => $this->inventoryService->almacenesParaConsulta($alcance),
            'movementProducts' => $this->inventoryService->productosParaConsulta($alcance, [], true)
                ->with('variants:id,producto_id,codigo_sku,nombre_color,estado')
                ->orderBy('nombre')
                ->get(),
            'latestMovements' => $latestMovements,
            'productosSinExistencias' => $this->inventoryService->productosParaConsulta($alcance, ['estado' => 'sin_existencias'], true)
                ->with(['suppliers:id,nombre'])
                ->orderBy('existencias_alcance')
                ->orderBy('nombre')
                ->limit(4)
                ->get(),
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
            'movementsTrend' => $this->trendPercent($movementsToday, $movementsYesterday),
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
        $data = $request->validated();
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
            : $this->inventoryService->adjust($product, $data['tipo_movimiento'], (int) $data['cantidad'], $data['notas'], $request->user());

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
        $product = Producto::findOrFail((int) $request->input('producto_id'));

        return $this->adjust($request, $product);
    }

    /**
     * @return array{buscar:?string,categoria:?int,estado:?string,proveedor:?int,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $search = trim($request->string('buscar')->toString());
        $status = trim($request->string('estado')->toString());
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
        if ($filtros['tipo'] !== null) {
            $consulta->where('tipo_movimiento', $filtros['tipo']);
        }

        if ($filtros['almacen'] !== null) {
            $consulta->where('almacen_id', $filtros['almacen']);
        }

        if ($filtros['buscar'] === null) {
            return;
        }

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
    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
