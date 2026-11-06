<?php

namespace App\Servicios;

use App\Models\Organizacion\ResponsableOrganizacional;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Define la responsabilidad de la clase ServicioResponsablesOrganizacionales dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioResponsablesOrganizacionales
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $idsSede  Parámetro recibido por la operación.
     * @return Collection Resultado de la operación.
     */
    public function listar(array $idsSede): Collection
    {
        if (! Schema::hasTable('responsables_organizacionales')) {
            return new Collection;
        }

        return ResponsableOrganizacional::query()
            ->with(['responsable:id,nombre', 'sede:id,nombre', 'almacen:id,nombre'])
            ->whereIn('sede_id', $idsSede)
            ->orderByDesc('vigente_desde')->orderByDesc('id')->get();
    }

    /** @param array{sede_id:int,tipo_recurso:string,almacen_id?:?int,responsable_usuario_id:int,cargo:string,vigente_desde:string,vigente_hasta?:?string,motivo:string} $datos */
    public function reasignar(User $actor, array $datos, RegistradorAuditoria $auditoria, ?string $ip): ResponsableOrganizacional
    {
        if (! Schema::hasTable('responsables_organizacionales')) {
            abort(503, 'La estructura de responsables aún no está instalada. Revise el archivo SQL aprobado.');
        }

        return DB::transaction(function () use ($actor, $datos, $auditoria, $ip): ResponsableOrganizacional {
            $sede = DB::table('sedes')->where('id', $datos['sede_id'])->lockForUpdate()->first();
            abort_if($sede === null, 404);

            $idsSedeAutorizados = app(ServicioAutorizacion::class)->authorizedSiteIds($actor, 'gestion.organizacion.actualizar');
            abort_unless(in_array((int) $sede->id, $idsSedeAutorizados, true), 403);

            $usuarioValido = DB::table('usuarios_sedes_roles as asignacion')
                ->join('roles as rol', 'rol.id', '=', 'asignacion.rol_id')
                ->where('asignacion.usuario_id', $datos['responsable_usuario_id'])
                ->where('asignacion.sede_id', $sede->id)->where('asignacion.activo', true)
                ->where('rol.activo', true)
                ->where(fn ($consulta) => $consulta->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now()))
                ->whereExists(fn ($consulta) => $consulta->selectRaw('1')->from('usuarios')->whereColumn('usuarios.id', 'asignacion.usuario_id')->where('usuarios.activo', true))
                ->exists();
            abort_unless($usuarioValido, 422, 'Selecciona un usuario activo.');

            $tipo = $datos['tipo_recurso'];
            $almacenId = $tipo === 'almacen' ? (int) $datos['almacen_id'] : null;
            if ($almacenId !== null) {
                abort_unless(DB::table('almacenes')->where('id', $almacenId)->where('sede_id', $sede->id)->where('activo', true)->exists(), 422, 'El almacén debe pertenecer a la sede seleccionada y estar activo.');
            }

            $inicio = Carbon::createFromFormat('Y-m-d', $datos['vigente_desde'])->startOfDay();
            $fin = filled($datos['vigente_hasta'] ?? null) ? Carbon::createFromFormat('Y-m-d', (string) $datos['vigente_hasta'])->endOfDay() : null;
            $vigentesSolapados = ResponsableOrganizacional::query()
                ->where('empresa_id', $sede->empresa_id)->where('sede_id', $sede->id)
                ->where('tipo_recurso', $tipo)->where('cargo', trim($datos['cargo']))
                ->when($tipo === 'almacen', fn ($consulta) => $consulta->where('almacen_id', $almacenId))
                ->when($tipo === 'sede', fn ($consulta) => $consulta->whereNull('almacen_id'))
                ->whereDate('vigente_desde', '<=', $fin?->toDateString() ?? '9999-12-31')
                ->where(fn ($consulta) => $consulta->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $inicio->toDateString()))
                ->lockForUpdate()->get();

            if ($vigentesSolapados->count() > 1) {
                throw ValidationException::withMessages(['vigente_desde' => 'Existen vigencias superpuestas que deben corregirse antes de reasignar.']);
            }

            $anterior = $vigentesSolapados->first();
            if ($anterior !== null) {
                if ($inicio->toDateString() <= $anterior->vigente_desde->toDateString()) {
                    throw ValidationException::withMessages(['vigente_desde' => 'La nueva vigencia debe iniciar después del comienzo de la asignación que reemplaza.']);
                }
                $anterior->vigente_hasta = $inicio->copy()->subDay()->toDateString();
                $anterior->save();
            }
            $asignacionPrevia = $anterior ?? ResponsableOrganizacional::query()
                ->where('empresa_id', $sede->empresa_id)->where('sede_id', $sede->id)
                ->where('tipo_recurso', $tipo)->where('cargo', trim($datos['cargo']))
                ->when($tipo === 'almacen', fn ($consulta) => $consulta->where('almacen_id', $almacenId))
                ->when($tipo === 'sede', fn ($consulta) => $consulta->whereNull('almacen_id'))
                ->whereNotNull('vigente_hasta')->whereDate('vigente_hasta', '<', $inicio->toDateString())
                ->orderByDesc('vigente_desde')->orderByDesc('id')->lockForUpdate()->first();

            $responsable = ResponsableOrganizacional::query()->create([
                'empresa_id' => (int) $sede->empresa_id,
                'sede_id' => (int) $sede->id,
                'tipo_recurso' => $tipo,
                'almacen_id' => $almacenId,
                'responsable_usuario_id' => (int) $datos['responsable_usuario_id'],
                'cargo' => trim($datos['cargo']),
                'vigente_desde' => $inicio->toDateString(),
                'vigente_hasta' => $fin?->toDateString(),
                'motivo' => trim($datos['motivo']),
                'reemplaza_asignacion_id' => $asignacionPrevia?->getKey(),
                'asignado_por_usuario_id' => $actor->getKey(),
                'activo' => true,
            ]);

            $auditoria->record($actor->getKey(), 'reasignar_responsable_organizacional', 'responsables_organizacionales', $responsable->getKey(),
                $anterior?->only(['responsable_usuario_id', 'vigente_desde', 'vigente_hasta']),
                $responsable->only(['empresa_id', 'sede_id', 'tipo_recurso', 'almacen_id', 'responsable_usuario_id', 'cargo', 'vigente_desde', 'vigente_hasta', 'reemplaza_asignacion_id']),
                $ip,
                scope: ['empresa_id' => (int) $sede->empresa_id, 'sede_id' => (int) $sede->id]);

            return $responsable;
        });
    }
}
