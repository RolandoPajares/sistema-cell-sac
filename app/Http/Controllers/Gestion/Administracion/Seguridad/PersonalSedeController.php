<?php

namespace App\Http\Controllers\Gestion\Administracion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Administracion\Seguridad\FiltroPersonalSedeRequest;
use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase PersonalSedeController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class PersonalSedeController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function listar(FiltroPersonalSedeRequest $request, ServicioAutorizacion $autorizacion): View
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);

        $idsSede = $autorizacion->authorizedSiteIds($usuario, 'gestion.organizacion.consultar');
        $consultaBase = $this->consultaAsignaciones($idsSede);
        $filtros = $request->validated();
        $consulta = clone $consultaBase;

        if (filled($filtros['buscar'] ?? null)) {
            $buscar = trim((string) $filtros['buscar']);
            $consulta->where(static function (Builder $subconsulta) use ($buscar): void {
                $subconsulta->where('usuario.nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('usuario.correo', 'like', '%'.$buscar.'%')
                    ->orWhere('rol.nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('sede.nombre', 'like', '%'.$buscar.'%');
            });
        }

        if (filled($filtros['sede_id'] ?? null)) {
            $consulta->where('asignacion.sede_id', (int) $filtros['sede_id']);
        }

        if (($filtros['estado'] ?? null) === 'vigente') {
            $this->restringirAsignacionesVigentes($consulta, now());
        } elseif (($filtros['estado'] ?? null) === 'no_vigente') {
            $ahora = now();
            $consulta->where(static function (Builder $subconsulta) use ($ahora): void {
                $subconsulta->where('asignacion.activo', false)
                    ->orWhere('usuario.activo', false)
                    ->orWhere('rol.activo', false)
                    ->orWhere('sede.activa', false)
                    ->orWhere('empresa.activo', false)
                    ->orWhere(static function (Builder $vigencia) use ($ahora): void {
                        $vigencia->whereNotNull('asignacion.vigente_hasta')
                            ->where('asignacion.vigente_hasta', '<=', $ahora);
                    });
            });
        }

        $asignaciones = $consulta
            ->select([
                'asignacion.usuario_id', 'asignacion.sede_id', 'asignacion.rol_id',
                'asignacion.asignado_en', 'asignacion.vigente_hasta', 'asignacion.activo as asignacion_activa',
                'usuario.nombre as usuario_nombre', 'usuario.correo as usuario_correo', 'usuario.activo as usuario_activo',
                'rol.nombre as rol_nombre', 'rol.activo as rol_activo',
                'sede.nombre as sede_nombre', 'sede.activa as sede_activa', 'empresa.activo as empresa_activa',
            ])
            ->orderBy('sede.nombre')->orderBy('usuario.nombre')->orderBy('asignacion.rol_id')
            ->paginate((int) ($filtros['por_pagina'] ?? 15))->withQueryString();
        $ahora = now();
        $asignaciones->through(static function (object $asignacion) use ($ahora): object {
            $asignacion->vigente = (bool) $asignacion->asignacion_activa
                && (bool) $asignacion->usuario_activo
                && (bool) $asignacion->rol_activo
                && (bool) $asignacion->sede_activa
                && (bool) $asignacion->empresa_activa
                && ($asignacion->vigente_hasta === null || Carbon::parse($asignacion->vigente_hasta)->greaterThan($ahora));

            return $asignacion;
        });

        $resumen = $this->resumenPersonal($consultaBase, $ahora);
        $personalPorSede = $this->personalPorSede($consultaBase, $ahora);
        $personalPorRol = $this->personalPorRol($consultaBase, $ahora);
        $sedesAutorizadas = Sede::query()->whereIn('id', $idsSede)->orderBy('nombre')->get(['id', 'codigo', 'nombre']);

        return view('gestion.administracion.seguridad.personal.listar', [
            'asignaciones' => $asignaciones,
            'filtros' => $filtros,
            'sedesAutorizadas' => $sedesAutorizadas,
            'resumen' => $resumen,
            'personalPorSede' => $personalPorSede,
            'personalPorRol' => $personalPorRol,
        ]);
    }

    /** @param array<int,int> $idsSede */
    private function consultaAsignaciones(array $idsSede): Builder
    {
        return DB::table('usuarios_sedes_roles as asignacion')
            ->join('usuarios as usuario', 'usuario.id', '=', 'asignacion.usuario_id')
            ->join('roles as rol', 'rol.id', '=', 'asignacion.rol_id')
            ->join('sedes as sede', 'sede.id', '=', 'asignacion.sede_id')
            ->join('empresas as empresa', 'empresa.id', '=', 'sede.empresa_id')
            ->whereIn('asignacion.sede_id', $idsSede);
    }

    /**
     * Ejecuta la operación restringirAsignacionesVigentes definida por la clase PersonalSedeController.
     *
     * @param  mixed  $consulta  Parámetro recibido por la operación.
     * @param  mixed  $ahora  Parámetro recibido por la operación.
     * @return Builder Resultado de la operación.
     */
    private function restringirAsignacionesVigentes(Builder $consulta, Carbon $ahora): Builder
    {
        return $consulta->where('asignacion.activo', true)
            ->where('usuario.activo', true)
            ->where('rol.activo', true)
            ->where('sede.activa', true)
            ->where('empresa.activo', true)
            ->where(static function (Builder $vigencia) use ($ahora): void {
                $vigencia->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', $ahora);
            });
    }

    /** @return array{personal_total:int,asignaciones_vigentes:int,sedes_con_personal:int,roles_vigentes:int} */
    private function resumenPersonal(Builder $consultaBase, Carbon $ahora): array
    {
        $condicionVigente = '(asignacion.activo = 1 AND usuario.activo = 1 AND rol.activo = 1 AND sede.activa = 1 AND empresa.activo = 1 AND (asignacion.vigente_hasta IS NULL OR asignacion.vigente_hasta > ?))';
        $resumen = (clone $consultaBase)
            ->selectRaw('COUNT(DISTINCT usuario.id) AS personal_total')
            ->selectRaw('COUNT(CASE WHEN '.$condicionVigente.' THEN asignacion.usuario_id END) AS asignaciones_vigentes', [$ahora])
            ->selectRaw('COUNT(DISTINCT CASE WHEN '.$condicionVigente.' THEN sede.id END) AS sedes_con_personal', [$ahora])
            ->selectRaw('COUNT(DISTINCT CASE WHEN '.$condicionVigente.' THEN rol.id END) AS roles_vigentes', [$ahora])
            ->first();

        return [
            'personal_total' => (int) ($resumen->personal_total ?? 0),
            'asignaciones_vigentes' => (int) ($resumen->asignaciones_vigentes ?? 0),
            'sedes_con_personal' => (int) ($resumen->sedes_con_personal ?? 0),
            'roles_vigentes' => (int) ($resumen->roles_vigentes ?? 0),
        ];
    }

    /** @return array<int,object> */
    private function personalPorSede(Builder $consultaBase, Carbon $ahora): array
    {
        return $this->restringirAsignacionesVigentes(clone $consultaBase, $ahora)
            ->select('sede.id', 'sede.nombre')
            ->selectRaw('COUNT(DISTINCT usuario.id) AS total_personal')
            ->groupBy('sede.id', 'sede.nombre')
            ->orderByDesc('total_personal')->orderBy('sede.nombre')->limit(6)->get()->all();
    }

    /** @return array<int,object> */
    private function personalPorRol(Builder $consultaBase, Carbon $ahora): array
    {
        return $this->restringirAsignacionesVigentes(clone $consultaBase, $ahora)
            ->select('rol.id', 'rol.nombre')
            ->selectRaw('COUNT(DISTINCT usuario.id) AS total_personal')
            ->groupBy('rol.id', 'rol.nombre')
            ->orderByDesc('total_personal')->orderBy('rol.nombre')->limit(6)->get()->all();
    }
}
