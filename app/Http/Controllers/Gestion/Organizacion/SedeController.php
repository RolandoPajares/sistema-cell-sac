<?php

namespace App\Http\Controllers\Gestion\Organizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarEstadoSedeRequest;
use App\Http\Requests\FiltroSedesRequest;
use App\Http\Requests\GuardarSedeRequest;
use App\Models\Organizacion\Empresa;
use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase SedeController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class SedeController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(FiltroSedesRequest $request, ServicioAutorizacion $autorizacion): View
    {
        $usuario = $request->user();
        $empresaId = $autorizacion->activeCompanyId();
        $consultaBase = Sede::query()->where('empresa_id', $empresaId);
        $filtros = $request->validated();
        $consulta = (clone $consultaBase)->withCount('almacenes');

        if (filled($filtros['buscar'] ?? null)) {
            $buscar = trim((string) $filtros['buscar']);
            $consulta->where(static function (Builder $subconsulta) use ($buscar): void {
                $subconsulta->where('codigo', 'like', '%'.$buscar.'%')
                    ->orWhere('nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('direccion', 'like', '%'.$buscar.'%');
            });
        }

        if (filled($filtros['estado'] ?? null)) {
            $consulta->where('activa', $filtros['estado'] === 'activa');
        }

        $sedes = $consulta->orderBy('nombre')->orderBy('id')->paginate(15)->withQueryString();
        $resumen = (clone $consultaBase)->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN activa = 1 THEN 1 ELSE 0 END) AS activas, SUM(CASE WHEN activa = 0 THEN 1 ELSE 0 END) AS inactivas',
        )->first();
        $totalAlmacenes = DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('sedes.empresa_id', $empresaId)
            ->count();
        $almacenesPorSede = (clone $consultaBase)->withCount('almacenes')
            ->orderByDesc('almacenes_count')->orderBy('nombre')->limit(6)->get(['id', 'nombre']);
        $empresa = $empresaId === null ? null : Empresa::query()->find($empresaId, ['id', 'razon_social', 'nombre_comercial']);

        return view('gestion.organizacion.sedes.listar', [
            'sedes' => $sedes,
            'filtros' => $filtros,
            'resumen' => [
                'total' => (int) ($resumen->total ?? 0),
                'activas' => (int) ($resumen->activas ?? 0),
                'inactivas' => (int) ($resumen->inactivas ?? 0),
            ],
            'totalAlmacenes' => $totalAlmacenes,
            'almacenesPorSede' => $almacenesPorSede,
            'empresa' => $empresa,
            'puedeEditar' => $usuario instanceof User && $autorizacion->allows($usuario, 'gestion.organizacion.actualizar'),
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
     */
    public function store(
        GuardarSedeRequest $request,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();
        $empresaId = $autorizacion->activeCompanyId();

        abort_if($empresaId === null, 409, 'Debe existir una empresa activa antes de registrar sedes.');

        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request): void {
            $empresa = Empresa::query()->whereKey($empresaId)->where('activo', true)->lockForUpdate()->firstOrFail();
            $sede = $empresa->sedes()->create([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'direccion' => $datos['direccion'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'zona_horaria' => $datos['zona_horaria'],
                'activa' => true,
            ]);

            $auditoria->record(
                (int) $usuario->getKey(), 'sede.registrada', 'sedes', (int) $sede->getKey(), null,
                ['codigo' => $sede->codigo, 'nombre' => $sede->nombre], $request->ip(),
                observations: 'Sede registrada desde el módulo de organización.',
                scope: ['empresa_id' => (int) $empresa->getKey(), 'sede_id' => (int) $sede->getKey()],
            );
        }, 3);

        return back()->with('status', 'La sede quedó registrada.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @return mixed Resultado de la operación.
     */
    public function update(
        GuardarSedeRequest $request,
        Sede $sede,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();
        $empresaId = $autorizacion->activeCompanyId();

        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request, $sede): void {
            $sede = Sede::query()->where('empresa_id', $empresaId)->whereKey($sede->getKey())->lockForUpdate()->firstOrFail();
            $valoresAnteriores = [
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'zona_horaria' => $sede->zona_horaria,
            ];
            $sede->update([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'direccion' => $datos['direccion'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'zona_horaria' => $datos['zona_horaria'],
            ]);
            $auditoria->record(
                (int) $usuario->getKey(), 'sede.actualizada', 'sedes', (int) $sede->getKey(), $valoresAnteriores,
                [
                    'codigo' => $sede->codigo, 'nombre' => $sede->nombre, 'direccion' => $sede->direccion,
                    'telefono' => $sede->telefono, 'zona_horaria' => $sede->zona_horaria,
                ], $request->ip(), observations: 'Datos de sede actualizados.',
                scope: ['empresa_id' => (int) $sede->empresa_id, 'sede_id' => (int) $sede->getKey()],
            );
        }, 3);

        return back()->with('status', 'Los datos de la sede quedaron actualizados.');
    }

    /**
     * Ejecuta la operación cambiarEstado definida por la clase SedeController.
     *
     * @return mixed Resultado de la operación.
     */
    public function cambiarEstado(
        CambiarEstadoSedeRequest $request,
        Sede $sede,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();
        $empresaId = $autorizacion->activeCompanyId();

        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request, $sede): void {
            $sede = Sede::query()->where('empresa_id', $empresaId)->whereKey($sede->getKey())->lockForUpdate()->firstOrFail();
            $estadoAnterior = (bool) $sede->activa;
            $estadoNuevo = (bool) $datos['activa'];

            if ($estadoAnterior === $estadoNuevo) {
                return;
            }

            $sede->update(['activa' => $estadoNuevo]);
            $auditoria->record(
                (int) $usuario->getKey(), $estadoNuevo ? 'sede.activada' : 'sede.desactivada',
                'sedes', (int) $sede->getKey(), ['activa' => $estadoAnterior],
                ['activa' => $estadoNuevo, 'motivo' => $datos['motivo']], $request->ip(),
                observations: $datos['motivo'],
                scope: ['empresa_id' => (int) $sede->empresa_id, 'sede_id' => (int) $sede->getKey()],
            );
        }, 3);

        return back()->with('status', $datos['activa'] ? 'La sede quedó activada.' : 'La sede quedó desactivada.');
    }
}
