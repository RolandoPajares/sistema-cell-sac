<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnularOperacionFinancieraRequest;
use App\Http\Requests\AprobarReembolsoRequest;
use App\Http\Requests\ConfirmarPagoEfectivoRequest;
use App\Http\Requests\FiltroPagosRequest;
use App\Http\Requests\FiltroReembolsosRequest;
use App\Http\Requests\RegistrarPagoRequest;
use App\Http\Requests\SolicitarReembolsoRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Finanzas\Pago;
use App\Models\Finanzas\Reembolso;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioConsultasFinancieras;
use App\Servicios\ServicioOperacionesFinancieras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanzasController extends Controller
{
    private const PAGE_SIZE = 25;

    private const MAX_EXPORT_ROWS = 5000;

    public function pagos(FiltroPagosRequest $request, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization): View
    {
        $filters = $request->validated();
        $query = $financialQueries->pagos($request->user(), $filters);
        $stats = (clone $query)->selectRaw('COUNT(*) AS cantidad')
            ->selectRaw("SUM(CASE WHEN tipo = 'cobro' THEN 1 ELSE 0 END) AS cobros")
            ->selectRaw("SUM(CASE WHEN tipo = 'pago' THEN 1 ELSE 0 END) AS pagos")
            ->first();
        $amountsByCurrency = (clone $query)->select('moneda')->selectRaw('COALESCE(SUM(importe), 0) AS importe_total')->groupBy('moneda')->get();
        $rows = (clone $query)->orderByDesc('fecha_pago')->orderByDesc('id')->paginate(self::PAGE_SIZE)->withQueryString();
        $baseQuery = $financialQueries->pagos($request->user(), []);
        $canCreate = $authorization->allows($request->user(), 'gestion.pagos.crear');

        return view('gestion.pagos.listar', [
            'module' => [
                'key' => 'pagos',
                'area' => 'Finanzas',
                'title' => 'Pagos y cobros',
                'description' => 'Consulta pagos y cobros registrados con su sede, medio de pago e importe.',
                'icon' => 'bi-credit-card',
                'record_singular' => 'pago',
                'record_plural' => 'pagos y cobros',
                'connected' => true,
                'mode' => 'consult',
                'route' => 'management.payments',
                'export_route' => $authorization->allows($request->user(), 'gestion.pagos.exportar') ? 'management.payments.export' : null,
                'create_route' => $canCreate ? 'management.payments.store' : null,
                'form_options' => $canCreate ? $financialQueries->opcionesPago($request->user()) : [],
                'request_token' => (string) Str::uuid(),
                'export_label' => 'Exportar CSV',
                'filters' => $filters + ['tipo' => null, 'estado' => null, 'desde' => null, 'hasta' => null, 'buscar' => null],
                'filter_options' => [
                    'types' => ['cobro' => 'Cobros', 'pago' => 'Pagos', 'reembolso' => 'Reembolsos'],
                    'statuses' => (clone $baseQuery)->distinct()->orderBy('estado')->pluck('estado')->all(),
                ],
                'kpis' => [
                    $this->metric('Registros filtrados', (int) ($stats->cantidad ?? 0), 'bi-receipt', 'blue', 'Según los filtros aplicados'),
                    $this->metric('Importes registrados', $this->moneyTotals($amountsByCurrency), 'bi-cash-coin', 'green', 'Separados por moneda; no equivale a conciliación bancaria'),
                    $this->metric('Cobros', (int) ($stats->cobros ?? 0), 'bi-arrow-down-circle', 'blue', 'Tipo registrado en pagos'),
                    $this->metric('Pagos', (int) ($stats->pagos ?? 0), 'bi-arrow-up-circle', 'orange', 'Tipo registrado en pagos'),
                ],
                'summary' => $this->stateSummary((clone $baseQuery)->select('estado')->selectRaw('COUNT(*) AS cantidad')->groupBy('estado')->orderBy('estado')->get()),
                'columns' => ['Código', 'Tercero asociado', 'Origen', 'Tipo', 'Medio de pago', 'Importe', 'Estado', 'Fecha del registro'],
                'records' => $rows->getCollection()->map(fn (Pago $payment): array => [
                    'cells' => $this->paymentCells($payment),
                    'detail_url' => route('management.payments.show', $payment),
                ])->all(),
                'pagination' => $rows,
                'total_count' => $rows->total(),
                'table_title' => 'Pagos registrados',
            ],
        ]);
    }

    public function reembolsos(FiltroReembolsosRequest $request, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization): View
    {
        $filters = $request->validated();
        $query = $financialQueries->reembolsos($request->user(), $filters);
        $stats = (clone $query)->selectRaw('COUNT(*) AS cantidad')
            ->selectRaw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendientes")
            ->first();
        $amountsByCurrency = (clone $query)
            ->join('pagos as pagos_reembolso', 'pagos_reembolso.id', '=', 'reembolsos.pago_id')
            ->select('pagos_reembolso.moneda')
            ->selectRaw('COALESCE(SUM(reembolsos.importe), 0) AS importe_total')
            ->groupBy('pagos_reembolso.moneda')
            ->get();
        $rows = (clone $query)->orderByDesc('creado_en')->orderByDesc('id')->paginate(self::PAGE_SIZE)->withQueryString();
        $baseQuery = $financialQueries->reembolsos($request->user(), []);
        $canCreate = $authorization->allows($request->user(), 'gestion.reembolsos.crear');

        return view('gestion.reembolsos.listar', [
            'module' => [
                'key' => 'reembolsos',
                'area' => 'Finanzas',
                'title' => 'Reembolsos',
                'description' => 'Consulta los reembolsos registrados y sus devoluciones y pagos asociados.',
                'icon' => 'bi-arrow-repeat',
                'record_singular' => 'reembolso',
                'record_plural' => 'reembolsos',
                'connected' => true,
                'mode' => 'consult',
                'route' => 'management.refunds',
                'export_route' => $authorization->allows($request->user(), 'gestion.reembolsos.exportar') ? 'management.refunds.export' : null,
                'create_route' => $canCreate ? 'management.refunds.store' : null,
                'form_options' => $canCreate ? $financialQueries->opcionesReembolso($request->user()) : [],
                'export_label' => 'Exportar CSV',
                'filters' => $filters + ['estado' => null, 'desde' => null, 'hasta' => null, 'buscar' => null],
                'filter_options' => [
                    'types' => [],
                    'statuses' => (clone $baseQuery)->distinct()->orderBy('estado')->pluck('estado')->all(),
                ],
                'kpis' => [
                    $this->metric('Reembolsos filtrados', (int) ($stats->cantidad ?? 0), 'bi-inbox', 'blue', 'Según los filtros aplicados'),
                    $this->metric('Importes registrados', $this->moneyTotals($amountsByCurrency), 'bi-cash-coin', 'orange', 'Separados por moneda; no confirma la ejecución del pago'),
                    $this->metric('Pendientes', (int) ($stats->pendientes ?? 0), 'bi-hourglass-split', 'orange', 'Estado pendiente registrado'),
                    $this->metric('Estados distintos', (clone $baseQuery)->distinct('estado')->count('estado'), 'bi-list-check', 'purple', 'Estados presentes en la tabla'),
                ],
                'summary' => $this->stateSummary((clone $baseQuery)->select('estado')->selectRaw('COUNT(*) AS cantidad')->groupBy('estado')->orderBy('estado')->get()),
                'columns' => ['ID', 'Devolución', 'Cliente', 'Pago asociado', 'Importe', 'Estado', 'Fecha de registro'],
                'records' => $rows->getCollection()->map(fn (Reembolso $refund): array => [
                    'cells' => $this->refundCells($refund),
                    'detail_url' => route('management.refunds.show', $refund),
                ])->all(),
                'pagination' => $rows,
                'total_count' => $rows->total(),
                'table_title' => 'Reembolsos registrados',
            ],
        ]);
    }

    public function registrarPago(RegistrarPagoRequest $request, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $payment = $operations->registrarPago($request->validated(), $request->user(), $request->ip());

        return redirect()->route('management.payments.show', $payment)
            ->with('status', 'Solicitud registrada como pendiente. No se ha confirmado ningún cobro ni pago.');
    }

    public function detallePago(Request $request, Pago $pago, ServicioAutorizacion $authorization): View
    {
        abort_unless($authorization->allowsRecord($request->user(), 'gestion.pagos.consultar', (int) $pago->empresa_id, (int) $pago->sede_id), 404);
        $pago->load(['medioPago:id,nombre,requiere_caja,requiere_banco', 'sesionCaja:id,caja_id,estado',
            'cuentaBancaria:id,codigo,banco,moneda', 'usuario:id,nombre', 'pedido:id', 'venta:id,codigo', 'compra:id,numero_referencia']);
        $history = RegistroAuditoria::query()->where('entidad', 'pagos')->where('entidad_id', $pago->getKey())
            ->with('user:id,nombre')->orderBy('id')->get();

        return view('gestion.pagos.detalle', [
            'record' => $pago,
            'history' => $history,
            'canCancel' => $pago->estado === 'pendiente'
                && $authorization->allowsRecord($request->user(), 'gestion.pagos.anular', (int) $pago->empresa_id, (int) $pago->sede_id),
            'canConfirmCash' => $pago->estado === 'pendiente' && $pago->medioPago?->requiere_caja
                && $pago->sesionCaja?->estado === 'abierta'
                && $authorization->hasRole($request->user(), 'administrador')
                && $authorization->allowsRecord($request->user(), 'gestion.pagos.actualizar',
                    (int) $pago->empresa_id, (int) $pago->sede_id),
        ]);
    }

    public function confirmarPagoEfectivo(ConfirmarPagoEfectivoRequest $request, Pago $pago, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $data = $request->validated();
        $operations->confirmarPagoEfectivo($pago, $data['importe_contado'], $data['observacion'],
            $request->user(), $request->ip());

        return redirect()->route('management.payments.show', $pago)
            ->with('status', 'Efectivo confirmado y movimiento de caja registrado.');
    }

    public function anularPago(AnularOperacionFinancieraRequest $request, Pago $pago, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $operations->anularPago($pago, $request->user(), $request->validated('motivo'), $request->ip());

        return redirect()->route('management.payments.show', $pago)->with('status', 'Solicitud pendiente anulada.');
    }

    public function solicitarReembolso(SolicitarReembolsoRequest $request, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $refund = $operations->solicitarReembolso($request->validated(), $request->user(), $request->ip());

        return redirect()->route('management.refunds.show', $refund)
            ->with('status', 'Solicitud de reembolso registrada. No se ha enviado dinero.');
    }

    public function detalleReembolso(Request $request, Reembolso $reembolso, ServicioAutorizacion $authorization): View
    {
        $reembolso->load(['pago:id,empresa_id,sede_id,codigo,moneda,importe,estado,referencia', 'devolucion:id,codigo,estado,resolucion', 'autorizadoPor:id,nombre']);
        $payment = $reembolso->pago;
        abort_unless($payment !== null && $authorization->allowsRecord($request->user(), 'gestion.reembolsos.consultar',
            (int) $payment->empresa_id, (int) $payment->sede_id), 404);
        $history = RegistroAuditoria::query()->where('entidad', 'reembolsos')->where('entidad_id', $reembolso->getKey())
            ->with('user:id,nombre')->orderBy('id')->get();

        return view('gestion.reembolsos.detalle', [
            'record' => $reembolso,
            'history' => $history,
            'canApprove' => $reembolso->estado === 'pendiente'
                && $authorization->allowsRecord($request->user(), 'gestion.reembolsos.aprobar', (int) $payment->empresa_id, (int) $payment->sede_id),
            'canCancel' => $reembolso->estado === 'pendiente'
                && $authorization->allowsRecord($request->user(), 'gestion.reembolsos.anular', (int) $payment->empresa_id, (int) $payment->sede_id),
        ]);
    }

    public function aprobarReembolso(AprobarReembolsoRequest $request, Reembolso $reembolso, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $operations->aprobarReembolso($reembolso, $request->user(), $request->ip());

        return redirect()->route('management.refunds.show', $reembolso)
            ->with('status', 'Solicitud aprobada para gestión. No se ha ejecutado el reembolso.');
    }

    public function anularReembolso(AnularOperacionFinancieraRequest $request, Reembolso $reembolso, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $operations->anularReembolso($reembolso, $request->user(), $request->validated('motivo'), $request->ip());

        return redirect()->route('management.refunds.show', $reembolso)->with('status', 'Solicitud pendiente anulada.');
    }

    public function exportarPagos(FiltroPagosRequest $request, string $format, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization, ExportadorTabular $exporter, RegistradorAuditoria $auditor): Response|StreamedResponse
    {
        $authorization->assertAllowed($request->user(), 'gestion.pagos.exportar');
        abort_unless($format === 'csv', 404);

        return $this->export(
            $request->user(), $request->validated(), 'pagos',
            $financialQueries->pagos($request->user(), $request->validated()),
            ['Código', 'Tercero asociado', 'Origen', 'Tipo', 'Medio de pago', 'Importe', 'Moneda', 'Estado', 'Fecha de pago'],
            fn (Pago $payment): array => $this->paymentExportCells($payment, $format),
            $format, $exporter, $auditor, $request->ip(),
        );
    }

    public function exportarReembolsos(FiltroReembolsosRequest $request, string $format, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization, ExportadorTabular $exporter, RegistradorAuditoria $auditor): Response|StreamedResponse
    {
        $authorization->assertAllowed($request->user(), 'gestion.reembolsos.exportar');
        abort_unless($format === 'csv', 404);

        return $this->export(
            $request->user(), $request->validated(), 'reembolsos',
            $financialQueries->reembolsos($request->user(), $request->validated()),
            ['ID', 'Devolución', 'Cliente', 'Pago asociado', 'Importe', 'Moneda', 'Estado', 'Fecha de registro'],
            fn (Reembolso $refund): array => $this->refundExportCells($refund, $format),
            $format, $exporter, $auditor, $request->ip(),
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    private function export(User $actor, array $filters, string $entity, Builder $query, array $columns, callable $map, string $format, ExportadorTabular $exporter, RegistradorAuditoria $auditor, ?string $ip): Response|StreamedResponse
    {
        abort_if((clone $query)->limit(self::MAX_EXPORT_ROWS + 1)->pluck('id')->count() > self::MAX_EXPORT_ROWS, 422, 'El conjunto supera el límite de exportación. Reduce los filtros.');
        $rows = $query->orderBy('id')->get()->map($map)->all();
        $report = [
            'title' => 'REPORTE DE '.mb_strtoupper($entity),
            'subtitle' => 'Registros financieros existentes en el período filtrado.',
            'module' => 'Finanzas / '.$entity,
            'report_name' => 'Reporte de '.$entity,
            'filters' => $filters,
            'generated_by' => (string) $actor->nombre,
            'role' => 'Usuario autorizado',
            'scope' => 'Registros filtrados por el alcance RBAC vigente.',
            'indicators' => [['label' => 'Registros incluidos', 'value' => count($rows)]],
            'summary_row' => ['label' => 'Registros incluidos: '.count($rows), 'values' => []],
        ];
        $response = $exporter->download($format, $entity.'-'.now()->format('Ymd'), $columns, $rows, $report);

        $auditor->record((int) $actor->getKey(), 'exportar', $entity, null, null, [
            'formato' => $format,
            'filtros' => $filters,
            'cantidad' => count($rows),
        ], $ip);

        return $response;
    }

    /** @return array<int, string|int> */
    private function paymentCells(Pago $payment): array
    {
        return [
            $payment->codigo,
            $this->paymentCounterparty($payment),
            $this->paymentOrigin($payment),
            $payment->tipo,
            $payment->medioPago?->nombre ?? 'Sin medio asociado',
            $this->money((float) $payment->importe, $payment->moneda),
            $payment->estado,
            $payment->fecha_pago?->format('d/m/Y H:i') ?? '',
        ];
    }

    /** @return array<int, string|int> */
    private function paymentExportCells(Pago $payment, string $format): array
    {
        return [
            self::safeExportText((string) $payment->codigo, $format),
            self::safeExportText($this->paymentCounterparty($payment), $format),
            self::safeExportText($this->paymentOrigin($payment), $format),
            $payment->tipo,
            self::safeExportText((string) ($payment->medioPago?->nombre ?? ''), $format),
            number_format((float) $payment->importe, 2, '.', ''),
            $payment->moneda,
            self::safeExportText((string) $payment->estado, $format),
            $payment->fecha_pago?->format('Y-m-d H:i') ?? '',
        ];
    }

    private function paymentCounterparty(Pago $payment): string
    {
        if ($payment->tipo === 'pago') {
            return (string) ($payment->compra?->supplier?->nombre ?? 'Proveedor no asociado');
        }

        return (string) ($payment->venta?->cliente?->nombre_contacto
            ?? $payment->venta?->cliente?->razon_social
            ?? $payment->pedido?->user?->nombre
            ?? 'Sin cliente asociado');
    }

    private function paymentOrigin(Pago $payment): string
    {
        return match (true) {
            $payment->venta !== null => (string) $payment->venta->codigo,
            $payment->pedido !== null => 'Pedido #'.$payment->pedido_id,
            $payment->compra !== null => (string) ($payment->compra->numero_referencia ?: 'Compra #'.$payment->compra_id),
            default => 'Sin documento asociado',
        };
    }

    /** @return array<int, string|int> */
    private function refundCells(Reembolso $refund): array
    {
        return [
            (int) $refund->getKey(),
            $refund->devolucion?->codigo ?? 'Devolución no asociada',
            $refund->devolucion?->customer?->nombre_contacto ?? $refund->devolucion?->customer?->razon_social ?? 'Sin cliente asociado',
            $refund->pago?->codigo ?? 'Pago no asociado',
            $this->money((float) $refund->importe, $refund->pago?->moneda ?? 'PEN'),
            $refund->estado,
            $refund->creado_en?->format('d/m/Y H:i') ?? '',
        ];
    }

    /** @return array<int, string|int> */
    private function refundExportCells(Reembolso $refund, string $format): array
    {
        return [
            (int) $refund->getKey(),
            self::safeExportText((string) ($refund->devolucion?->codigo ?? ''), $format),
            self::safeExportText((string) ($refund->devolucion?->customer?->nombre_contacto ?? $refund->devolucion?->customer?->razon_social ?? ''), $format),
            self::safeExportText((string) ($refund->pago?->codigo ?? ''), $format),
            number_format((float) $refund->importe, 2, '.', ''),
            $refund->pago?->moneda ?? 'PEN',
            self::safeExportText((string) $refund->estado, $format),
            $refund->creado_en?->format('Y-m-d H:i') ?? '',
        ];
    }

    /** @return array{label:string,value:string|int,icon:string,tone:string,helper:string} */
    private function metric(string $label, string|int $value, string $icon, string $tone, string $helper): array
    {
        return compact('label', 'value', 'icon', 'tone', 'helper');
    }

    /** @return array<int, array{title:string,icon:string,tone:string,text:string}> */
    private function stateSummary(Collection $states): array
    {
        return $states->map(static fn (object $state): array => [
            'title' => (string) $state->estado,
            'icon' => 'bi-list-check',
            'tone' => 'blue',
            'text' => number_format((int) $state->cantidad).' registros',
        ])->values()->all();
    }

    private function money(float $amount, string $currency): string
    {
        return $currency.' '.number_format($amount, 2, '.', ',');
    }

    private function moneyTotals(Collection $totals): string
    {
        if ($totals->isEmpty()) {
            return '—';
        }

        return $totals->map(fn (object $total): string => $this->money((float) $total->importe_total, (string) $total->moneda))
            ->implode(' · ');
    }

    private static function safeExportText(string $value, string $format): string
    {
        $clean = trim(preg_replace('/[\p{C}\p{Z}]+/u', ' ', $value) ?? '');
        if ($format === 'pdf') {
            $clean = mb_convert_encoding(mb_convert_encoding($clean, 'Windows-1252', 'UTF-8'), 'UTF-8', 'Windows-1252');
        }

        return preg_match('/^[=+\-@]/u', $clean) === 1 ? "'".$clean : $clean;
    }
}
