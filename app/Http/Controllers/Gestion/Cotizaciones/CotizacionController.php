<?php

namespace App\Http\Controllers\Gestion\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCotizacionRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Servicios\ServicioCotizaciones;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    public function __construct(private readonly ServicioCotizaciones $quotationService) {}

    public function index(Request $request): View
    {
        if ($request->routeIs('management.quotations')) {
            return $this->administratorIndex($request);
        }

        $query = Cotizacion::with('customer', 'details.product')->latest('creado_en')->latest('id');
        if ($request->user()->rol === 'cliente_mayorista') {
            $query->whereHas('customer', fn ($builder) => $builder->where('usuario_id', $request->user()->id));
        }

        return view('gestion.cotizaciones.listar', [
            'quotations' => $query->paginate(20),
            'customers' => Cliente::query()->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get(),
            'products' => Producto::query()->active()->orderBy('nombre')->get(),
        ]);
    }

    private function administratorIndex(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'cliente' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);

        $activeQuotations = static fn (): Builder => Cotizacion::query()->where('activo', true);
        $applyFilters = static function (Builder $query) use ($filters): Builder {
            if (($filters['buscar'] ?? '') !== '') {
                $search = trim($filters['buscar']);
                $query->where(function (Builder $query) use ($search): void {
                    if (ctype_digit($search)) {
                        $query->where('id', (int) $search)
                            ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery
                                ->where('nombre_contacto', 'like', "%{$search}%")
                                ->orWhere('documento', 'like', "%{$search}%"))
                            ->orWhereHas('details.product', fn (Builder $productQuery) => $productQuery
                                ->where('nombre', 'like', "%{$search}%"));

                        return;
                    }

                    $query->whereHas('customer', fn (Builder $customerQuery) => $customerQuery
                        ->where('nombre_contacto', 'like', "%{$search}%")
                        ->orWhere('documento', 'like', "%{$search}%"))
                        ->orWhereHas('details.product', fn (Builder $productQuery) => $productQuery
                            ->where('nombre', 'like', "%{$search}%"));
                });
            }

            if (isset($filters['cliente']) && $filters['cliente'] !== '') {
                $query->where('cliente_id', $filters['cliente']);
            }

            if (($filters['estado'] ?? '') !== '') {
                $query->where('estado', $filters['estado']);
            }

            if (($filters['desde'] ?? '') !== '') {
                $query->whereDate('creado_en', '>=', $filters['desde']);
            }

            if (($filters['hasta'] ?? '') !== '') {
                $query->whereDate('creado_en', '<=', $filters['hasta']);
            }

            return $query;
        };

        $quotations = $applyFilters($activeQuotations())
            ->with(['customer', 'details.product'])
            ->withCount('details')
            ->latest('creado_en')
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        $allQuotations = $activeQuotations();
        $recentQuotations = (clone $allQuotations)
            ->with('customer')
            ->latest('creado_en')
            ->latest('id')
            ->limit(4)
            ->get();
        $statusSummary = (clone $allQuotations)
            ->select('estado')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('estado')
            ->orderByDesc('cantidad')
            ->get();
        $totalQuotations = (clone $allQuotations)->count();

        return view('gestion.cotizaciones.administrador', [
            'quotations' => $quotations,
            'customers' => Cliente::query()
                ->where('tipo', 'mayorista')
                ->where('activo', true)
                ->orderBy('nombre_contacto')
                ->get(),
            'products' => Producto::query()->active()->orderBy('nombre')->get(),
            'filters' => $filters,
            'totalQuotations' => $totalQuotations,
            'pendingQuotations' => (clone $allQuotations)->whereIn('estado', ['Borrador', 'Pendiente', 'Enviada'])->count(),
            'approvedQuotations' => (clone $allQuotations)->where('estado', 'Aprobada')->count(),
            'quotedTotal' => (float) (clone $allQuotations)->sum('total'),
            'recentQuotations' => $recentQuotations,
            'statusSummary' => $statusSummary->map(fn ($status): array => [
                'label' => $status->estado,
                'count' => (int) $status->cantidad,
                'percent' => $totalQuotations > 0 ? round(((int) $status->cantidad / $totalQuotations) * 100) : 0,
            ]),
        ]);
    }

    public function store(GuardarCotizacionRequest $request): RedirectResponse
    {
        $this->quotationService->create($request->validated(), $request->user());

        return back()->with('status', 'CotizaciÃ³n guardada como borrador; aÃºn no se envÃ­a al cliente.');
    }
}
