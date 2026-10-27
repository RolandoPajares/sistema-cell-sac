<?php

namespace App\Http\Controllers\Gestion\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\AjustarInventarioRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\MovimientoInventario;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function __construct(private readonly ServicioInventario $inventoryService) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $products = $this->productQuery($filters)
            ->with(['category', 'suppliers:id,nombre'])
            ->orderBy('nombre')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $activeProducts = Producto::query()->where('activo', true)->count();
        $lowStock = Producto::query()->where('activo', true)->where('existencias', '<=', 10)->count();
        $movementsToday = MovimientoInventario::query()->whereDate('creado_en', today())->count();
        $movementsYesterday = MovimientoInventario::query()->whereDate('creado_en', today()->subDay())->count();
        $inventoryValue = (float) Producto::query()
            ->where('activo', true)
            ->selectRaw('COALESCE(SUM(existencias * precio), 0) as inventory_value')
            ->value('inventory_value');
        $latestMovements = $this->latestMovements($products->getCollection());

        return view('gestion.inventario.listar', [
            'products' => $products,
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'suppliers' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'movementProducts' => Producto::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'marca', 'existencias']),
            'latestMovements' => $latestMovements,
            'lowStockProducts' => Producto::query()
                ->where('activo', true)
                ->where('existencias', '<=', 10)
                ->with(['suppliers:id,nombre'])
                ->orderBy('existencias')
                ->orderBy('nombre')
                ->limit(4)
                ->get(),
            'recentMovements' => MovimientoInventario::query()
                ->with(['product:id,nombre,marca,etiqueta', 'user:id,nombre'])
                ->latest('creado_en')
                ->limit(5)
                ->get(),
            'filters' => $filters,
            'activeProducts' => $activeProducts,
            'lowStock' => $lowStock,
            'movementsToday' => $movementsToday,
            'movementsTrend' => $this->trendPercent($movementsToday, $movementsYesterday),
            'inventoryValue' => $inventoryValue,
        ]);
    }

    public function adjust(AjustarInventarioRequest $request, Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $before = (int) $product->existencias;
        $this->inventoryService->adjust($product, $data['tipo_movimiento'], (int) $data['cantidad'], $data['notas'], $request->user());
        $audit->record($request->user()->id, 'inventory.adjusted', 'productos', $product->id, ['existencias' => $before], ['existencias' => $product->fresh()->existencias], $request->ip());

        return back()->with('status', 'Movimiento de inventario registrado.');
    }

    public function adjustLegacy(AjustarInventarioRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $product = Producto::findOrFail((int) $request->input('producto_id'));

        return $this->adjust($request, $product, $audit);
    }

    /**
     * @param  array{buscar:?string,categoria:?int,estado:?string,proveedor:?int,por_pagina:int}  $filters
     * @return Builder<Producto>
     */
    private function productQuery(array $filters): Builder
    {
        return Producto::query()
            ->where('activo', true)
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('marca', 'like', '%'.$search.'%')
                        ->orWhere('etiqueta', 'like', '%'.$search.'%')
                        ->orWhere('categoria', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['categoria'], fn (Builder $query, int $categoryId): Builder => $query->where('categoria_id', $categoryId))
            ->when($filters['proveedor'], fn (Builder $query, int $supplierId): Builder => $query->whereHas('suppliers', fn (Builder $supplierQuery): Builder => $supplierQuery->whereKey($supplierId)))
            ->when($filters['estado'] === 'optimo', fn (Builder $query): Builder => $query->where('existencias', '>', 10))
            ->when($filters['estado'] === 'bajo', fn (Builder $query): Builder => $query->whereBetween('existencias', [6, 10]))
            ->when($filters['estado'] === 'critico', fn (Builder $query): Builder => $query->where('existencias', '<=', 5));
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
            'estado' => in_array($status, ['optimo', 'bajo', 'critico'], true) ? $status : null,
            'proveedor' => $request->integer('proveedor') ?: null,
            'por_pagina' => in_array($perPage, [7, 8, 10, 20, 50], true) ? $perPage : 7,
        ];
    }

    /**
     * @param  Collection<int, Producto>  $products
     * @return Collection<int, MovimientoInventario>
     */
    private function latestMovements(Collection $products): Collection
    {
        if ($products->isEmpty()) {
            return collect();
        }

        return MovimientoInventario::query()
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
