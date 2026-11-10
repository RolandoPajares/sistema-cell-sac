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
        $companyId = $authorization->activeCompanyId();
        abort_unless($companyId !== null, 409, 'Selecciona una única empresa activa para administrar cajas.');
        $filters = $request->validated();
        $companySites = Sede::query()->where('empresa_id', $companyId)->where('activa', true)
            ->orderBy('nombre')->get(['id', 'empresa_id', 'nombre']);
        $viewableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.consultar');
        $creatableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.crear');
        $editableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.actualizar');
        $sites = $companySites->filter(static fn (Sede $site): bool => in_array((int) $site->id, $viewableSiteIds, true))->values();
        $createSites = $sites->filter(static fn (Sede $site): bool => in_array((int) $site->id, $creatableSiteIds, true))->values();
        $editableSiteIds = array_values(array_intersect($sites->modelKeys(), $editableSiteIds));
        $siteIds = $sites->pluck('id')->all();
        $base = Caja::query()->whereHas('sede', static fn (Builder $siteQuery): Builder => $siteQuery
            ->where('empresa_id', $companyId)->where('activa', true))->whereIn('sede_id', $siteIds);
        $filtered = (clone $base)
            ->when($filters['buscar'] ?? null, static fn (Builder $query, string $search): Builder => $query
                ->where(static fn (Builder $terms): Builder => $terms->where('codigo', 'like', '%'.$search.'%')
                    ->orWhere('nombre', 'like', '%'.$search.'%')))
            ->when($filters['sede_id'] ?? null, static fn (Builder $query, int|string $siteId): Builder => $query->where('sede_id', $siteId))
            ->when(isset($filters['activa']), static fn (Builder $query): Builder => $query->where('activa', (bool) $filters['activa']));
        $boxes = (clone $filtered)->with('sede:id,empresa_id,nombre')
            ->addSelect(['sesiones_abiertas' => DB::table('sesiones_caja')->selectRaw('COUNT(*)')
                ->whereColumn('sesiones_caja.caja_id', 'cajas.id')->where('estado', 'abierta')])
            ->orderBy('sede_id')->orderBy('codigo')->orderBy('id')->paginate(20)->withQueryString();

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
        $boxes->crear($request->validated(), $request->user(), $request->ip());

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
        $boxes->actualizar($caja, $request->validated(), $request->user(), $request->ip());

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
        $box = $boxes->cambiarEstado($caja, (bool) $request->validated('activa'), $request->user(), $request->ip());

        return redirect()->route('management.payments.preview.cash_registers')
            ->with('status', $box->activa ? 'Caja activada.' : 'Caja desactivada.');
    }
}
