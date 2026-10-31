<?php

namespace App\Http\Controllers\Gestion\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarProveedorRequest;
use App\Http\Requests\GuardarProveedorRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $suppliers = $this->supplierQuery($filters)
            ->with([
                'products' => fn ($query) => $query
                    ->select(['productos.id', 'productos.nombre', 'productos.categoria_id'])
                    ->with('category:id,nombre')
                    ->orderBy('nombre'),
            ])
            ->withCount(['products', 'purchases'])
            ->withSum('purchases as purchases_total', 'total')
            ->withMax('purchases as latest_purchase_date', 'fecha_compra')
            ->orderBy('nombre')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $selectedSupplierId = $request->integer('proveedor') ?: $suppliers->first()?->id;
        $selectedSupplier = $selectedSupplierId ? $this->supplierDetails((int) $selectedSupplierId) : null;
        $purchaseStatusCounts = $selectedSupplier
            ? Compra::query()
                ->where('proveedor_id', $selectedSupplier->id)
                ->select('estado')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('estado')
                ->orderByDesc('total')
                ->get()
            : collect();

        return view('gestion.proveedores.listar', [
            'suppliers' => $suppliers,
            'selectedSupplier' => $selectedSupplier,
            'purchaseStatusCounts' => $purchaseStatusCounts,
            'filters' => $filters,
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'cities' => Proveedor::query()->whereNotNull('ciudad')->where('ciudad', '<>', '')->distinct()->orderBy('ciudad')->pluck('ciudad'),
            'totalSuppliers' => Proveedor::query()->count(),
            'activeSuppliers' => Proveedor::query()->where('activo', true)->count(),
            'linkedOrders' => Compra::query()->where('activo', true)->count(),
            'purchasedTotal' => (float) Compra::query()->where('activo', true)->sum('total'),
            'lowStockProducts' => Producto::query()
                ->where('activo', true)
                ->where('existencias', '<=', 10)
                ->whereHas('suppliers')
                ->with('suppliers:id,nombre')
                ->orderBy('existencias')
                ->orderBy('nombre')
                ->limit(4)
                ->get(['id', 'nombre', 'existencias']),
            'recentActivity' => $this->recentActivity(),
        ]);
    }

    public function store(GuardarProveedorRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $supplier = Proveedor::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'supplier.created', 'proveedores', $supplier->id, null, $supplier->only(['nombre', 'ruc', 'correo', 'ciudad']), $request->ip());

        return back()->with('status', 'Proveedor registrado.');
    }

    public function update(ActualizarProveedorRequest $request, Proveedor $supplier, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $supplier->only(['nombre', 'ruc', 'correo', 'telefono', 'ciudad', 'activo']);
        $supplier->update($request->validated());
        $audit->record($request->user()->id, 'supplier.updated', 'proveedores', $supplier->id, $before, $supplier->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Proveedor actualizado.');
    }

    public function deactivate(Proveedor $supplier, RegistradorAuditoria $audit): RedirectResponse
    {
        $supplier->update(['activo' => false]);
        $audit->record(request()->user()->id, 'supplier.deactivated', 'proveedores', $supplier->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Proveedor desactivado.');
    }

    /**
     * @param  array{buscar:?string,categoria:?int,estado:?string,ciudad:?string,por_pagina:int}  $filters
     * @return Builder<Proveedor>
     */
    private function supplierQuery(array $filters): Builder
    {
        return Proveedor::query()
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('ruc', 'like', '%'.$search.'%')
                        ->orWhere('correo', 'like', '%'.$search.'%')
                        ->orWhere('telefono', 'like', '%'.$search.'%')
                        ->orWhere('ciudad', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['categoria'], fn (Builder $query, int $categoryId): Builder => $query->whereHas('products', fn (Builder $productQuery): Builder => $productQuery->where('categoria_id', $categoryId)))
            ->when($filters['estado'] === 'activo', fn (Builder $query): Builder => $query->where('activo', true))
            ->when($filters['estado'] === 'inactivo', fn (Builder $query): Builder => $query->where('activo', false))
            ->when($filters['ciudad'], fn (Builder $query, string $city): Builder => $query->where('ciudad', $city));
    }

    /**
     * @return array{buscar:?string,categoria:?int,estado:?string,ciudad:?string,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $search = trim($request->string('buscar')->toString());
        $status = trim($request->string('estado')->toString());
        $city = trim($request->string('ciudad')->toString());
        $perPage = $request->integer('por_pagina', 10);

        return [
            'buscar' => $search !== '' ? $search : null,
            'categoria' => $request->integer('categoria') ?: null,
            'estado' => in_array($status, ['activo', 'inactivo'], true) ? $status : null,
            'ciudad' => $city !== '' ? $city : null,
            'por_pagina' => in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
        ];
    }

    private function supplierDetails(int $supplierId): ?Proveedor
    {
        return Proveedor::query()
            ->with([
                'products' => fn ($query) => $query
                    ->select(['productos.id', 'productos.nombre', 'productos.categoria_id', 'productos.existencias'])
                    ->with('category:id,nombre')
                    ->orderBy('nombre'),
                'purchases' => fn ($query) => $query
                    ->latest('fecha_compra')
                    ->latest('id')
                    ->limit(5),
            ])
            ->withCount(['products', 'purchases'])
            ->withSum('purchases as purchases_total', 'total')
            ->withMax('purchases as latest_purchase_date', 'fecha_compra')
            ->find($supplierId);
    }

    /**
     * @return Collection<int, array{icon:string,color:string,title:string,detail:string,date:mixed}>
     */
    private function recentActivity(): Collection
    {
        $audits = RegistroAuditoria::query()
            ->where('entidad', 'proveedores')
            ->latest('creado_en')
            ->limit(5)
            ->get(['id', 'accion', 'entidad_id', 'creado_en']);
        $purchases = Compra::query()
            ->with('supplier:id,nombre')
            ->latest('creado_en')
            ->limit(5)
            ->get(['id', 'proveedor_id', 'total', 'fecha_compra', 'creado_en']);
        $activities = collect();

        foreach ($audits as $audit) {
            $activities->push([
                'icon' => match ($audit->accion) {
                    'supplier.created' => 'person-plus',
                    'supplier.deactivated' => 'x-circle',
                    default => 'pencil-square',
                },
                'color' => match ($audit->accion) {
                    'supplier.deactivated' => 'red',
                    'supplier.created' => 'green',
                    default => 'blue',
                },
                'title' => match ($audit->accion) {
                    'supplier.created' => 'Nuevo proveedor registrado',
                    'supplier.deactivated' => 'Proveedor desactivado',
                    default => 'Proveedor actualizado',
                },
                'detail' => 'Registro de proveedor #'.$audit->entidad_id,
                'date' => $audit->creado_en,
            ]);
        }

        foreach ($purchases as $purchase) {
            $activities->push([
                'icon' => 'cart-plus',
                'color' => 'purple',
                'title' => 'Nueva orden de compra',
                'detail' => ($purchase->supplier?->nombre ?: 'Proveedor').' · S/ '.number_format((float) $purchase->total, 2),
                'date' => $purchase->creado_en,
            ]);
        }

        return $activities->sortByDesc('date')->take(5)->values();
    }
}
