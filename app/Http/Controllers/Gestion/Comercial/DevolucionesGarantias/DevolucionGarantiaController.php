<?php

namespace App\Http\Controllers\Gestion\Comercial\DevolucionesGarantias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Comercial\DevolucionesGarantias\ActualizarDevolucionGarantiaRequest;
use App\Http\Requests\Gestion\Comercial\DevolucionesGarantias\GuardarDevolucionGarantiaRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\DevolucionGarantia;
use App\Models\Comercio\Pedido;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase DevolucionGarantiaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class DevolucionGarantiaController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $authorization) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $returnsQuery = $this->applyFilters($this->authorization->scopeRecordsFor(
            DevolucionGarantia::query()->where('activo', true),
            $request->user(),
            'gestion.devoluciones.consultar',
        ), $filters);
        $returns = (clone $returnsQuery)
            ->with(['customer:id,nombre_contacto,razon_social', 'product:id,nombre', 'order:id', 'sale:id,codigo'])
            ->when($filters['orden'] === 'antiguos', fn (Builder $query): Builder => $query->orderBy('fecha_solicitud')->orderBy('id'))
            ->when($filters['orden'] !== 'antiguos', fn (Builder $query): Builder => $query->latest('fecha_solicitud')->latest('id'))
            ->paginate(8)
            ->withQueryString();
        $allReturns = $this->authorization->scopeRecordsFor(
            DevolucionGarantia::query()->where('activo', true),
            $request->user(),
            'gestion.devoluciones.consultar',
        );
        $module = require config_path('gestion/inventario/devoluciones-garantias.php');
        $pedidosFormulario = $this->authorization->scopeRecordsFor(Pedido::query(), $request->user(), 'gestion.pedidos.consultar')
            ->whereNotNull('cliente_id')->with(['user:id,nombre', 'details:pedido_id,producto_id'])
            ->latest('creado_en')->latest('id')->limit(100)
            ->get(['id', 'usuario_id', 'cliente_id', 'creado_en']);
        $module['form_data'] = [
            'customers' => Cliente::query()->whereIn('id', $pedidosFormulario->pluck('cliente_id'))
                ->where('activo', true)->orderBy('nombre_contacto')->get(['id', 'nombre_contacto', 'razon_social']),
            'orders' => $pedidosFormulario,
            'products' => Producto::query()->whereIn('id', $pedidosFormulario->flatMap(fn (Pedido $pedido) => $pedido->details->pluck('producto_id'))->unique())
                ->active()->orderBy('nombre')->get(['id', 'nombre']),
        ];
        $module['operacion_ui']['backend'] = true;
        $module['operacion_ui']['filter_action'] = route('management.returns');
        $module['operacion_ui']['clear_action'] = route('management.returns');
        $module['operacion_ui']['filter_route'] = 'management.returns';
        $module['operacion_ui']['filter_values'] = $filters;
        $module['operacion_ui']['sort_options'] = [
            'recientes' => 'Más recientes',
            'antiguos' => 'Más antiguos',
        ];
        $module['operacion_ui']['table_count'] = $returns->total().' registros';
        $module['operacion_ui']['table_footer'] = $returns->total() > 0
            ? 'Mostrando '.$returns->firstItem().' a '.$returns->lastItem().' de '.$returns->total().' solicitudes'
            : 'Sin registros disponibles';
        $module['operacion_ui']['pagination'] = [
            'current' => $returns->currentPage(),
            'last' => $returns->lastPage(),
            'previous' => $returns->previousPageUrl(),
            'next' => $returns->nextPageUrl(),
        ];
        $module['operacion_ui']['kpis'][0]['value'] = (string) (clone $allReturns)->whereIn('estado', ['Recibida', 'En evaluación'])->count();
        $module['operacion_ui']['kpis'][1]['value'] = (string) (clone $allReturns)->where('estado', 'En evaluación')->count();
        $module['operacion_ui']['kpis'][2]['value'] = (string) (clone $allReturns)->where('estado', 'Resuelta')->count();
        $module['operacion_ui']['kpis'][3]['value'] = (string) (clone $allReturns)
            ->whereNotIn('estado', ['Resuelta', 'Rechazada'])
            ->whereNotNull('fecha_limite')
            ->whereDate('fecha_limite', '<', today())
            ->count();
        $priorityCount = (clone $allReturns)->whereNotIn('estado', ['Resuelta', 'Rechazada'])
            ->whereNotNull('fecha_limite')->whereDate('fecha_limite', '<', today())->count();
        $upcomingCount = (clone $allReturns)->whereNotIn('estado', ['Resuelta', 'Rechazada'])
            ->whereNotNull('fecha_limite')->whereBetween('fecha_limite', [today(), today()->addDays(30)])->count();
        $resolvedCount = (clone $allReturns)->where('estado', 'Resuelta')->count();
        $module['operacion_ui']['panels'][0]['empty'] = $priorityCount > 0
            ? $priorityCount.' solicitud(es) fuera de plazo requieren atención.'
            : 'No hay solicitudes fuera de plazo.';
        $module['operacion_ui']['panels'][1]['empty'] = $upcomingCount > 0
            ? $upcomingCount.' vencimiento(s) dentro de los próximos 30 días.'
            : 'No hay vencimientos próximos.';
        $module['operacion_ui']['panels'][2]['empty'] = $resolvedCount > 0
            ? $resolvedCount.' caso(s) resuelto(s) registrados en MariaDB.'
            : 'No hay casos resueltos registrados.';
        $module['operacion_ui']['rows'] = $returns->getCollection()->map(fn (DevolucionGarantia $return): array => $this->tableRow($return))->all();

        return view('gestion.comercial.devoluciones-garantias.listar', compact('module'));
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarDevolucionGarantiaRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $order = Pedido::query()->findOrFail($request->integer('pedido_id'));
        $this->assertOrderInScope($request, $order, 'gestion.devoluciones.crear');

        DB::transaction(function () use ($request, $order, $audit): void {
            $returnRequest = DevolucionGarantia::create($request->validated() + [
                'creado_por_usuario_id' => $request->user()->id,
                'activo' => true,
                'empresa_id' => $order->empresa_id,
                'sede_id' => $order->sede_id,
            ]);
            $audit->record($request->user()->id, 'return.created', 'devoluciones_garantias', $returnRequest->id, null, $returnRequest->only(['codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'estado']), $request->ip());
        }, 3);

        return redirect()->route('management.returns')->with('status', 'Solicitud registrada correctamente.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $devolucionGarantia  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarDevolucionGarantiaRequest $request, DevolucionGarantia $devolucionGarantia, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless($this->authorization->allowsRecord(
            $request->user(),
            'gestion.devoluciones.actualizar',
            is_numeric($devolucionGarantia->empresa_id) ? (int) $devolucionGarantia->empresa_id : null,
            is_numeric($devolucionGarantia->sede_id) ? (int) $devolucionGarantia->sede_id : null,
        ), 403);
        $order = Pedido::query()->findOrFail($request->integer('pedido_id'));
        $this->assertOrderInScope($request, $order, 'gestion.devoluciones.actualizar');

        abort_unless($devolucionGarantia->activo, 404);
        DB::transaction(function () use ($request, $order, $devolucionGarantia, $audit): void {
            $before = $devolucionGarantia->only(['codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'motivo', 'estado', 'fecha_solicitud', 'fecha_limite', 'resolucion']);
            $devolucionGarantia->update($request->validated() + ['empresa_id' => $order->empresa_id, 'sede_id' => $order->sede_id]);
            $audit->record($request->user()->id, 'return.updated', 'devoluciones_garantias', $devolucionGarantia->id, $before, $devolucionGarantia->only(array_keys($before)), $request->ip());
        }, 3);

        return redirect()->route('management.returns')->with('status', 'Solicitud actualizada correctamente.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $devolucionGarantia  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function destroy(Request $request, DevolucionGarantia $devolucionGarantia, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless($this->authorization->allowsRecord(
            $request->user(),
            'gestion.devoluciones.eliminar',
            is_numeric($devolucionGarantia->empresa_id) ? (int) $devolucionGarantia->empresa_id : null,
            is_numeric($devolucionGarantia->sede_id) ? (int) $devolucionGarantia->sede_id : null,
        ), 403);

        abort_unless($devolucionGarantia->activo, 404);
        DB::transaction(function () use ($request, $devolucionGarantia, $audit): void {
            $before = $devolucionGarantia->only(['codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'estado', 'activo']);
            $devolucionGarantia->update(['activo' => false]);
            $audit->record($request->user()->id, 'return.deactivated', 'devoluciones_garantias', $devolucionGarantia->id, $before, ['activo' => false], $request->ip());
        }, 3);

        return redirect()->route('management.returns')->with('status', 'Solicitud desactivada correctamente.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $returns = $this->applyFilters($this->authorization->scopeRecordsFor(
            DevolucionGarantia::query()->where('activo', true),
            $request->user(),
            'gestion.devoluciones.exportar',
        ), $this->filters($request))
            ->with(['customer:id,nombre_contacto,razon_social', 'product:id,nombre', 'sale:id,codigo'])
            ->when($this->filters($request)['orden'] === 'antiguos', fn (Builder $query): Builder => $query->orderBy('fecha_solicitud')->orderBy('id'))
            ->when($this->filters($request)['orden'] !== 'antiguos', fn (Builder $query): Builder => $query->latest('fecha_solicitud')->latest('id'))
            ->get();
        $rows = $returns->map(static fn (DevolucionGarantia $returnRequest): array => [
            $returnRequest->codigo,
            $returnRequest->customer?->nombre_contacto ?: $returnRequest->customer?->razon_social,
            $returnRequest->sale?->codigo,
            $returnRequest->product?->nombre,
            $returnRequest->tipo,
            $returnRequest->estado,
            $returnRequest->fecha_solicitud?->format('d/m/Y'),
            $returnRequest->fecha_limite?->format('d/m/Y'),
        ])->all();
        $response = $exporter->download($format, 'devoluciones-garantias',
            ['Código', 'Cliente', 'Venta', 'Producto', 'Tipo', 'Estado', 'Fecha de solicitud', 'Fecha límite'],
            $rows,
            ['title' => 'REPORTE DE DEVOLUCIONES Y GARANTÍAS', 'module' => 'Operaciones Comerciales', 'report_name' => 'Devoluciones y garantías', 'generated_by' => (string) $request->user()->nombre, 'indicators' => [['label' => 'Solicitudes incluidas', 'value' => count($rows)]], 'column_types' => ['text', 'text', 'text', 'text', 'text', 'text', 'date', 'date']]
        );
        $audit->record($request->user()->getKey(), 'exportar', 'devoluciones_garantias', null, null, ['formato' => $format, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Ejecuta la operación assertOrderInScope definida por la clase DevolucionGarantiaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $order  Parámetro recibido por la operación.
     * @param  mixed  $permission  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function assertOrderInScope(Request $request, Pedido $order, string $permission): void
    {
        abort_unless($this->authorization->allowsRecord(
            $request->user(),
            $permission,
            is_numeric($order->empresa_id) ? (int) $order->empresa_id : null,
            is_numeric($order->sede_id) ? (int) $order->sede_id : null,
        ), 403);

        abort_unless($order->cliente_id !== null && (int) $order->cliente_id === $request->integer('cliente_id'), 422, 'El cliente no corresponde al pedido.');

        if ($request->filled('producto_id')) {
            abort_unless($order->details()->where('producto_id', $request->integer('producto_id'))->exists(), 422, 'El producto no corresponde al pedido.');
        }
    }

    /**
     * @return array{buscar:?string,periodo:string,tipo:?string,estado:?string,desde:CarbonImmutable,hasta:CarbonImmutable}
     */
    private function filters(Request $request): array
    {
        $period = $request->string('periodo')->toString();
        $today = CarbonImmutable::today();
        [$from, $to] = match ($period) {
            'Esta semana' => [$today->startOfWeek(), $today->endOfWeek()],
            'Últimos 90 días' => [$today->subDays(89), $today],
            default => [$today->startOfMonth(), $today],
        };
        $search = trim($request->string('buscar')->toString());
        $type = trim($request->string('tipo')->toString());
        $status = trim($request->string('estado')->toString());
        $order = trim($request->string('orden')->toString());

        return [
            'buscar' => $search !== '' ? $search : null,
            'periodo' => in_array($period, ['Esta semana', 'Este mes', 'Últimos 90 días'], true) ? $period : 'Este mes',
            'tipo' => in_array($type, ['Devolución', 'Garantía', 'Cambio'], true) ? $type : null,
            'estado' => in_array($status, ['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'], true) ? $status : null,
            'orden' => in_array($order, ['recientes', 'antiguos'], true) ? $order : 'recientes',
            'desde' => $from->startOfDay(),
            'hasta' => $to->endOfDay(),
        ];
    }

    /**
     * @param  array{buscar:?string,periodo:string,tipo:?string,estado:?string,desde:CarbonImmutable,hasta:CarbonImmutable}  $filters
     * @return Builder<DevolucionGarantia>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('codigo', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', fn (Builder $customerQuery): Builder => $customerQuery
                            ->where('nombre_contacto', 'like', '%'.$search.'%')
                            ->orWhere('razon_social', 'like', '%'.$search.'%'))
                        ->orWhereHas('product', fn (Builder $productQuery): Builder => $productQuery->where('nombre', 'like', '%'.$search.'%'));

                    if (ctype_digit($search)) {
                        $searchQuery->orWhere('pedido_id', (int) $search);
                    }
                });
            })
            ->when($filters['tipo'], fn (Builder $query, string $type): Builder => $query->where('tipo', $type))
            ->when($filters['estado'], fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->whereBetween('fecha_solicitud', [$filters['desde']->toDateString(), $filters['hasta']->toDateString()]);
    }

    /**
     * @return array{id:int,cells:array<int,string|array{value:string,tone:string}>,record:array<string,mixed>,edit_url:string,delete_url:string}
     */
    private function tableRow(DevolucionGarantia $returnRequest): array
    {
        $record = [
            'id' => $returnRequest->id,
            'codigo' => $returnRequest->codigo,
            'pedido_id' => $returnRequest->pedido_id,
            'cliente_id' => $returnRequest->cliente_id,
            'cliente_nombre' => $returnRequest->customer?->nombre_contacto ?: $returnRequest->customer?->razon_social ?: '—',
            'producto_id' => $returnRequest->producto_id,
            'producto_nombre' => $returnRequest->product?->nombre ?: '—',
            'venta_id' => $returnRequest->venta_id,
            'venta_codigo' => $returnRequest->sale?->codigo ?: '—',
            'tipo' => $returnRequest->tipo,
            'motivo' => $returnRequest->motivo,
            'estado' => $returnRequest->estado,
            'fecha_solicitud' => $returnRequest->fecha_solicitud?->format('Y-m-d'),
            'fecha_solicitud_label' => $returnRequest->fecha_solicitud?->format('d/m/Y') ?: '—',
            'fecha_limite' => $returnRequest->fecha_limite?->format('Y-m-d'),
            'fecha_limite_label' => $returnRequest->fecha_limite?->format('d/m/Y') ?: '—',
            'resolucion' => $returnRequest->resolucion,
        ];

        return [
            'id' => $returnRequest->id,
            'cells' => [
                $returnRequest->codigo,
                $returnRequest->customer?->nombre_contacto ?: $returnRequest->customer?->razon_social ?: '—',
                $returnRequest->sale?->codigo ?: '—',
                $returnRequest->product?->nombre ?: '—',
                ['value' => $returnRequest->tipo, 'tone' => 'blue'],
                Str::limit($returnRequest->motivo, 36),
                $returnRequest->fecha_solicitud?->format('d/m/Y') ?: '—',
                ['value' => $returnRequest->estado, 'tone' => $this->statusTone($returnRequest->estado)],
            ],
            'record' => $record,
            'edit_url' => route('management.returns.update', $returnRequest),
            'delete_url' => route('management.returns.destroy', $returnRequest),
        ];
    }

    /**
     * Ejecuta la operación statusTone definida por la clase DevolucionGarantiaController.
     *
     * @param  mixed  $status  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function statusTone(string $status): string
    {
        return match ($status) {
            'Aprobada', 'Resuelta' => 'green',
            'Rechazada' => 'red',
            'En evaluación' => 'orange',
            default => 'blue',
        };
    }
}
