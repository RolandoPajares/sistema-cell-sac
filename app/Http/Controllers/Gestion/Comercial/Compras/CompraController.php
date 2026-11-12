<?php

namespace App\Http\Controllers\Gestion\Comercial\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Comercial\Compras\ActualizarCompraRequest;
use App\Http\Requests\Gestion\Comercial\Compras\GuardarCompraRequest;
use App\Http\Requests\Gestion\Comercial\Compras\ImportarComprasRequest;
use App\Http\Requests\Gestion\Comercial\Compras\RegistrarRecepcionParcialCompraRequest;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Comercial\Compras\ServicioCompras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use ZipArchive;

/**
 * Define la responsabilidad de la clase CompraController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CompraController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioCompras $purchaseService,
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
                // El formulario debe poder abrirse aunque la base limpia todavía
                // no tenga proveedores ni productos. La validación del registro
                // impedirá guardar una compra incompleta.
                $canCreate = true;
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
                    'status_class' => $this->claseEstado($status),
                    'reception_class' => $this->claseEstado($receptionStatus),
                    'reception_label' => $this->etiquetaRecepcion($receptionStatus),
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

    private function claseEstado(string $status): string
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

    private function etiquetaRecepcion(string $status): string
    {
        return match ($status) {
            'Recibida', 'Completada' => 'Completa',
            'Parcial' => 'Parcial',
            'Pendiente', 'En proceso', 'Aprobada' => 'Sin recibir',
            default => $status,
        };
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
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
                'scope' => 'Empresa, sede, almacén y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Compras incluidas', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'compras', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCompraRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $this->purchaseService->registrar($datos, $request->user(), $datos['compra_token'], $request->ip());
        $request->session()->forget('compra_token');

        return back()->with('status', 'Compra registrada como pendiente; aún no se ha recibido mercadería.');
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    /**
     * Actualiza una compra pendiente y conserva su identificador original.
     */
    public function update(ActualizarCompraRequest $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->actualizar($compra, $request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'La compra fue actualizada correctamente.');
    }

    public function importTemplate(ExportadorTabular $exportador): Response|StreamedResponse
    {
        $columns = ['proveedor_id', 'fecha_compra', 'producto_id', 'variante_id', 'cantidad', 'costo_unitario'];

        return $exportador->download('excel', 'plantilla-importacion-compras', $columns, [array_fill(0, count($columns), null)], [
            'title' => 'Plantilla de importación de compras',
            'description' => 'Completa una fila por línea de compra. No cambies los nombres de las columnas.',
        ]);
    }

    public function import(ImportarComprasRequest $request): RedirectResponse
    {
        $expected = ['proveedor_id', 'fecha_compra', 'producto_id', 'variante_id', 'cantidad', 'costo_unitario'];
        $temporaryCsv = null;
        $file = $request->file('archivo');
        $path = $file->getRealPath();
        if (strtolower($file->getClientOriginalExtension()) === 'xlsx') {
            $temporaryCsv = tempnam(sys_get_temp_dir(), 'tc-import-');
            abort_if($temporaryCsv === false, 422, 'No se pudo preparar el archivo Excel.');
            $output = fopen($temporaryCsv, 'wb');
            abort_if($output === false, 422, 'No se pudo preparar el archivo Excel.');
            fputcsv($output, $expected);
            foreach ($this->leerFilasXlsx($file) as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            $path = $temporaryCsv;
        }
        $handle = fopen($path, 'rb');
        abort_unless($handle !== false, 422, 'No se pudo leer el archivo CSV.');
        $header = fgetcsv($handle);
        $header = is_array($header) ? array_map(static fn ($value): string => trim((string) $value), $header) : [];
        if ($header !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages(['archivo' => 'El CSV debe contener exactamente: '.implode(',', $expected).'.']);
        }
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [] || collect($row)->every(static fn ($value): bool => trim((string) $value) === '')) {
                continue;
            }
            if (count($row) !== count($expected)) {
                fclose($handle);
                throw ValidationException::withMessages(['archivo' => 'El CSV contiene una fila con columnas inválidas.']);
            }
            $data = array_combine($expected, array_map(static fn ($value): string => trim((string) $value), $row));
            $validator = validator($data, [
                'proveedor_id' => ['required', 'integer', 'min:1'], 'fecha_compra' => ['required', 'date_format:Y-m-d'],
                'producto_id' => ['required', 'integer', 'min:1'], 'variante_id' => ['nullable', 'integer', 'min:1'],
                'cantidad' => ['required', 'integer', 'min:1', 'max:1000'], 'costo_unitario' => ['required', 'regex:/\A\d{1,8}(?:\.\d{1,2})?\z/'],
            ]);
            if ($validator->fails()) {
                fclose($handle);
                throw ValidationException::withMessages(['archivo' => 'Fila inválida: '.implode(' ', $validator->errors()->all())]);
            }
            $rows[] = $data;
            if (count($rows) > 100) {
                fclose($handle);
                throw ValidationException::withMessages(['archivo' => 'El archivo no puede superar 100 líneas.']);
            }
        }
        fclose($handle);
        if ($rows === []) {
            throw ValidationException::withMessages(['archivo' => 'El CSV no contiene líneas de compra.']);
        }

        if ($temporaryCsv !== null && file_exists($temporaryCsv)) {
            unlink($temporaryCsv);
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
     * Ejecuta la operación approve definida por la clase CompraController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function approve(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->aprobar($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra está aprobada; aún no se ha recibido mercadería.');
    }

    /**
     * Ejecuta la operación receive definida por la clase CompraController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function receive(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->recibir($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra está recibida.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function cancel(Request $request, Compra $compra): RedirectResponse
    {
        $this->purchaseService->anular($compra, $request->user(), $request->ip());

        return back()->with('status', 'La compra pendiente fue anulada y quedó conservada en auditoría.');
    }

    /**
     * Ejecuta la operación recibirParcial definida por la clase CompraController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
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
            ? 'La recepción completó la compra y actualizó las existencias.'
            : 'La recepción parcial quedó registrada y la discrepancia se agregó a la auditoría.');
    }

    /** @return array<int, array<int, string>> */
    private function leerFilasXlsx(UploadedFile $file): array
    {
        abort_unless(class_exists(ZipArchive::class), 422, 'El servidor no tiene habilitada la lectura de archivos Excel.');
        $zip = new ZipArchive;
        abort_unless($zip->open($file->getRealPath()) === true, 422, 'El archivo Excel no se puede abrir.');
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        abort_unless(is_string($sheet), 422, 'La plantilla Excel no contiene una hoja válida.');
        $dom = new \DOMDocument;
        abort_unless(@$dom->loadXML($sheet) === true, 422, 'La hoja Excel no tiene un formato válido.');
        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $rows = [];
        foreach ($dom->getElementsByTagNameNS($namespace, 'row') as $rowNode) {
            $values = [];
            foreach ($rowNode->getElementsByTagNameNS($namespace, 'c') as $cell) {
                preg_match('/^([A-Z]+)/', (string) $cell->getAttribute('r'), $match);
                $column = 0;
                foreach (str_split($match[1] ?? 'A') as $letter) {
                    $column = ($column * 26) + ord($letter) - 64;
                }
                $valueNode = $cell->getElementsByTagNameNS($namespace, 't')->item(0)
                    ?: $cell->getElementsByTagNameNS($namespace, 'v')->item(0);
                $values[$column - 1] = $valueNode?->textContent ?? '';
            }
            ksort($values);
            $rows[] = array_pad(array_values($values), 6, '');
        }

        return array_slice($rows, 1);
    }
}
