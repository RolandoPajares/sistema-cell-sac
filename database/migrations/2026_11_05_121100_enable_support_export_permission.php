<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('modulos') || ! Schema::hasTable('permisos') || ! Schema::hasTable('roles_permisos')) {
            return;
        }
        $moduleId = DB::table('modulos')->where('codigo', 'soporte')->value('id');
        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        if ($moduleId === null || $adminRoleId === null) {
            return;
        }
        $code = 'gestion.soporte.exportar';
        $permission = DB::table('permisos')->where('codigo', $code)->first();
        $permissionId = $permission?->id;
        if ($permissionId === null) {
            $permissionId = DB::table('permisos')->insertGetId([
                'modulo_id' => $moduleId, 'pantalla_id' => null, 'codigo' => $code, 'accion' => 'exportar',
                'nombre' => 'Exportar - Soporte y reclamos', 'es_sistema' => true, 'activo' => true,
                'creado_en' => now(), 'actualizado_en' => now(),
            ]);
        } else {
            DB::table('permisos')->where('id', $permissionId)->update(['modulo_id' => $moduleId, 'accion' => 'exportar', 'activo' => true, 'actualizado_en' => now()]);
        }
        DB::table('roles_permisos')->insertOrIgnore(['rol_id' => $adminRoleId, 'permiso_id' => $permissionId, 'alcance' => 'empresa', 'creado_en' => now()]);
    }

    public function down(): void
    {
        throw new LogicException('This RBAC repair is forward-only to avoid revoking administrator permissions.');
    }
};
