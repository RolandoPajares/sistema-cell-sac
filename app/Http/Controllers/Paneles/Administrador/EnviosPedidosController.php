<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarEstadoEnvioPedidoRequest;
use App\Http\Requests\ListadoEnviosPedidosRequest;
use App\Models\Comercio\EnvioPedido;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioEnviosPedidos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase EnviosPedidosController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class EnviosPedidosController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $envios  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioEnviosPedidos $envios, private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Ejecuta la operación mostrar definida por la clase EnviosPedidosController.
     *
     * @param  mixed  $solicitud  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function mostrar(ListadoEnviosPedidosRequest $solicitud): View
    {
        return view(
            'gestion.inventario.envios-entregas.envios.listar',
            $this->envios->listar($solicitud->user(), $solicitud->validated()),
        );
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $solicitud  Parámetro recibido por la operación.
     * @param  mixed  $envioPedido  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function actualizarEstado(ActualizarEstadoEnvioPedidoRequest $solicitud, EnvioPedido $envioPedido): RedirectResponse
    {
        $this->envios->actualizarEstado($envioPedido, $solicitud->validated(), $solicitud->user(), $solicitud->ip());

        return back()->with('status', 'Estado de envío actualizado y evento registrado.');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $envio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function detalle(Request $request, EnvioPedido $envio): View
    {
        $sedesAutorizadas = $this->autorizacion->authorizedSiteIds($request->user(), 'gestion.inventario.consultar');
        abort_unless(in_array((int) $envio->sede_id, $sedesAutorizadas, true), 404);

        $envio->load(['pedido:id,numero_referencia,cliente_id', 'almacen.sede', 'eventos' => static fn ($query) => $query->orderByDesc('ocurrido_en')->orderByDesc('id')]);

        return view('gestion.inventario.envios-entregas.detalle', compact('envio'));
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $solicitud  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function exportar(ListadoEnviosPedidosRequest $solicitud, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $records = $this->envios->consultaExportacion($solicitud->user(), $solicitud->validated())->get();
        $rows = $records->map(static fn ($envio): array => [$envio->codigo, $envio->pedido_id, $envio->almacen?->nombre, $envio->almacen?->sede?->nombre, $envio->transportista, $envio->servicio, $envio->numero_seguimiento, $envio->estado, $envio->ultimoEvento?->ocurrido_en?->toDateTimeString()])->all();
        $response = $exporter->download($format, 'envios-pedidos', ['Código', 'Pedido', 'Almacén', 'Sede', 'Transportista', 'Servicio', 'Seguimiento', 'Estado', 'Último evento'], $rows, [
            'title' => 'REPORTE DE ENVÍOS Y ENTREGAS', 'module' => 'Inventario y Logística', 'report_name' => 'Envíos de pedidos', 'generated_by' => (string) $solicitud->user()->nombre,
            'indicators' => [['label' => 'Envíos incluidos', 'value' => count($rows)]], 'column_types' => ['text', 'integer', 'text', 'text', 'text', 'text', 'text', 'text', 'datetime'],
        ]);
        $audit->record($solicitud->user()->getKey(), 'exportar', 'envios_pedidos', null, null, ['formato' => $format, 'cantidad' => count($rows)], $solicitud->ip());

        return $response;
    }
}
