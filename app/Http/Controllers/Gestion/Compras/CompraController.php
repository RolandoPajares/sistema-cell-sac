<?php

namespace App\Http\Controllers\Gestion\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCompraRequest;
use App\Http\Requests\GuardarCompraRequest;
use App\Http\Requests\ImportarComprasRequest;
use App\Http\Requests\RegistrarRecepcionParcialCompraRequest;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCompras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Define la responsabilidad de la clase CompraController dentro de su mÃ³dulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CompraController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operaciÃ³n.
     */
    public function __construct(
        private readonly ServicioCompras $purchaseService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    /**
     * Obtiene y prepara la informaciÃ³n solicitada para la operaciÃ³n.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @return View Resultado de la operaciÃ³n.
     */
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
            ->with(['supplier', 'details.product', 'details.variante'])
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
        $canCreatePermission = $this->authorization->allows($request->user(), 'gestion.compras.crear');
        $canExport = $this->authorization->allows($request->user(), 'gestion.compras.exportar');
        $canImport = false;
        $canCreateLocation = false;
        $canCreate = false;
        $editables = [];
        if (Schema::hasColumn('registros_auditoria', 'identificador_universal_evento') && $canCreatePermission) {
            try {
                $ubicacion = $this->authorization->operationLocation($request->user());
                $canCreateLocation = $ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null
                    && $this->authorization->allowsRecord($request->user(), 'gestion.compras.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']);
                $canImport = $canCreateLocation;
            } catch (HttpExceptionInterface) {
                $canCreateLocation = false;
                $canImport = false;
            }
            if ($canCreateLocation) {
                $suppliers = Proveedor::query()->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->orderBy('nombre')->get();
                $products = Producto::query()->active()->where('empresa_id', $ubicacion['empresa_id'])->with('variants:id,producto_id,nombre_color,codigo_sku,estado')->orderBy('nombre')->get();
                if (! is_string($request->session()->get('compra_token'))) {
                    $request->session()->put('compra_token', bin2hex(random_bytes(32)));
                }
                $canCreate = $suppliers->isNotEmpty() && $products->isNotEmpty();
            }
        }
        $editables = $this->authorization
            ->scopeRecordsFor(Compra::query()->whereIn('compras.id', $purchases->getCollection()->modelKeys())->where('estado', 'Pendiente'), $request->user(), 'gestion.compras.actualizar')
            ->pluck('compras.id')
            ->all();
        $editables = array_values(array_filter($editables, function (int $purchaseId): bool {
            if (DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $purchaseId)->exists()) {
                return false;
            }
            if (Schema::hasTable('pagos') && DB::table('pagos')->where('compra_id', $purchaseId)->exists()) {
                return false;
            }

            return ! Schema::hasTable('cuentas_pagar') || ! DB::table('cuentas_pagar')->where('compra_id', $purchaseId)->exists();
        }));
        $idsPendientes = array_values(array_unique([...$pendingPurchaseRecords->modelKeys(), ...$purchases->getCollection()->modelKeys()]));
        $aprobables = $this->authorization->scopeRecordsFor(Compra::query()->whereIn('compras.id', $idsPendientes), $request->user(), 'gestion.compras.aprobar')->pluck('compras.id')->all();
        $anulables = $this->authorization->scopeRecordsFor(Compra::query()->whereIn('compras.id', $idsPendientes)->where('estado', 'Pendiente'), $request->user(), 'gestion.compras.anular')->pluck('compras.id')->all();
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
        $purchasePresentation = $purchases->getCollection()
            ->concat($pendingPurchaseRecords)
            ->concat($recentPurchases)
            ->unique('id')
            ->mapWithKeys(function (Compra $purchase): array {
                $status = (string) $purchase->estado;
                $receptionStatus = $purchase->estado === 'Aprobada' && (int) $purchase->cantidad_recibida_total > 0
                    ? 'Parcial'
                    : $status;

                return [$purchase->id => [
                    'status_class' => $this->statusClass($status),
                    'reception_class' => $this->statusClass($receptionStatus),
                    'reception_label' => $this->receptionLabel($receptionStatus),
                    'supplier_name' => $purchase->supplier?->nombre ?? 'Proveedor no disponible',
                    'reference' => 'OC-'.($purchase->fecha_compra?->format('y') ?? now()->format('y')).'-'.str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT),
                ]];
            })
            ->all();

        return view('gestion.comercial.compras.listar', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'supplierFilters' => $proveedoresVisibles,
            'products' => $products,
            'canCreate' => $canCreate,
            'editables' => $editables,
            'canCreatePermission' => $canCreatePermission,
            'canCreateLocation' => $canCreateLocation,
            'canImport' => $canImport,
            'canExport' => $canExport,
            'aprobables' => $aprobables,
            'anulables' => $anulables,
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
            'purchasePresentation' => $purchasePresentation,
        ]);
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'Pendiente', 'En proceso' => 'pending',
            'Aprobada' => 'approved',
            'Recibida', 'Completada' => 'received',
            'Parcial' => 'partial',
            'Cancelada', 'Cancelado', 'Rechazada' => 'cancelled',
            default => 'muted',
        };
    }

    private function receptionLabel(string $status): string
    {
        return match ($status) {
            'Recibida', 'Completada' => 'Completa',
            'Parcial' => 'Parcial',
            'Pendiente', 'En proceso', 'Aprobada' => 'Sin recibir',
            default => $status,
        };
    }

    /**
     * Genera la salida exportable de la informaciÃ³n disponible.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $format  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $exporter  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $audit  ParÃ¡metro recibido por la operaciÃ³n.
     * @return Response|StreamedResponse Resultado de la operaciÃ³n.
     */
    public function export(Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportaciÃ³n no permitido.');
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'proveedor' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);
        $query = $this->authorization->scopeRecordsFor(Compra::query(), $request->user(), 'gestion.compras.consultar')
            ->where('activo', true)
            ->with('supplier')
            ->when(($filters['buscar'] ?? '') !== '', function (Builder $builder) use ($filters): void {
                $search = trim((string) $filters['buscar']);
                $builder->where(function (Builder $nested) use ($search): void {
                    if (ctype_digit($search)) {
                        $nested->where('id', (int) $search)
                            ->orWhereHas('supplier', fn (Builder $supplier): Builder => $supplier->where('nombre', 'like', '%'.$search.'%'));

                        return;
                    }

                    $nested->whereHas('supplier', fn (Builder $supplier): Builder => $supplier->where('nombre', 'like', '%'.$search.'%'));
                });
            })
            ->when(($filters['proveedor'] ?? '') !== '', fn (Builder $builder): Builder => $builder->where('proveedor_id', $filters['proveedor']))
            ->when(($filters['estado'] ?? '') !== '', fn (Builder $builder): Builder => $builder->where('estado', $filters['estado']))
            ->when(($filters['desde'] ?? '') !== '', fn (Builder $builder): Builder => $builder->whereDate('fecha_compra', '>=', $filters['desde']))
            ->when(($filters['hasta'] ?? '') !== '', fn (Builder $builder): Builder => $builder->whereDate('fecha_compra', '<=', $filters['hasta']))
            ->orderByDesc('fecha_compra')->orderByDesc('id')->limit(5000);
        $purchases = $query->get();
        $rows = $purchases->map(static fn (Compra $purchase): array => [
            $purchase->id,
            $purchase->supplier?->nombre,
            $purchase->fecha_compra?->format('Y-m-d'),
            $purchase->estado,
            $purchase->total,
        ])->all();
        $response = $exporter->download($format, 'compras-'.now()->format('Ymd'),
            ['ID', 'Proveedor', 'Fecha', 'Estado', 'Total'],
            $rows,
            [
                'title' => 'REPORTE DE COMPRAS',
                'subtitle' => 'Compras reales consultadas desde MariaDB.',
                'module' => 'Operaciones Comerciales',
                'report_name' => 'Compras',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa, sede, almacÃ©n y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Compras incluidas', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'compras', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Registra la informaciÃ³n recibida y coordina su persistencia.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @return RedirectResponse Resultado de la operaciÃ³n.
     */
    public function store(GuardarCompraRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $this->purchaseService->registrar($datos, $request->user(), $datos['compra_token'], $request->ip());
        $request->session()->forget('compra_token');

        return back()->with('status', 'Compra registrada como pendiente; aÃºn no se ha recibido mercaderÃ­a.');
    }

    /**
     * Procesa e incorpora la informaciÃ³n importada.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @return RedirectResponse Resultado de la operaciÃ³n.
     */
    /**
     * Actualiza una compra pendiente y conserva su identificador original.
     */
    public function update(ActualizarCompraRequest $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->actualizar($compra, $request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'La compra fue actualizada correctamente.');
    }

    public function import(ImportarComprasRequest $request): RedirectResponse
    {
        $expected = ['proveedor_id', 'fecha_compra', 'producto_id', 'variante_id', 'cantidad', 'costo_unitario'];
        $handle = fopen($request->file('archivo')->getRealPath(), 'rb');
        abort_unless($handle !== false, 422, 'No se pudo leer el archivo CSV.');
        $header = fgetcsv($handle);
        $header = is_array($header) ? array_map(static fn ($value): string => trim((string) $value), $header) : [];
        if ($header !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages(['archivo' => 'El CSV debe contener exactamente: '.implode(',', $expected).'.']);
        }
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }
            if (count($row) !== count($expected)) {
                fclose($handle);
                throw ValidationException::withMessages(['archivo' => 'El CSV contiene una fila con columnas invÃ¡lidas.']);
            }
            $data = array_combine($expected, array_map(static fn ($value): string => trim((string) $value), $row));
            $validator = validator($data, [
                'proveedor_id' => ['required', 'integer', 'min:1'], 'fecha_compra' => ['required', 'date_format:Y-m-d'],
                'producto_id' => ['required', 'integer', 'min:1'], 'variante_id' => ['nullable', 'integer', 'min:1'],
                'cantidad' => ['required', 'integer', 'min:1', 'max:1000'], 'costo_unitario' => ['required', 'regex:/\A\d{1,8}(?:\.\d{1,2})?\z/'],
            ]);
            if ($validator->fails()) {
                fclose($handle);
                throw ValidationException::withMessages(['archivo' => 'Fila invÃ¡lida: '.implode(' ', $validator->errors()->all())]);
            }
            $rows[] = $data;
            if (count($rows) > 100) {
                fclose($handle);
                throw ValidationException::withMessages(['archivo' => 'El archivo no puede superar 100 lÃ­neas.']);
            }
        }
        fclose($handle);
        if ($rows === []) {
            throw ValidationException::withMessages(['archivo' => 'El CSV no contiene lÃ­neas de compra.']);
        }

        DB::transaction(function () use ($rows, $request): void {
            foreach ($rows as $data) {
                $token = bin2hex(random_bytes(32));
                $request->session()->put('compra_token', $token);
                $this->purchaseService->registrar([
                    'proveedor_id' => (int) $data['proveedor_id'], 'fecha_compra' => $data['fecha_compra'],
                    'items' => [[
                        'producto_id' => (int) $data['producto_id'],
                        'variante_id' => $data['variante_id'] === '' ? null : (int) $data['variante_id'],
                        'cantidad' => (int) $data['cantidad'], 'costo_unitario' => $data['costo_unitario'],
                    ]],
                ], $request->user(), $token, $request->ip());
            }
        }, 3);

        return back()->with('status', count($rows).' compra(s) importada(s) correctamente.');
    }

    /**
     * Ejecuta la operaciÃ³n approve definida por la clase CompraController.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $compra  ParÃ¡metro recibido por la operaciÃ³n.
     * @return RedirectResponse Resultado de la operaciÃ³n.
     */
    public function approve(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->aprobar($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra estÃ¡ aprobada; aÃºn no se ha recibido mercaderÃ­a.');
    }

    /**
     * Ejecuta la operaciÃ³n receive definida por la clase CompraController.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $compra  ParÃ¡metro recibido por la operaciÃ³n.
     * @return RedirectResponse Resultado de la operaciÃ³n.
     */
    public function receive(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->recibir($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra estÃ¡ recibida.');
    }

    /**
     * Modifica el estado o elimina el registro segÃºn la operaciÃ³n solicitada.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $compra  ParÃ¡metro recibido por la operaciÃ³n.
     * @return RedirectResponse Resultado de la operaciÃ³n.
     */
    public function cancel(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->anular($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra pendiente fue anulada y quedÃ³ conservada en auditorÃ­a.');
    }

    /**
     * Ejecuta la operaciÃ³n recibirParcial definida por la clase CompraController.
     *
     * @param  mixed  $request  ParÃ¡metro recibido por la operaciÃ³n.
     * @param  mixed  $compra  ParÃ¡metro recibido por la operaciÃ³n.
     * @return RedirectResponse Resultado de la operaciÃ³n.
     */
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
            ? 'La recepciÃ³n completÃ³ la compra y actualizÃ³ las existencias.'
            : 'La recepciÃ³n parcial quedÃ³ registrada y la discrepancia se agregÃ³ a la auditorÃ­a.');
    }
}
