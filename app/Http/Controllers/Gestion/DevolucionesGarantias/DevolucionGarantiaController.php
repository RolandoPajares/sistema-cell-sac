<?php

namespace App\Http\Controllers\Gestion\DevolucionesGarantias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarDevolucionGarantiaRequest;
use App\Http\Requests\GuardarDevolucionGarantiaRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\DevolucionGarantia;
use App\Models\Comercio\Pedido;
use App\Servicios\RegistradorAuditoria;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DevolucionGarantiaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $returnsQuery = $this->applyFilters(DevolucionGarantia::query()->where('activo', true), $filters);
        $returns = (clone $returnsQuery)
            ->with(['customer:id,nombre_contacto,razon_social', 'product:id,nombre', 'order:id'])
            ->latest('fecha_solicitud')
            ->latest('id')
            ->paginate(8)
            ->withQueryString();
        $allReturns = DevolucionGarantia::query()->where('activo', true);
        $module = require resource_path('views/gestion/devoluciones-garantias/datos.php');
        $module['form_data'] = [
            'customers' => Cliente::query()
                ->where('activo', true)
                ->orderBy('nombre_contacto')
                ->get(['id', 'nombre_contacto', 'razon_social']),
            'orders' => Pedido::query()
                ->with('user:id,nombre')
                ->latest('creado_en')
                ->latest('id')
                ->limit(100)
                ->get(['id', 'usuario_id', 'creado_en']),
            'products' => Producto::query()
                ->active()
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
        ];
        $module['operacion_ui']['backend'] = true;
        $module['operacion_ui']['filter_action'] = route('management.returns');
        $module['operacion_ui']['clear_action'] = route('management.returns');
        $module['operacion_ui']['filter_route'] = 'management.returns';
        $module['operacion_ui']['filter_values'] = $filters;
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
        $module['operacion_ui']['rows'] = $returns->getCollection()->map(fn (DevolucionGarantia $return): array => $this->tableRow($return))->all();

        return view('gestion.devoluciones-garantias.listar', compact('module'));
    }

    public function store(GuardarDevolucionGarantiaRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $returnRequest = DevolucionGarantia::create($request->validated() + [
            'creado_por' => $request->user()->id,
            'activo' => true,
            'creado_en' => now(),
        ]);
        $audit->record($request->user()->id, 'return.created', 'devoluciones_garantias', $returnRequest->id, null, $returnRequest->only(['codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'estado']), $request->ip());

        return redirect()->route('management.returns')->with('status', 'Solicitud registrada correctamente.');
    }

    public function update(ActualizarDevolucionGarantiaRequest $request, DevolucionGarantia $devolucionGarantia, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $devolucionGarantia->only(['codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'motivo', 'estado', 'fecha_solicitud', 'fecha_limite', 'resolucion']);
        $devolucionGarantia->update($request->validated());
        $audit->record($request->user()->id, 'return.updated', 'devoluciones_garantias', $devolucionGarantia->id, $before, $devolucionGarantia->only(array_keys($before)), $request->ip());

        return redirect()->route('management.returns')->with('status', 'Solicitud actualizada correctamente.');
    }

    public function destroy(DevolucionGarantia $devolucionGarantia, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $devolucionGarantia->only(['codigo', 'pedido_id', 'cliente_id', 'producto_id', 'tipo', 'estado']);
        $devolucionGarantia->delete();
        $audit->record(request()->user()->id, 'return.deleted', 'devoluciones_garantias', $devolucionGarantia->id, $before, null, request()->ip());

        return redirect()->route('management.returns')->with('status', 'Solicitud eliminada correctamente.');
    }

    public function export(Request $request): StreamedResponse
    {
        $returns = $this->applyFilters(DevolucionGarantia::query()->where('activo', true), $this->filters($request))
            ->with(['customer:id,nombre_contacto,razon_social', 'product:id,nombre'])
            ->orderBy('fecha_solicitud')
            ->orderBy('id')
            ->lazyById(200, 'id');

        return response()->streamDownload(function () use ($returns): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Código', 'Cliente', 'Producto', 'Tipo', 'Estado', 'Fecha de solicitud', 'Fecha límite']);

            foreach ($returns as $returnRequest) {
                fputcsv($output, [
                    $returnRequest->codigo,
                    $returnRequest->customer?->nombre_contacto ?: $returnRequest->customer?->razon_social,
                    $returnRequest->product?->nombre,
                    $returnRequest->tipo,
                    $returnRequest->estado,
                    $returnRequest->fecha_solicitud?->format('d/m/Y'),
                    $returnRequest->fecha_limite?->format('d/m/Y'),
                ]);
            }

            fclose($output);
        }, 'devoluciones-garantias.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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

        return [
            'buscar' => $search !== '' ? $search : null,
            'periodo' => in_array($period, ['Esta semana', 'Este mes', 'Últimos 90 días'], true) ? $period : 'Este mes',
            'tipo' => in_array($type, ['Devolución', 'Garantía', 'Cambio'], true) ? $type : null,
            'estado' => in_array($status, ['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'], true) ? $status : null,
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
