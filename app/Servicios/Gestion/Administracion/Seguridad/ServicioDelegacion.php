<?php

namespace App\Servicios\Gestion\Administracion\Seguridad;

use App\Models\Seguridad\Rol;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioDelegacion dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioDelegacion
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /** @param array<int,int> $permissionIds */
    public function puedeDelegarPermisos(User $actor, array $permissionIds, string $requestedScope = 'sede'): bool
    {
        if (! in_array($requestedScope, ['empresa', 'sede', 'propio'], true)) {
            return false;
        }

        foreach (array_unique($permissionIds) as $permissionId) {
            $scopes = $this->actorScopesForPermission($actor, (int) $permissionId);

            if (! $this->canCoverScope($scopes, $requestedScope)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $role  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    public function puedeAsignarRol(User $actor, Rol $role): bool
    {
        if (($role->es_sistema || $role->alcance_permitido === 'empresa')
            && ! $this->autorizacion->allowsGlobal($actor, 'gestion.seguridad.consultar')) {
            return false;
        }

        $grants = DB::table('roles_permisos')->where('rol_id', $role->getKey())->get(['permiso_id', 'alcance']);

        foreach ($grants as $grant) {
            if (! $this->puedeDelegarPermisos($actor, [(int) $grant->permiso_id], (string) $grant->alcance)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $target  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    public function puedeAdministrarUsuario(User $actor, User $target): bool
    {
        $globalRoleIds = DB::table('usuarios_roles')
            ->where('usuario_id', $target->getKey())
            ->where('activo', true)
            ->where(fn ($query) => $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>', now()))
            ->pluck('rol_id');
        $siteRoleIds = DB::table('usuarios_sedes_roles')
            ->where('usuario_id', $target->getKey())
            ->where('activo', true)
            ->where(fn ($query) => $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>', now()))
            ->pluck('rol_id');
        $grants = DB::table('roles_permisos')
            ->whereIn('rol_id', $globalRoleIds->merge($siteRoleIds)->unique())
            ->get(['permiso_id', 'alcance']);

        foreach ($grants as $grant) {
            if (! $this->puedeDelegarPermisos($actor, [(int) $grant->permiso_id], (string) $grant->alcance)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int, string> */
    private function actorScopesForPermission(User $actor, int $permissionId): array
    {
        $permissionCode = DB::table('permisos')->where('id', $permissionId)->where('activo', true)->value('codigo');
        if (! is_string($permissionCode) || ! $this->autorizacion->allows($actor, $permissionCode)) {
            return [];
        }

        $globalScopes = DB::table('usuarios_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->where('usuarios_roles.usuario_id', $actor->getKey())
            ->where('usuarios_roles.activo', true)
            ->where('roles.activo', true)
            ->where('roles_permisos.permiso_id', $permissionId)
            ->whereIn('roles_permisos.alcance', ['empresa', 'propio'])
            ->where(fn ($query) => $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now()))
            ->pluck('roles_permisos.alcance');
        $siteId = $this->autorizacion->activeSiteIdFor($actor);
        if ($siteId === null) {
            return $globalScopes->unique()->values()->all();
        }

        $siteScopes = DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->join('sedes', 'sedes.id', '=', 'usuarios_sedes_roles.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->where('usuarios_sedes_roles.usuario_id', $actor->getKey())
            ->where('usuarios_sedes_roles.sede_id', $siteId)
            ->where('usuarios_sedes_roles.activo', true)
            ->where('roles.activo', true)
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->where('roles_permisos.permiso_id', $permissionId)
            ->where('roles_permisos.alcance', 'sede')
            ->where(fn ($query) => $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now()))
            ->pluck('roles_permisos.alcance');

        return $globalScopes->merge($siteScopes)->unique()->values()->all();
    }

    /** @param array<int, string> $actorScopes */
    private function canCoverScope(array $actorScopes, string $requestedScope): bool
    {
        return match ($requestedScope) {
            'empresa' => in_array('empresa', $actorScopes, true),
            'sede' => in_array('empresa', $actorScopes, true) || in_array('sede', $actorScopes, true),
            'propio' => in_array('empresa', $actorScopes, true)
                || in_array('sede', $actorScopes, true)
                || in_array('propio', $actorScopes, true),
            default => false,
        };
    }

    /**
     * Ejecuta la operación esUltimoAdministradorGlobal definida por la clase ServicioDelegacion.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    public function esUltimoAdministradorGlobal(User $user): bool
    {
        $globalRoleIds = DB::table('roles_permisos')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->join('roles', 'roles.id', '=', 'roles_permisos.rol_id')
            ->where('permisos.codigo', 'gestion.seguridad.actualizar')
            ->where('roles_permisos.alcance', 'empresa')
            ->where('roles.activo', true)
            ->pluck('roles.id');

        if ($globalRoleIds->isEmpty()) {
            return true;
        }

        $query = DB::table('usuarios_roles')
            ->join('usuarios', 'usuarios.id', '=', 'usuarios_roles.usuario_id')
            ->whereIn('usuarios_roles.rol_id', $globalRoleIds)
            ->where('usuarios_roles.activo', true)
            ->where(function ($query): void {
                $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>', now());
            })
            ->where('usuarios_roles.usuario_id', '!=', $user->getKey());

        if (Schema::hasColumn('usuarios', 'activo')) {
            $query->where('usuarios.activo', true);
        }

        if (Schema::hasColumn('usuarios', 'bloqueado_hasta')) {
            $query->where(fn ($query) => $query->whereNull('usuarios.bloqueado_hasta')->orWhere('usuarios.bloqueado_hasta', '<=', now()));
        }

        return $query->doesntExist();
    }

    /**
     * Ejecuta la operación bloquearAdministradoresGlobales definida por la clase ServicioDelegacion.
     *
     * @return void Resultado de la operación.
     */
    public function bloquearAdministradoresGlobales(): void
    {
        $adminRole = Schema::hasTable('roles')
            ? DB::table('roles')
                ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
                ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('permisos.codigo', 'gestion.seguridad.actualizar')
                ->where('roles_permisos.alcance', 'empresa')
                ->where('roles.activo', true)
                ->lockForUpdate()
                ->first(['roles.id'])
            : null;

        if ($adminRole !== null) {
            return;
        }

        abort(503, 'El rol administrador no está inicializado.');
    }
}
