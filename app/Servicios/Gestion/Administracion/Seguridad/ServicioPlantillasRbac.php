<?php

namespace App\Servicios\Gestion\Administracion\Seguridad;

use Illuminate\Support\Facades\DB;

/**
 * Obtiene la configuración vigente de un rol desde el catálogo RBAC.
 *
 * Este servicio no contiene plantillas por código: la matriz persistida es la
 * única fuente de los permisos recomendados para la interfaz.
 */
final class ServicioPlantillasRbac
{
    /** @return array<int, int> */
    public function permissionIdsFor(?string $roleCode): array
    {
        if ($roleCode === null || ! DB::getSchemaBuilder()->hasTable('roles')) {
            return [];
        }

        return DB::table('roles_permisos')
            ->join('roles', 'roles.id', '=', 'roles_permisos.rol_id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->where('roles.codigo', $roleCode)
            ->where('roles.activo', true)
            ->where('permisos.activo', true)
            ->pluck('permisos.id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
