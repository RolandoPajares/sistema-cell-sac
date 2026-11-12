<?php

namespace App\Http\Controllers\Gestion\Finanzas\CuentasCobrarPagar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Finanzas\CuentasCobrarPagar\AplicarCobroCuentaRequest;
use App\Http\Requests\Gestion\Finanzas\CuentasCobrarPagar\AplicarPagoCuentaRequest;
use App\Http\Requests\Gestion\Finanzas\CuentasCobrarPagar\CrearCuentaPorPagarRequest;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Finanzas\CuentasCobrarPagar\ServicioConsultasCuentas;
use App\Servicios\Gestion\Finanzas\CuentasCobrarPagar\ServicioCuentasFinancieras;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Define la responsabilidad de la clase CuentasFinancierasController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CuentasFinancierasController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $consultasCuentas  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private ServicioConsultasCuentas $consultasCuentas) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioAutorizacion $autorizacion): View
    {
        // Guarda en $datosConfiguracion el resultado necesario para continuar esta operacion.
        $datosConfiguracion = require config_path('gestion/finanzas-facturacion.php');
        // Guarda en $pagina el resultado necesario para continuar esta operacion.
        $pagina = $datosConfiguracion['cuentas'];
        // Prepara en $datosReales la consulta que obtiene o modifica los registros necesarios.
        $datosReales = $this->consultasCuentas->obtenerVista(User::query()->findOrFail(auth()->id()));
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $sedesAutorizadas el resultado necesario para continuar esta operacion.
        $sedesAutorizadas = $autorizacion->authorizedSiteIds($usuario, 'gestion.pagos.crear');
        $comprasRecibidas = $sedesAutorizadas === [] ? collect() : DB::table('compras')
            ->join('sedes', 'sedes.id', '=', 'compras.sede_id')
            ->join('proveedores', 'proveedores.id', '=', 'compras.proveedor_id')
            ->whereIn('compras.sede_id', $sedesAutorizadas)->whereColumn('compras.empresa_id', 'sedes.empresa_id')
            ->where('compras.activo', true)->where('compras.estado', 'Recibida')
            ->whereNotExists(fn ($consulta) => $consulta->selectRaw('1')->from('cuentas_por_pagar')->whereColumn('cuentas_por_pagar.compra_id', 'compras.id'))
            ->orderByDesc('compras.fecha_compra')->orderByDesc('compras.id')
            ->get(['compras.id', 'compras.numero_referencia', 'compras.total', 'compras.moneda', 'proveedores.nombre']);
        // Agrupa en $pagosPorCuenta los datos que se utilizaran en la siguiente operacion.
        $pagosPorCuenta = [];
        // Recorre collect($datosReales['filas'])->where('tipo_cuenta', 'Por pagar') as $filaCuenta y procesa cada elemento de la coleccion.
        foreach (collect($datosReales['filas'])->where('tipo_cuenta', 'Por pagar') as $filaCuenta) {
            $pagosPorCuenta[$filaCuenta['cuenta_id']] = DB::table('pagos')
                ->join('compras as compra_origen', 'compra_origen.id', '=', 'pagos.compra_id')
                ->leftJoin('aplicaciones_pago', 'aplicaciones_pago.pago_id', '=', 'pagos.id')
                ->where('pagos.compra_id', $filaCuenta['origen_id'])->where('pagos.tipo', 'pago')->where('pagos.estado', 'registrado')
                ->whereColumn('pagos.empresa_id', 'compra_origen.empresa_id')->whereColumn('pagos.sede_id', 'compra_origen.sede_id')
                ->groupBy('pagos.id', 'pagos.codigo', 'pagos.importe', 'pagos.moneda')
                ->havingRaw('pagos.importe > COALESCE(SUM(aplicaciones_pago.importe_aplicado), 0)')
                ->orderBy('pagos.id')->get(['pagos.id', 'pagos.codigo', 'pagos.importe', 'pagos.moneda']);
        }

        // Agrupa en $pagosCobroPorCuenta los datos que se utilizaran en la siguiente operacion.
        $pagosCobroPorCuenta = [];
        // Guarda en $sedesAutorizadasParaActualizar el resultado necesario para continuar esta operacion.
        $sedesAutorizadasParaActualizar = $autorizacion->authorizedSiteIds($usuario, 'gestion.pagos.actualizar');
        // Recorre collect($datosReales['filas'])->where('tipo_cuenta', 'Por cobrar') as $filaCuenta y procesa cada elemento de la coleccion.
        foreach (collect($datosReales['filas'])->where('tipo_cuenta', 'Por cobrar') as $filaCuenta) {
            $pagosCobroPorCuenta[$filaCuenta['cuenta_id']] = $sedesAutorizadasParaActualizar === [] ? collect() : DB::table('documentos_electronicos as documentos')
                ->join('pagos', function ($join): void {
                    $join->on('pagos.venta_id', '=', 'documentos.venta_id')
                        ->on('pagos.empresa_id', '=', 'documentos.empresa_id')
                        ->on('pagos.sede_id', '=', 'documentos.sede_id');
                })
                ->leftJoin('aplicaciones_cobro', 'aplicaciones_cobro.pago_id', '=', 'pagos.id')
                ->where('documentos.id', $filaCuenta['origen_id'])
                ->whereNotNull('documentos.venta_id')
                ->whereIn('documentos.sede_id', $sedesAutorizadasParaActualizar)
                ->where('pagos.tipo', 'cobro')->where('pagos.estado', 'registrado')
                ->groupBy('pagos.id', 'pagos.codigo', 'pagos.importe', 'pagos.moneda')
                ->havingRaw('pagos.importe > COALESCE(SUM(aplicaciones_cobro.importe_aplicado), 0)')
                ->orderBy('pagos.id')->get(['pagos.id', 'pagos.codigo', 'pagos.importe', 'pagos.moneda']);
        }

        $pagina['filas'] = $datosReales['filas'];
        $pagina['metricas'] = $datosReales['metricas'];
        $pagina['paneles'] = $datosReales['paneles'];
        $pagina['datosReales'] = true;
        $pagina['comprasRecibidas'] = $comprasRecibidas;
        $pagina['pagosPorCuenta'] = $pagosPorCuenta;
        $pagina['pagosCobroPorCuenta'] = $pagosCobroPorCuenta;
        // Recorre ['tipo', 'estado', 'moneda'] as $indice => $campo y procesa cada elemento de la coleccion.
        foreach (['tipo', 'estado', 'moneda'] as $indice => $campo) {
            $pagina['filtros'][$indice]['opciones'] = collect($datosReales['filas'])
                ->pluck('filtros.'.$campo)->unique()->sort()->values()->all();
        }
        $pagina['descripcion'] = 'Seguimiento de saldos derivados de documentos electrónicos, compras y aplicaciones de pagos registrados.';
        $pagina['tablaTitulo'] = 'Cuentas por cobrar y pagar';
        $pagina['aside']['titulo'] = 'Nueva cuenta por pagar y aplicación';
        $pagina['aside']['icono'] = 'bi-file-earmark-plus';
        $pagina['aside']['descripcion'] = 'Registra una obligación desde una compra recibida y aplica pagos internos ya registrados.';
        $pagina['aviso'] = 'Los datos y saldos mostrados se consultan desde MariaDB. Las operaciones de alta y abono requieren completar sus flujos transaccionales.';

        $pagina['descripcion'] = 'Saldos derivados de documentos electrónicos, compras y aplicaciones de pagos registrados.';
        $pagina['aside']['titulo'] = 'Cuentas por cobrar y pagar';
        $pagina['aside']['descripcion'] = 'Registra obligaciones desde compras recibidas y aplica pagos o cobros locales previamente registrados.';
        $pagina['aviso'] = 'Los saldos se calculan con documentos, compras y aplicaciones registrados en MariaDB.';

        // Devuelve la vista 'gestion.finanzas.cuentas-cobrar-pagar.listar' con los datos preparados por este controlador.
        return view('gestion.finanzas.cuentas-cobrar-pagar.listar', ['paginaReal' => $pagina]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $cuentas  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function crearCuentaPorPagar(CrearCuentaPorPagarRequest $request, ServicioCuentasFinancieras $cuentas): RedirectResponse
    {
        // Guarda en $cuentaId el resultado necesario para continuar esta operacion.
        $cuentaId = $cuentas->crearCuentaPorPagar(
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            (int) $request->validated('compra_id'), $request->validated('vence_en'), $request->user(), $request->ip(),
        );

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.preview.accounts')->with('status', 'Cuenta por pagar #'.$cuentaId.' registrada desde la compra recibida.');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $cuentas  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function aplicarCobro(AplicarCobroCuentaRequest $request, ServicioCuentasFinancieras $cuentas): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        $cuentas->aplicarCobroRegistrado((int) $datos['cuenta_cobrar_id'], (int) $datos['pago_id'], $datos['importe'], $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.preview.accounts')->with('status', 'El cobro registrado se aplicó a la cuenta por cobrar.');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $cuentas  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function aplicarPago(AplicarPagoCuentaRequest $request, ServicioCuentasFinancieras $cuentas): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        $cuentas->aplicarPagoRegistrado((int) $datos['cuenta_pagar_id'], (int) $datos['pago_id'], $datos['importe'], $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.preview.accounts')->with('status', 'El pago registrado se aplicó a la cuenta por pagar.');
    }
}
