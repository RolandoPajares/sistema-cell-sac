<?php

namespace App\Http\Controllers\Gestion\EcommerceMarketing\PromocionesCupones;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\EcommerceMarketing\PromocionesCupones\ActualizarCuponRequest;
use App\Http\Requests\Gestion\EcommerceMarketing\PromocionesCupones\DesactivarCuponRequest;
use App\Http\Requests\Gestion\EcommerceMarketing\PromocionesCupones\GuardarCuponRequest;
use App\Models\Comercio\Cupon;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\EcommerceMarketing\PromocionesCupones\ServicioCupones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase CuponController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CuponController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $coupons  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioCupones $coupons, ServicioAutorizacion $autorizacion): View
    {
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Prepara en $couponList la consulta que obtiene o modifica los registros necesarios.
        $couponList = $coupons->query($filters, $empresaId)->orderByDesc('id')->paginate(10)->withQueryString();
        $couponList->setCollection($couponList->getCollection()->map(function (Cupon $coupon) use ($coupons): Cupon {
            $coupon->setAttribute('estado_vigencia', $coupons->status($coupon));

            return $coupon;
        }));

        // Devuelve la vista 'gestion.ecommerce-marketing.promociones-cupones.listar' con los datos preparados por este controlador.
        return view('gestion.ecommerce-marketing.promociones-cupones.listar', [
            'coupons' => $couponList,
            'filters' => $filters,
            ...$coupons->dashboard($empresaId),
            'empresaCuponDisponible' => $coupons->empresaDisponible(),
            'isAdmin' => $autorizacion->allows($request->user(), 'gestion.seguridad.consultar'),
            'statusClasses' => [
                'Activo' => 'active',
                'Programado' => 'scheduled',
                'default' => 'finished',
            ],
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $coupons  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCuponRequest $request, ServicioCupones $coupons): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $coupons->registrar($request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Cupón registrado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $coupon  Parámetro recibido por la operación.
     * @param  mixed  $coupons  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarCuponRequest $request, Cupon $coupon, ServicioCupones $coupons): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $coupons->update($coupon, $request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Cupón actualizado.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $coupon  Parámetro recibido por la operación.
     * @param  mixed  $coupons  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(DesactivarCuponRequest $request, Cupon $coupon, ServicioCupones $coupons): RedirectResponse
    {
        $coupons->deactivate($coupon, $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Cupón cancelado. Ya no estará disponible para nuevos canjes.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $coupons  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, ServicioCupones $coupons, ServicioAutorizacion $autorizacion, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        // Guarda en $format el resultado necesario para continuar esta operacion.
        $format = (string) ($request->route('format') ?: 'csv');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Prepara en $records la consulta que obtiene o modifica los registros necesarios.
        $records = $coupons->query($filters, $autorizacion->activeCompanyId())->orderBy('id')->limit(5000)->get();
        $rows = $records->map(fn (Cupon $coupon): array => [
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->valorCsvSeguro($coupon->codigo),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->valorCsvSeguro($coupon->tipo_descuento),
            $coupon->valor,
            $coupon->minimo_compra,
            $coupon->usos,
            $coupon->limite_usos,
            $coupon->inicia_en?->toDateTimeString(),
            $coupon->finaliza_en?->toDateTimeString(),
            $coupons->status($coupon),
        ])->all();
        // Guarda en $response el resultado necesario para continuar esta operacion.
        $response = $exporter->download($format, 'cupones-'.now()->format('Ymd'),
            ['Código', 'Tipo de descuento', 'Valor', 'Compra mínima', 'Usos', 'Límite de usos', 'Vigente desde', 'Vigente hasta', 'Estado'],
            $rows,
            [
                'title' => 'REPORTE DE CUPONES',
                'subtitle' => 'Cupones reales consultados desde MariaDB.',
                'module' => 'Ecommerce y Marketing',
                'report_name' => 'Cupones',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa activa y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Cupones incluidos', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'cupones', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /** @return array{buscar:?string,tipo:?string,estado:?string,vigencia:?string} */
    private function filtros(Request $request): array
    {
        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'in:porcentaje,fijo'],
            'estado' => ['nullable', 'in:Activo,Programado,Finalizado'],
            'vigencia' => ['nullable', 'in:este_mes,proximos_30_dias,historico'],
        ]);
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim((string) ($validated['buscar'] ?? ''));

        return [
            'buscar' => $search !== '' ? $search : null,
            'tipo' => $validated['tipo'] ?? null,
            'estado' => $validated['estado'] ?? null,
            'vigencia' => $validated['vigencia'] ?? null,
        ];
    }

    /**
     * Ejecuta la operación safeCsvValue definida por la clase CuponController.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function valorCsvSeguro(string $value): string
    {
        return preg_match('/^[\s\x00-\x1f]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
