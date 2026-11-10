<?php

namespace App\Http\Controllers\Gestion\Finanzas\AperturasCierres;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Finanzas\AperturasCierres\AbrirSesionCajaRequest;
use App\Http\Requests\Gestion\Finanzas\AperturasCierres\CerrarSesionCajaRequest;
use App\Http\Requests\Gestion\Finanzas\AperturasCierres\FiltroSesionesCajaRequest;
use App\Http\Requests\Gestion\Finanzas\AperturasCierres\RegistrarMovimientoCajaRequest;
use App\Models\Finanzas\Caja;
use App\Models\Finanzas\SesionCaja;
use App\Models\Organizacion\Sede;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Finanzas\ServicioCajas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase SesionCajaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class SesionCajaController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(FiltroSesionesCajaRequest $request, ServicioAutorizacion $authorization): View
    {
        $companyId = $authorization->activeCompanyId();
        abort_unless($companyId !== null, 409, 'Selecciona una única empresa activa.');
        $visibleSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.consultar');
        $sites = Sede::query()->where('empresa_id', $companyId)->where('activa', true)
            ->whereIn('id', $visibleSiteIds)->orderBy('nombre')->get(['id', 'empresa_id', 'nombre']);
        $siteIds = $sites->modelKeys();
        $filters = $request->validated();
        $base = SesionCaja::query()->whereHas('caja', static fn (Builder $box): Builder => $box->whereIn('sede_id', $siteIds));
        $sessions = (clone $base)
            ->when($filters['buscar'] ?? null, static fn (Builder $query, string $search): Builder => $query
                ->whereHas('caja', static fn (Builder $box): Builder => $box
                    ->where(static fn (Builder $terms): Builder => $terms->where('codigo', 'like', '%'.$search.'%')
                        ->orWhere('nombre', 'like', '%'.$search.'%'))))
            ->when($filters['sede_id'] ?? null, static fn (Builder $query, int|string $siteId): Builder => $query
                ->whereHas('caja', static fn (Builder $box): Builder => $box->where('sede_id', $siteId)))
            ->when($filters['estado'] ?? null, static fn (Builder $query, string $state): Builder => $query->where('estado', $state))
            ->when($filters['desde'] ?? null, static fn (Builder $query, string $from): Builder => $query->whereDate('abierta_en', '>=', $from))
            ->when($filters['hasta'] ?? null, static fn (Builder $query, string $to): Builder => $query->whereDate('abierta_en', '<=', $to))
            ->with(['caja.sede:id,nombre', 'usuarioApertura:id,nombre', 'usuarioCierre:id,nombre'])
            ->latest('abierta_en')->paginate(20)->withQueryString();
        $creatableSiteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.pagos.crear');
        $boxes = Caja::query()->whereIn('sede_id', array_intersect($siteIds, $creatableSiteIds))
            ->where('activa', true)->whereDoesntHave('sesiones', static fn (Builder $query): Builder => $query->where('estado', 'abierta'))
            ->with('sede:id,nombre')->orderBy('codigo')->get();

        return view('gestion.finanzas.aperturas-cierres.operativa', [
            'sessions' => $sessions,
            'sites' => $sites,
            'boxes' => $boxes,
            'filters' => $filters,
            'openCount' => (clone $base)->where('estado', 'abierta')->count(),
            'closedCount' => (clone $base)->where('estado', 'cerrada')->count(),
            'canOpen' => $boxes->isNotEmpty(),
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(AbrirSesionCajaRequest $request, ServicioCajas $boxes): RedirectResponse
    {
        $data = $request->validated();
        $session = $boxes->abrirSesion(Caja::query()->findOrFail($data['caja_id']),
            $data['saldo_inicial'], $data['token'], $request->user(), $request->ip());

        return redirect()->route('management.payments.cash_sessions.show', $session)
            ->with('status', 'Sesión de caja abierta.');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $sesionCaja  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function show(Request $request, SesionCaja $sesionCaja, ServicioAutorizacion $authorization, ServicioCajas $boxes): View
    {
        abort_unless($authorization->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403);
        $sesionCaja->load(['caja.sede', 'usuarioApertura:id,nombre', 'usuarioCierre:id,nombre']);
        $site = $sesionCaja->caja->sede;
        abort_unless((int) $site->empresa_id === $authorization->activeCompanyId()
            && $authorization->allowsRecord($request->user(), 'gestion.pagos.consultar',
                (int) $site->empresa_id, (int) $site->id), 404);

        return view('gestion.finanzas.aperturas-cierres.detalle', [
            'session' => $sesionCaja,
            'movements' => $sesionCaja->movimientos()->with('creador:id,nombre')->latest('id')->paginate(25),
            'balance' => $boxes->arqueo($sesionCaja),
            'canMove' => $sesionCaja->estado === 'abierta' && $sesionCaja->caja->activa
                && $authorization->allowsRecord($request->user(), 'gestion.pagos.crear', (int) $site->empresa_id, (int) $site->id),
            'canClose' => $sesionCaja->estado === 'abierta'
                && $authorization->allowsRecord($request->user(), 'gestion.pagos.actualizar', (int) $site->empresa_id, (int) $site->id),
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $sesionCaja  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function storeMovement(RegistrarMovimientoCajaRequest $request, SesionCaja $sesionCaja, ServicioCajas $boxes): RedirectResponse
    {
        $data = $request->validated();
        $boxes->registrarMovimiento($sesionCaja, $data['direccion'], $data['importe'], $data['descripcion'],
            $data['token'], $request->user(), $request->ip());

        return redirect()->route('management.payments.cash_sessions.show', $sesionCaja)
            ->with('status', 'Movimiento de caja registrado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $sesionCaja  Parámetro recibido por la operación.
     * @param  mixed  $boxes  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(CerrarSesionCajaRequest $request, SesionCaja $sesionCaja, ServicioCajas $boxes): RedirectResponse
    {
        $data = $request->validated();
        $boxes->cerrarSesion($sesionCaja, $data['saldo_cierre_declarado'], $data['token'],
            $request->user(), $request->ip());

        return redirect()->route('management.payments.cash_sessions.show', $sesionCaja)
            ->with('status', 'Sesión de caja cerrada con arqueo conforme.');
    }
}
