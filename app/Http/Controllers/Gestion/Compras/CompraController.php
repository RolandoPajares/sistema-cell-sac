<?php

namespace App\Http\Controllers\Gestion\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCompraRequest;
use App\Http\Requests\RegistrarRecepcionParcialCompraRequest;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCompras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class CompraController extends Controller
{
    public function __construct(
        private readonly ServicioCompras $purchaseService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'proveedor' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);
        $activePurchases = fn (): Builder => $this->authorization
            ->scopeRecordsFor(Compra::query(), $request->user(), 'gestion.compras.consultar')
            ->where('activo', true);
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
        $idsComprasPagina = $purchases->getCollection()->modelKeys();
        $cantidadesRecibidas = collect();
        if ($idsComprasPagina !== []) {
            $cantidadesRecibidas = DB::table('movimientos_inventario as movimiento')
                ->join('compras', function ($join): void {
                    $join->on('compras.id', '=', 'movimiento.documento_id')
                        ->on('compras.empresa_id', '=', 'movimiento.empresa_id')
                        ->on('compras.sede_id', '=', 'movimiento.sede_id')
                        ->on('compras.almacen_id', '=', 'movimiento.almacen_id');
                })
                ->where('compras.activo', true)
                ->whereIn('compras.id', $idsComprasPagina)
                ->where('movimiento.documento_tipo', 'compra')
                ->where('movimiento.tipo_movimiento', 'entrada')
                ->select('movimiento.documento_id', 'movimiento.producto_id', 'movimiento.variante_id')
                ->selectRaw('SUM(movimiento.cantidad) AS cantidad_recibida')
                ->groupBy('movimiento.documento_id', 'movimiento.producto_id', 'movimiento.variante_id')
                ->get()
                ->mapWithKeys(fn (object $movimiento): array => [
                    (string) $movimiento->documento_id.':'.(int) $movimiento->producto_id.':'.($movimiento->variante_id === null ? 'base' : (int) $movimiento->variante_id) => (int) $movimiento->cantidad_recibida,
                ]);
        }
        foreach ($purchases as $purchase) {
            $cantidadTotalRecibida = 0;
            foreach ($purchase->details as $detail) {
                $claveRecepcion = (int) $purchase->id.':'.(int) $detail->producto_id.':'.($detail->variante_id === null ? 'base' : (int) $detail->variante_id);
                $cantidadRecibida = (int) $cantidadesRecibidas->get($claveRecepcion, 0);
                $detail->setAttribute('cantidad_recibida', $cantidadRecibida);
                $detail->setAttribute('cantidad_pendiente', max(0, (int) $detail->cantidad - $cantidadRecibida));
                $cantidadTotalRecibida += $cantidadRecibida;
            }
            $purchase->setAttribute('cantidad_recibida_total', $cantidadTotalRecibida);
        }
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
            ->whereIn('estado', ['Pendiente', 'En proceso', 'Aprobada'])
            ->with('supplier')
            ->withCount('details')
            ->latest('fecha_compra')
            ->latest('id')
            ->limit(4)
            ->get();
        $visiblePurchaseIds = $this->authorization
            ->scopeRecordsFor(Compra::query(), $request->user(), 'gestion.compras.consultar')
            ->select('compras.id');
        $mostPurchasedProducts = DB::table('movimientos_inventario as movimiento')
            ->join('compras', function ($join): void {
                $join->on('compras.id', '=', 'movimiento.documento_id')
                    ->on('compras.empresa_id', '=', 'movimiento.empresa_id')
                    ->on('compras.sede_id', '=', 'movimiento.sede_id')
                    ->on('compras.almacen_id', '=', 'movimiento.almacen_id');
            })
            ->join('productos', 'productos.id', '=', 'movimiento.producto_id')
            ->where('compras.activo', true)
            ->where('movimiento.documento_tipo', 'compra')
            ->where('movimiento.tipo_movimiento', 'entrada')
            ->whereIn('compras.id', $visiblePurchaseIds)
            ->select('productos.nombre')
            ->selectRaw('SUM(movimiento.cantidad) as cantidad')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidad')
            ->limit(4)
            ->get();

        $proveedoresVisibles = Proveedor::query()->whereIn('id', (clone $activePurchases())->select('compras.proveedor_id'))->orderBy('nombre')->get();
        $suppliers = collect();
        $products = collect();
        $canCreate = false;
        if (Schema::hasColumn('registros_auditoria', 'identificador_universal_evento') && $this->authorization->allows($request->user(), 'gestion.compras.crear')) {
            try {
                $ubicacion = $this->authorization->operationLocation($request->user());
                $canCreate = $ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null
                    && $this->authorization->allowsRecord($request->user(), 'gestion.compras.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']);
            } catch (HttpExceptionInterface) {
                $canCreate = false;
            }
            if ($canCreate) {
                $suppliers = Proveedor::query()->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->orderBy('nombre')->get();
                $products = Producto::query()->active()->where('empresa_id', $ubicacion['empresa_id'])->with('variants:id,producto_id,nombre_color,codigo_sku,estado')->orderBy('nombre')->get();
                if (! is_string($request->session()->get('compra_token'))) {
                    $request->session()->put('compra_token', bin2hex(random_bytes(32)));
                }
            }
        }
        $idsPendientes = array_values(array_unique([...$pendingPurchaseRecords->modelKeys(), ...$purchases->getCollection()->modelKeys()]));
        $aprobables = $this->authorization->scopeRecordsFor(Compra::query()->whereIn('compras.id', $idsPendientes), $request->user(), 'gestion.compras.aprobar')->pluck('compras.id')->all();
        $recibibles = $this->authorization->scopeRecordsFor(Compra::query()->whereIn('compras.id', $idsPendientes), $request->user(), 'gestion.compras.recibir')->pluck('compras.id')->all();
        $tokensRecepcion = [];
        foreach ($purchases as $purchase) {
            if ($purchase->estado !== 'Aprobada' || ! in_array($purchase->id, $recibibles, true)) {
                continue;
            }
            $claveToken = 'compra_recepcion_tokens.'.$purchase->id;
            $tokenExistente = $request->session()->get($claveToken);
            if (! is_string($tokenExistente) || preg_match('/\A[a-f0-9]{64}\z/', $tokenExistente) !== 1) {
                $tokenExistente = bin2hex(random_bytes(32));
                $request->session()->put($claveToken, $tokenExistente);
            }
            $tokensRecepcion[$purchase->id] = $tokenExistente;
        }

        return view('gestion.compras.listar', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'supplierFilters' => $proveedoresVisibles,
            'products' => $products,
            'canCreate' => $canCreate,
            'aprobables' => $aprobables,
            'recibibles' => $recibibles,
            'tokensRecepcion' => $tokensRecepcion,
            'filters' => $filters,
            'statusSummary' => $statusSummary->map(fn ($status): array => [
                'label' => $status->estado,
                'count' => (int) $status->cantidad,
            ]),
            'totalPurchases' => $totalPurchases,
            'pendingPurchases' => (clone $allPurchases)->whereIn('estado', ['Pendiente', 'En proceso', 'Aprobada'])->count(),
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
        $datos = $request->validated();
        $this->purchaseService->registrar($datos, $request->user(), $datos['compra_token'], $request->ip());
        $request->session()->forget('compra_token');

        return back()->with('status', 'Compra registrada como pendiente; aún no se ha recibido mercadería.');
    }

    public function approve(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->aprobar($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra está aprobada; aún no se ha recibido mercadería.');
    }

    public function receive(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->recibir($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra está recibida.');
    }

    public function recibirParcial(RegistrarRecepcionParcialCompraRequest $request, Compra $compra): RedirectResponse
    {
        $datos = $request->validated();
        $this->purchaseService->registrarRecepcionParcial(
            $compra,
            $datos['items'],
            $datos['observaciones'] ?? null,
            $datos['token_recepcion'],
            $request->user(),
            $request->ip(),
        );
        $request->session()->put('compra_recepcion_tokens.'.$compra->id, bin2hex(random_bytes(32)));
        $compraActualizada = $compra->fresh();

        return back()->with('status', $compraActualizada?->estado === 'Recibida'
            ? 'La recepción completó la compra y actualizó las existencias.'
            : 'La recepción parcial quedó registrada y la discrepancia se agregó a la auditoría.');
    }
}
