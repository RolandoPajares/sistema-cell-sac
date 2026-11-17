<?php

namespace App\Http\Controllers\Gestion\Finanzas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Finanzas\AnularOperacionFinancieraRequest;
use App\Http\Requests\Gestion\Finanzas\PagosCobros\ConfirmarPagoEfectivoRequest;
use App\Http\Requests\Gestion\Finanzas\PagosCobros\FiltroPagosRequest;
use App\Http\Requests\Gestion\Finanzas\PagosCobros\RegistrarPagoRequest;
use App\Http\Requests\Gestion\Finanzas\Reembolsos\AprobarReembolsoRequest;
use App\Http\Requests\Gestion\Finanzas\Reembolsos\FiltroReembolsosRequest;
use App\Http\Requests\Gestion\Finanzas\Reembolsos\SolicitarReembolsoRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Finanzas\Pago;
use App\Models\Finanzas\Reembolso;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Finanzas\ServicioConsultasFinancieras;
use App\Servicios\Gestion\Finanzas\ServicioOperacionesFinancieras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase FinanzasController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FinanzasController extends Controller
{
    private const PAGE_SIZE = 25;

    private const MAX_EXPORT_ROWS = 5000;

    /**
     * Ejecuta la operación pagos definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $financialQueries  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function pagos(FiltroPagosRequest $request, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization): View
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $request->validated();
        // Obtiene en $query la informacion de la solicitud o del usuario actual.
        $query = $financialQueries->pagos($request->user(), $filters);
        // Guarda en $stats el resultado necesario para continuar esta operacion.
        $stats = (clone $query)->selectRaw('COUNT(*) AS cantidad')
            ->selectRaw("SUM(CASE WHEN tipo = 'cobro' THEN 1 ELSE 0 END) AS cobros")
            ->selectRaw("SUM(CASE WHEN tipo = 'pago' THEN 1 ELSE 0 END) AS pagos")
            ->first();
        // Guarda en $amountsByCurrency el resultado necesario para continuar esta operacion.
        $amountsByCurrency = (clone $query)->select('moneda')->selectRaw('COALESCE(SUM(importe), 0) AS importe_total')->groupBy('moneda')->get();
        // Guarda en $rows el resultado necesario para continuar esta operacion.
        $rows = (clone $query)->orderByDesc('fecha_pago')->orderByDesc('id')->paginate(self::PAGE_SIZE)->withQueryString();
        // Obtiene en $baseQuery la informacion de la solicitud o del usuario actual.
        $baseQuery = $financialQueries->pagos($request->user(), []);
        // Obtiene en $canCreate la informacion de la solicitud o del usuario actual.
        $canCreate = $authorization->allows($request->user(), 'gestion.pagos.crear');
        // Obtiene en $formOptions la informacion de la solicitud o del usuario actual.
        $formOptions = $canCreate ? $financialQueries->opcionesPago($request->user()) : [];
        // Guarda en $canSubmit el resultado necesario para continuar esta operacion.
        $canSubmit = ($formOptions['documentos'] ?? collect())->isNotEmpty()
            && ($formOptions['medios'] ?? collect())->isNotEmpty();

        // Devuelve la vista 'gestion.finanzas.pagos-cobros.listar' con los datos preparados por este controlador.
        return view('gestion.finanzas.pagos-cobros.listar', [
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
                'form_options' => $formOptions,
                'can_submit' => $canSubmit,
                'export_parameters' => array_filter($filters, static fn (mixed $value): bool => $value !== null && $value !== ''),
                'request_token' => (string) Str::uuid(),
                'export_label' => 'Exportar',
                'filters' => $filters + ['tipo' => null, 'estado' => null, 'desde' => null, 'hasta' => null, 'buscar' => null],
                'filter_options' => [
                    'types' => ['cobro' => 'Cobros', 'pago' => 'Pagos', 'reembolso' => 'Reembolsos'],
                    'statuses' => (clone $baseQuery)->distinct()->orderBy('estado')->pluck('estado')->all(),
                ],
                'kpis' => [
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Registros filtrados', (int) ($stats->cantidad ?? 0), 'bi-receipt', 'blue', 'Según los filtros aplicados'),
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Importes registrados', $this->totalesMonetarios($amountsByCurrency), 'bi-cash-coin', 'green', 'Separados por moneda; no equivale a conciliación bancaria'),
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Cobros', (int) ($stats->cobros ?? 0), 'bi-arrow-down-circle', 'blue', 'Tipo registrado en pagos'),
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Pagos', (int) ($stats->pagos ?? 0), 'bi-arrow-up-circle', 'orange', 'Tipo registrado en pagos'),
                ],
                'summary' => $this->resumenEstados((clone $baseQuery)->select('estado')->selectRaw('COUNT(*) AS cantidad')->groupBy('estado')->orderBy('estado')->get()),
                'columns' => ['Código', 'Tercero asociado', 'Origen', 'Tipo', 'Medio de pago', 'Importe', 'Estado', 'Fecha del registro'],
                'records' => $rows->getCollection()->map(fn (Pago $payment): array => [
                    'cells' => $this->celdasPago($payment),
                    'detail_url' => route('management.payments.show', $payment),
                ])->all(),
                'pagination' => $rows,
                'total_count' => $rows->total(),
                'table_title' => 'Pagos registrados',
            ],
        ]);
    }

    /**
     * Ejecuta la operación reembolsos definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $financialQueries  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function reembolsos(FiltroReembolsosRequest $request, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization): View
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $request->validated();
        // Obtiene en $query la informacion de la solicitud o del usuario actual.
        $query = $financialQueries->reembolsos($request->user(), $filters);
        // Guarda en $stats el resultado necesario para continuar esta operacion.
        $stats = (clone $query)->selectRaw('COUNT(*) AS cantidad')
            ->selectRaw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendientes")
            ->first();
        // Guarda en $amountsByCurrency el resultado necesario para continuar esta operacion.
        $amountsByCurrency = (clone $query)
            ->join('pagos as pagos_reembolso', 'pagos_reembolso.id', '=', 'reembolsos.pago_id')
            ->select('pagos_reembolso.moneda')
            ->selectRaw('COALESCE(SUM(reembolsos.importe), 0) AS importe_total')
            ->groupBy('pagos_reembolso.moneda')
            ->get();
        // Guarda en $rows el resultado necesario para continuar esta operacion.
        $rows = (clone $query)->orderByDesc('creado_en')->orderByDesc('id')->paginate(self::PAGE_SIZE)->withQueryString();
        // Obtiene en $baseQuery la informacion de la solicitud o del usuario actual.
        $baseQuery = $financialQueries->reembolsos($request->user(), []);
        // Obtiene en $canCreate la informacion de la solicitud o del usuario actual.
        $canCreate = $authorization->allows($request->user(), 'gestion.reembolsos.crear');

        // Devuelve la vista 'gestion.finanzas.reembolsos.listar' con los datos preparados por este controlador.
        return view('gestion.finanzas.reembolsos.listar', [
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
                'export_label' => 'Exportar',
                'filters' => $filters + ['estado' => null, 'desde' => null, 'hasta' => null, 'buscar' => null],
                'filter_options' => [
                    'types' => [],
                    'statuses' => (clone $baseQuery)->distinct()->orderBy('estado')->pluck('estado')->all(),
                ],
                'kpis' => [
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Reembolsos filtrados', (int) ($stats->cantidad ?? 0), 'bi-inbox', 'blue', 'Según los filtros aplicados'),
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Importes registrados', $this->totalesMonetarios($amountsByCurrency), 'bi-cash-coin', 'orange', 'Separados por moneda; no confirma la ejecución del pago'),
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Pendientes', (int) ($stats->pendientes ?? 0), 'bi-hourglass-split', 'orange', 'Estado pendiente registrado'),
                    // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
                    $this->metrica('Estados distintos', (clone $baseQuery)->distinct('estado')->count('estado'), 'bi-list-check', 'purple', 'Estados presentes en la tabla'),
                ],
                'summary' => $this->resumenEstados((clone $baseQuery)->select('estado')->selectRaw('COUNT(*) AS cantidad')->groupBy('estado')->orderBy('estado')->get()),
                'columns' => ['ID', 'Devolución', 'Cliente', 'Pago asociado', 'Importe', 'Estado', 'Fecha de registro'],
                'records' => $rows->getCollection()->map(fn (Reembolso $refund): array => [
                    'cells' => $this->celdasReembolso($refund),
                    'detail_url' => route('management.refunds.show', $refund),
                ])->all(),
                'pagination' => $rows,
                'total_count' => $rows->total(),
                'table_title' => 'Reembolsos registrados',
            ],
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $operations  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function registrarPago(RegistrarPagoRequest $request, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $payment = $operations->registrarPago($request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.show', $payment)
            ->with('status', 'Solicitud registrada como pendiente. No se ha confirmado ningún cobro ni pago.');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $pago  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function detallePago(Request $request, Pago $pago, ServicioAutorizacion $authorization): View
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($authorization->allowsRecord($request->user(), 'gestion.pagos.consultar', (int) $pago->empresa_id, (int) $pago->sede_id), 404);
        $pago->load(['medioPago:id,nombre,requiere_caja,requiere_banco', 'sesionCaja:id,caja_id,estado',
            'cuentaBancaria:id,codigo,banco,moneda', 'usuario:id,nombre', 'pedido:id', 'venta:id,codigo', 'compra:id,numero_referencia']);
        // Prepara en $history la consulta que obtiene o modifica los registros necesarios.
        $history = RegistroAuditoria::query()->where('entidad', 'pagos')->where('entidad_id', $pago->getKey())
            ->with('user:id,nombre')->orderBy('id')->get();

        // Devuelve la vista 'gestion.finanzas.pagos-cobros.detalle' con los datos preparados por este controlador.
        return view('gestion.finanzas.pagos-cobros.detalle', [
            'record' => $pago,
            'history' => $history,
            'canCancel' => $pago->estado === 'pendiente'
                && $authorization->allowsRecord($request->user(), 'gestion.pagos.anular', (int) $pago->empresa_id, (int) $pago->sede_id),
            'canConfirmCash' => $pago->estado === 'pendiente' && $pago->medioPago?->requiere_caja
                && $pago->sesionCaja?->estado === 'abierta'
                && $authorization->allowsGlobal($request->user(), 'gestion.seguridad.consultar')
                && $authorization->allowsRecord($request->user(), 'gestion.pagos.actualizar',
                    (int) $pago->empresa_id, (int) $pago->sede_id),
        ]);
    }

    /**
     * Ejecuta la operación confirmarPagoEfectivo definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $pago  Parámetro recibido por la operación.
     * @param  mixed  $operations  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function confirmarPagoEfectivo(ConfirmarPagoEfectivoRequest $request, Pago $pago, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        $operations->confirmarPagoEfectivo($pago, $data['importe_contado'], $data['observacion'],
            $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.show', $pago)
            ->with('status', 'Efectivo confirmado y movimiento de caja registrado.');
    }

    /**
     * Ejecuta la operación anularPago definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $pago  Parámetro recibido por la operación.
     * @param  mixed  $operations  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function anularPago(AnularOperacionFinancieraRequest $request, Pago $pago, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $operations->anularPago($pago, $request->user(), $request->validated('motivo'), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.show', $pago)->with('status', 'Solicitud pendiente anulada.');
    }

    /**
     * Ejecuta la operación solicitarReembolso definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $operations  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function solicitarReembolso(SolicitarReembolsoRequest $request, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $refund = $operations->solicitarReembolso($request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.refunds.show', $refund)
            ->with('status', 'Solicitud de reembolso registrada. No se ha enviado dinero.');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $reembolso  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function detalleReembolso(Request $request, Reembolso $reembolso, ServicioAutorizacion $authorization): View
    {
        $reembolso->load(['pago:id,empresa_id,sede_id,codigo,moneda,importe,estado,referencia', 'devolucion:id,codigo,estado,resolucion', 'autorizadoPor:id,nombre']);
        // Guarda en $payment el resultado necesario para continuar esta operacion.
        $payment = $reembolso->pago;
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($payment !== null && $authorization->allowsRecord($request->user(), 'gestion.reembolsos.consultar',
            (int) $payment->empresa_id, (int) $payment->sede_id), 404);
        // Prepara en $history la consulta que obtiene o modifica los registros necesarios.
        $history = RegistroAuditoria::query()->where('entidad', 'reembolsos')->where('entidad_id', $reembolso->getKey())
            ->with('user:id,nombre')->orderBy('id')->get();

        // Devuelve la vista 'gestion.finanzas.reembolsos.detalle' con los datos preparados por este controlador.
        return view('gestion.finanzas.reembolsos.detalle', [
            'record' => $reembolso,
            'history' => $history,
            'canApprove' => $reembolso->estado === 'pendiente'
                && $authorization->allowsRecord($request->user(), 'gestion.reembolsos.aprobar', (int) $payment->empresa_id, (int) $payment->sede_id),
            'canCancel' => $reembolso->estado === 'pendiente'
                && $authorization->allowsRecord($request->user(), 'gestion.reembolsos.anular', (int) $payment->empresa_id, (int) $payment->sede_id),
        ]);
    }

    /**
     * Ejecuta la operación aprobarReembolso definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $reembolso  Parámetro recibido por la operación.
     * @param  mixed  $operations  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function aprobarReembolso(AprobarReembolsoRequest $request, Reembolso $reembolso, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        $operations->aprobarReembolso($reembolso, $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.refunds.show', $reembolso)
            ->with('status', 'Solicitud aprobada para gestión. No se ha ejecutado el reembolso.');
    }

    /**
     * Ejecuta la operación anularReembolso definida por la clase FinanzasController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $reembolso  Parámetro recibido por la operación.
     * @param  mixed  $operations  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function anularReembolso(AnularOperacionFinancieraRequest $request, Reembolso $reembolso, ServicioOperacionesFinancieras $operations): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $operations->anularReembolso($reembolso, $request->user(), $request->validated('motivo'), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.refunds.show', $reembolso)->with('status', 'Solicitud pendiente anulada.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $financialQueries  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $auditor  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function exportarPagos(FiltroPagosRequest $request, string $format, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization, ExportadorTabular $exporter, RegistradorAuditoria $auditor): Response|StreamedResponse
    {
        $authorization->assertAllowed($request->user(), 'gestion.pagos.exportar');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');

        return $this->exportarRegistros(
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $request->user(), $request->validated(), 'pagos',
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $financialQueries->pagos($request->user(), $request->validated()),
            ['Código', 'Tercero asociado', 'Origen', 'Tipo', 'Medio de pago', 'Importe', 'Moneda', 'Estado', 'Fecha de pago'],
            fn (Pago $payment): array => $this->celdasExportacionPago($payment, $format),
            $format, $exporter, $auditor, $request->ip(),
        );
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $financialQueries  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $auditor  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function exportarReembolsos(FiltroReembolsosRequest $request, string $format, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization, ExportadorTabular $exporter, RegistradorAuditoria $auditor): Response|StreamedResponse
    {
        $authorization->assertAllowed($request->user(), 'gestion.reembolsos.exportar');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');

        return $this->exportarRegistros(
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $request->user(), $request->validated(), 'reembolsos',
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $financialQueries->reembolsos($request->user(), $request->validated()),
            ['ID', 'Devolución', 'Cliente', 'Pago asociado', 'Importe', 'Moneda', 'Estado', 'Fecha de registro'],
            fn (Reembolso $refund): array => $this->celdasExportacionReembolso($refund, $format),
            $format, $exporter, $auditor, $request->ip(),
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    private function exportarRegistros(User $actor, array $filters, string $entity, Builder $query, array $columns, callable $map, string $format, ExportadorTabular $exporter, RegistradorAuditoria $auditor, ?string $ip): Response|StreamedResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if((clone $query)->limit(self::MAX_EXPORT_ROWS + 1)->pluck('id')->count() > self::MAX_EXPORT_ROWS, 422, 'El conjunto supera el límite de exportación. Reduce los filtros.');
        // Guarda en $rows el resultado necesario para continuar esta operacion.
        $rows = $query->orderBy('id')->get()->map($map)->all();
        // Agrupa en $report los datos que se utilizaran en la siguiente operacion.
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
        // Guarda en $response el resultado necesario para continuar esta operacion.
        $response = $exporter->download($format, $entity.'-'.now()->format('Ymd'), $columns, $rows, $report);

        $auditor->record((int) $actor->getKey(), 'exportar', $entity, null, null, [
            'formato' => $format,
            'filtros' => $filters,
            'cantidad' => count($rows),
        ], $ip);

        return $response;
    }

    /** @return array<int, string|int> */
    private function celdasPago(Pago $payment): array
    {
        return [
            $payment->codigo,
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->contrapartePago($payment),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->origenPago($payment),
            $payment->tipo,
            $payment->medioPago?->nombre ?? 'Sin medio asociado',
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->formatearDinero((float) $payment->importe, $payment->moneda),
            $payment->estado,
            $payment->fecha_pago?->format('d/m/Y H:i') ?? '',
        ];
    }

    /** @return array<int, string|int> */
    private function celdasExportacionPago(Pago $payment, string $format): array
    {
        return [
            self::textoExportacionSeguro((string) $payment->codigo, $format),
            self::textoExportacionSeguro($this->contrapartePago($payment), $format),
            self::textoExportacionSeguro($this->origenPago($payment), $format),
            $payment->tipo,
            self::textoExportacionSeguro((string) ($payment->medioPago?->nombre ?? ''), $format),
            number_format((float) $payment->importe, 2, '.', ''),
            $payment->moneda,
            self::textoExportacionSeguro((string) $payment->estado, $format),
            $payment->fecha_pago?->format('Y-m-d H:i') ?? '',
        ];
    }

    /**
     * Ejecuta la operación contrapartePago definida por la clase FinanzasController.
     *
     * @param  mixed  $payment  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function contrapartePago(Pago $payment): string
    {
        // Comprueba la condicion $payment->tipo === 'pago' antes de ejecutar este bloque.
        if ($payment->tipo === 'pago') {
            return (string) ($payment->compra?->supplier?->nombre ?? 'Proveedor no asociado');
        }

        return (string) ($payment->venta?->cliente?->nombre_contacto
            ?? $payment->venta?->cliente?->razon_social
            ?? $payment->pedido?->user?->nombre
            ?? 'Sin cliente asociado');
    }

    /**
     * Ejecuta la operación origenPago definida por la clase FinanzasController.
     *
     * @param  mixed  $payment  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function origenPago(Pago $payment): string
    {
        return match (true) {
            $payment->venta !== null => (string) $payment->venta->codigo,
            $payment->pedido !== null => 'Pedido #'.$payment->pedido_id,
            $payment->compra !== null => (string) ($payment->compra->numero_referencia ?: 'Compra #'.$payment->compra_id),
            default => 'Sin documento asociado',
        };
    }

    /** @return array<int, string|int> */
    private function celdasReembolso(Reembolso $refund): array
    {
        return [
            (int) $refund->getKey(),
            $refund->devolucion?->codigo ?? 'Devolución no asociada',
            $refund->devolucion?->customer?->nombre_contacto ?? $refund->devolucion?->customer?->razon_social ?? 'Sin cliente asociado',
            $refund->pago?->codigo ?? 'Pago no asociado',
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->formatearDinero((float) $refund->importe, $refund->pago?->moneda ?? 'PEN'),
            $refund->estado,
            $refund->creado_en?->format('d/m/Y H:i') ?? '',
        ];
    }

    /** @return array<int, string|int> */
    private function celdasExportacionReembolso(Reembolso $refund, string $format): array
    {
        return [
            (int) $refund->getKey(),
            self::textoExportacionSeguro((string) ($refund->devolucion?->codigo ?? ''), $format),
            self::textoExportacionSeguro((string) ($refund->devolucion?->customer?->nombre_contacto ?? $refund->devolucion?->customer?->razon_social ?? ''), $format),
            self::textoExportacionSeguro((string) ($refund->pago?->codigo ?? ''), $format),
            number_format((float) $refund->importe, 2, '.', ''),
            $refund->pago?->moneda ?? 'PEN',
            self::textoExportacionSeguro((string) $refund->estado, $format),
            $refund->creado_en?->format('Y-m-d H:i') ?? '',
        ];
    }

    /** @return array{label:string,value:string|int,icon:string,tone:string,helper:string} */
    private function metrica(string $label, string|int $value, string $icon, string $tone, string $helper): array
    {
        return compact('label', 'value', 'icon', 'tone', 'helper');
    }

    /** @return array<int, array{title:string,icon:string,tone:string,text:string}> */
    private function resumenEstados(Collection $states): array
    {
        return $states->map(static fn (object $state): array => [
            'title' => (string) $state->estado,
            'icon' => 'bi-list-check',
            'tone' => 'blue',
            'text' => number_format((int) $state->cantidad).' registros',
        ])->values()->all();
    }

    /**
     * Ejecuta la operación money definida por la clase FinanzasController.
     *
     * @param  mixed  $amount  Parámetro recibido por la operación.
     * @param  mixed  $currency  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function formatearDinero(float $amount, string $currency): string
    {
        return $currency.' '.number_format($amount, 2, '.', ',');
    }

    /**
     * Ejecuta la operación totalesMonetarios definida por la clase FinanzasController.
     *
     * @param  mixed  $totals  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function totalesMonetarios(Collection $totals): string
    {
        // Comprueba la condicion $totals->isEmpty() antes de ejecutar este bloque.
        if ($totals->isEmpty()) {
            return '—';
        }

        return $totals->map(fn (object $total): string => $this->formatearDinero((float) $total->importe_total, (string) $total->moneda))
            ->implode(' · ');
    }

    /**
     * Ejecuta la operación safeExportText definida por la clase FinanzasController.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private static function textoExportacionSeguro(string $value, string $format): string
    {
        // Guarda en $clean el resultado necesario para continuar esta operacion.
        $clean = trim(preg_replace('/[\p{C}\p{Z}]+/u', ' ', $value) ?? '');
        // Comprueba la condicion $format === 'pdf' antes de ejecutar este bloque.
        if ($format === 'pdf') {
            // Guarda en $clean el resultado necesario para continuar esta operacion.
            $clean = mb_convert_encoding(mb_convert_encoding($clean, 'Windows-1252', 'UTF-8'), 'UTF-8', 'Windows-1252');
        }

        return preg_match('/^[=+\-@]/u', $clean) === 1 ? "'".$clean : $clean;
    }
}
