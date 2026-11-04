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

class InventarioController extends Controller
{
    public function __construct(
        private readonly ServicioInventario $inventoryService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

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

        return view('gestion.inventario.listar', [
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
            'recentMovements' => $movementQuery()
                ->with(['product:id,nombre,marca,etiqueta', 'user:id,nombre'])
                ->latest('creado_en')
                ->limit(5)
                ->get(),
            'filters' => $filters,
            'activeProducts' => $resumen['productos'],
            'sinExistencias' => $resumen['sin_existencias'],
            'movementsToday' => $movementsToday,
            'movementsTrend' => $this->trendPercent($movementsToday, $movementsYesterday),
            'inventoryValue' => $resumen['valor_catalogo'],
        ]);
    }

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

    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
