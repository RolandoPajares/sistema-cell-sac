<?php

namespace App\Http\Controllers\Gestion\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCompraRequest;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Servicios\ServicioCompras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function __construct(private readonly ServicioCompras $purchaseService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'proveedor' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);
        $activePurchases = static fn (): Builder => Compra::query()->where('activo', true);
        $applyFilters = static function (Builder $query) use ($filters): Builder {
            if (($filters['buscar'] ?? '') !== '') {
                $search = trim($filters['buscar']);
                $query->where(function (Builder $query) use ($search): void {
                    if (ctype_digit($search)) {
                        $query->where('id', (int) $search)
                            ->orWhereHas('supplier', fn (Builder $supplierQuery) => $supplierQuery->where('nombre', 'like', "%{$search}%"))
                            ->orWhereHas('details.product', fn (Builder $productQuery) => $productQuery->where('nombre', 'like', "%{$search}%"));

                        return;
                    }

                    $query->whereHas('supplier', fn (Builder $supplierQuery) => $supplierQuery->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('details.product', fn (Builder $productQuery) => $productQuery->where('nombre', 'like', "%{$search}%"));
                });
            }

            if (($filters['proveedor'] ?? '') !== '') {
                $query->where('proveedor_id', $filters['proveedor']);
            }

            if (($filters['estado'] ?? '') !== '') {
                $query->where('estado', $filters['estado']);
            }

            if (($filters['desde'] ?? '') !== '') {
                $query->whereDate('fecha_compra', '>=', $filters['desde']);
            }

            if (($filters['hasta'] ?? '') !== '') {
                $query->whereDate('fecha_compra', '<=', $filters['hasta']);
            }

            return $query;
        };

        $purchases = $applyFilters($activePurchases())
            ->with(['supplier', 'details.product'])
            ->withCount('details')
            ->latest('fecha_compra')
            ->latest('id')
            ->paginate(8)
            ->withQueryString();
        $allPurchases = $activePurchases();
        $statusSummary = (clone $allPurchases)
            ->select('estado')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('estado')
            ->orderByDesc('cantidad')
            ->get();
        $totalPurchases = (clone $allPurchases)->count();
        $recentPurchases = (clone $allPurchases)
            ->with('supplier')
            ->withCount('details')
            ->latest('fecha_compra')
            ->latest('id')
            ->limit(4)
            ->get();
        $pendingPurchaseRecords = (clone $allPurchases)
            ->whereIn('estado', ['Pendiente', 'En proceso'])
            ->with('supplier')
            ->withCount('details')
            ->latest('fecha_compra')
            ->latest('id')
            ->limit(4)
            ->get();
        $mostPurchasedProducts = DB::table('compra_detalles')
            ->join('compras', 'compras.id', '=', 'compra_detalles.compra_id')
            ->join('productos', 'productos.id', '=', 'compra_detalles.producto_id')
            ->where('compras.activo', true)
            ->select('productos.nombre')
            ->selectRaw('SUM(compra_detalles.cantidad) as cantidad')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidad')
            ->limit(4)
            ->get();

        return view('gestion.compras.listar', [
            'purchases' => $purchases,
            'suppliers' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'supplierFilters' => Proveedor::query()->orderBy('nombre')->get(),
            'products' => Producto::query()->active()->orderBy('nombre')->get(),
            'filters' => $filters,
            'statusSummary' => $statusSummary->map(fn ($status): array => [
                'label' => $status->estado,
                'count' => (int) $status->cantidad,
            ]),
            'totalPurchases' => $totalPurchases,
            'pendingPurchases' => (clone $allPurchases)->whereIn('estado', ['Pendiente', 'En proceso'])->count(),
            'receivedPurchases' => (clone $allPurchases)->where('estado', 'Recibida')->count(),
            'monthlyPurchaseTotal' => (float) (clone $allPurchases)
                ->whereYear('fecha_compra', now()->year)
                ->whereMonth('fecha_compra', now()->month)
                ->sum('total'),
            'monthlyPurchaseCount' => (clone $allPurchases)
                ->whereYear('fecha_compra', now()->year)
                ->whereMonth('fecha_compra', now()->month)
                ->count(),
            'recentPurchases' => $recentPurchases,
            'pendingPurchaseRecords' => $pendingPurchaseRecords,
            'mostPurchasedProducts' => $mostPurchasedProducts,
        ]);
    }

    public function store(GuardarCompraRequest $request): RedirectResponse
    {
        $this->purchaseService->receive($request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'Compra recibida e inventario actualizado.');
    }
}
