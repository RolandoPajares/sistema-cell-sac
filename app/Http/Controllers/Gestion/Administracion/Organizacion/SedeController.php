<?php

namespace App\Http\Controllers\Gestion\Administracion\Organizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Administracion\Organizacion\CambiarEstadoSedeRequest;
use App\Http\Requests\Gestion\Administracion\Organizacion\FiltroSedesRequest;
use App\Http\Requests\Gestion\Administracion\Organizacion\GuardarSedeRequest;
use App\Models\Organizacion\Empresa;
use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Prepara en $consultaBase la consulta que obtiene o modifica los registros necesarios.
        $consultaBase = Sede::query()->where('empresa_id', $empresaId);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filtros = $request->validated();
        // Guarda en $consulta el resultado necesario para continuar esta operacion.
        $consulta = (clone $consultaBase)->withCount('almacenes');

        // Comprueba la condicion filled($filtros['buscar'] ?? null) antes de ejecutar este bloque.
        if (filled($filtros['buscar'] ?? null)) {
            // Guarda en $buscar el resultado necesario para continuar esta operacion.
            $buscar = trim((string) $filtros['buscar']);
            $consulta->where(static function (Builder $subconsulta) use ($buscar): void {
                $subconsulta->where('codigo', 'like', '%'.$buscar.'%')
                    ->orWhere('nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('direccion', 'like', '%'.$buscar.'%');
            });
        }

        // Comprueba la condicion filled($filtros['estado'] ?? null) antes de ejecutar este bloque.
        if (filled($filtros['estado'] ?? null)) {
            $consulta->where('activa', $filtros['estado'] === 'activa');
        }

        // Guarda en $sedes el resultado necesario para continuar esta operacion.
        $sedes = $consulta->orderBy('nombre')->orderBy('id')->paginate(15)->withQueryString();
        // Guarda en $resumen el resultado necesario para continuar esta operacion.
        $resumen = (clone $consultaBase)->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN activa = 1 THEN 1 ELSE 0 END) AS activas, SUM(CASE WHEN activa = 0 THEN 1 ELSE 0 END) AS inactivas',
        )->first();
        // Prepara en $totalAlmacenes la consulta que obtiene o modifica los registros necesarios.
        $totalAlmacenes = DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('sedes.empresa_id', $empresaId)
            ->count();
        // Guarda en $almacenesPorSede el resultado necesario para continuar esta operacion.
        $almacenesPorSede = (clone $consultaBase)->withCount('almacenes')
            ->orderByDesc('almacenes_count')->orderBy('nombre')->limit(6)->get(['id', 'nombre']);
        $empresa = $empresaId === null ? null : Empresa::query()->find($empresaId, ['id', 'razon_social', 'nombre_comercial']);

        // Devuelve la vista 'gestion.administracion.organizacion.sedes.listar' con los datos preparados por este controlador.
        return view('gestion.administracion.organizacion.sedes.listar', [
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($empresaId === null, 409, 'Debe existir una empresa activa antes de registrar sedes.');

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request): void {
            // Prepara en $empresa la consulta que obtiene o modifica los registros necesarios.
            $empresa = Empresa::query()->whereKey($empresaId)->where('activo', true)->lockForUpdate()->firstOrFail();
            // Crea y guarda en $sede el registro correspondiente mediante el modelo.
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

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request, $sede): void {
            // Prepara en $sede la consulta que obtiene o modifica los registros necesarios.
            $sede = Sede::query()->where('empresa_id', $empresaId)->whereKey($sede->getKey())->lockForUpdate()->firstOrFail();
            // Agrupa en $valoresAnteriores los datos que se utilizaran en la siguiente operacion.
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

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request, $sede): void {
            // Prepara en $sede la consulta que obtiene o modifica los registros necesarios.
            $sede = Sede::query()->where('empresa_id', $empresaId)->whereKey($sede->getKey())->lockForUpdate()->firstOrFail();
            // Guarda en $estadoAnterior el resultado necesario para continuar esta operacion.
            $estadoAnterior = (bool) $sede->activa;
            // Guarda en $estadoNuevo el resultado necesario para continuar esta operacion.
            $estadoNuevo = (bool) $datos['activa'];

            // Comprueba la condicion $estadoAnterior === $estadoNuevo antes de ejecutar este bloque.
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

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', $datos['activa'] ? 'La sede quedó activada.' : 'La sede quedó desactivada.');
    }
}
