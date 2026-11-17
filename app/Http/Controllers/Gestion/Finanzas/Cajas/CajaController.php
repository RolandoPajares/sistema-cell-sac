<?php

namespace App\Http\Controllers\Gestion\Finanzas\Cajas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Finanzas\Cajas\ActualizarCajaRequest;
use App\Http\Requests\Gestion\Finanzas\Cajas\CambiarEstadoCajaRequest;
use App\Http\Requests\Gestion\Finanzas\Cajas\FiltroCajasRequest;
use App\Http\Requests\Gestion\Finanzas\Cajas\GuardarCajaRequest;
use App\Models\Finanzas\Caja;
use App\Models\Organizacion\Sede;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Finanzas\ServicioCajas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase CajaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CajaController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(FiltroCajasRequest $request, ServicioAutorizacion $authorization): View
    {
        // Guarda en $companyId el resultado necesario para continuar esta operacion.
        $companyId = $authorization->activeCompanyId();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($companyId !== null, 409, 'Selecciona una única empresa activa para administrar cajas.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $request->validated();
        // Prepara en $companySites la consulta que obtiene o modifica los registros necesarios.
        $companySites = Sede::query()->where('empresa_id', $companyId)->where('activa', true)
            ->orderBy('nombre')->get(['id', 'empresa_id', 'nombre']);
        // Obtiene en $viewableSiteIds la informacion de la solicitud o del usuario actual.
        $viewableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.consultar');
        // Obtiene en $creatableSiteIds la informacion de la solicitud o del usuario actual.
        $creatableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.crear');
        // Obtiene en $editableSiteIds la informacion de la solicitud o del usuario actual.
        $editableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.actualizar');
        $sites = $companySites->filter(static fn (Sede $site): bool => in_array((int) $site->id, $viewableSiteIds, true))->values();
        $createSites = $sites->filter(static fn (Sede $site): bool => in_array((int) $site->id, $creatableSiteIds, true))->values();
        // Guarda en $editableSiteIds el resultado necesario para continuar esta operacion.
        $editableSiteIds = array_values(array_intersect($sites->modelKeys(), $editableSiteIds));
        // Guarda en $siteIds el resultado necesario para continuar esta operacion.
        $siteIds = $sites->pluck('id')->all();
        $base = Caja::query()->whereHas('sede', static fn (Builder $siteQuery): Builder => $siteQuery
            ->where('empresa_id', $companyId)->where('activa', true))->whereIn('sede_id', $siteIds);
        // Guarda en $filtered el resultado necesario para continuar esta operacion.
        $filtered = (clone $base)
            ->when($filters['buscar'] ?? null, static fn (Builder $query, string $search): Builder => $query
                ->where(static fn (Builder $terms): Builder => $terms->where('codigo', 'like', '%'.$search.'%')
                    ->orWhere('nombre', 'like', '%'.$search.'%')))
            ->when($filters['sede_id'] ?? null, static fn (Builder $query, int|string $siteId): Builder => $query->where('sede_id', $siteId))
            ->when(isset($filters['activa']), static fn (Builder $query): Builder => $query->where('activa', (bool) $filters['activa']));
        // Guarda en $boxes el resultado necesario para continuar esta operacion.
        $boxes = (clone $filtered)->with('sede:id,empresa_id,nombre')
            ->addSelect(['sesiones_abiertas' => DB::table('sesiones_caja')->selectRaw('COUNT(*)')
                ->whereColumn('sesiones_caja.caja_id', 'cajas.id')->where('estado', 'abierta')])
            ->orderBy('sede_id')->orderBy('codigo')->orderBy('id')->paginate(20)->withQueryString();

        // Devuelve la vista 'gestion.finanzas.cajas.operativa' con los datos preparados por este controlador.
        return view('gestion.finanzas.cajas.operativa', [
            'boxes' => $boxes,
            'sites' => $sites,
            'createSites' => $createSites,
            'editableSiteIds' => $editableSiteIds,
            'filters' => $filters,
            'totalBoxes' => (clone $base)->count(),
            'activeBoxes' => (clone $base)->where('activa', true)->count(),
            'siteCounts' => (clone $base)->select('sede_id')->selectRaw('COUNT(*) AS cantidad')
                ->groupBy('sede_id')->pluck('cantidad', 'sede_id'),
            'openSessions' => DB::table('sesiones_caja')->join('cajas', 'cajas.id', '=', 'sesiones_caja.caja_id')
                ->whereIn('cajas.sede_id', $siteIds)
                ->where('sesiones_caja.estado', 'abierta')->count(),
            'canCreate' => $createSites->isNotEmpty(),
            'canUpdate' => $editableSiteIds !== [],
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCajaRequest $request, ServicioCajas $boxes): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $boxes->crear($request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.preview.cash_registers')->with('status', 'Caja creada correctamente.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $caja  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarCajaRequest $request, Caja $caja, ServicioCajas $boxes): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $boxes->actualizar($caja, $request->validated(), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.preview.cash_registers')->with('status', 'Caja actualizada correctamente.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $caja  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateStatus(CambiarEstadoCajaRequest $request, Caja $caja, ServicioCajas $boxes): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $box = $boxes->cambiarEstado($caja, (bool) $request->validated('activa'), $request->user(), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.payments.preview.cash_registers')
            ->with('status', $box->activa ? 'Caja activada.' : 'Caja desactivada.');
    }
}
