<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pantallas') || ! Schema::hasTable('permisos') || ! Schema::hasTable('roles_permisos')) {
            return;
        }

        $screen = DB::table('pantallas')->where('codigo', 'admin.sessions')->first();
        $moduleId = DB::table('modulos')->where('codigo', 'seguridad')->value('id');
        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        if ($screen === null || $moduleId === null || $adminRoleId === null) {
            return;
        }

        DB::table('pantallas')->where('id', $screen->id)->update([
            'ruta' => 'management.security_sessions',
            'habilitada' => (bool) $screen->habilitada,
            'actualizado_en' => now(),
        ]);

        $permissionCode = 'pantalla.admin.sessions.consultar';
        $permission = DB::table('permisos')->where('codigo', $permissionCode)->first();
        $permissionId = $permission?->id;
        if ($permissionId === null) {
            $permissionId = DB::table('permisos')->insertGetId([
                'modulo_id' => $moduleId,
                'pantalla_id' => $screen->id,
                'codigo' => $permissionCode,
                'accion' => 'consultar',
                'nombre' => 'Sesiones y accesos - consultar',
                'es_sistema' => true,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        } else {
            DB::table('permisos')->where('id', $permissionId)->update([
                'modulo_id' => $moduleId,
                'pantalla_id' => $screen->id,
                'accion' => 'consultar',
                'activo' => true,
                'actualizado_en' => now(),
            ]);
        }

        DB::table('roles_permisos')->insertOrIgnore([
            'rol_id' => $adminRoleId,
            'permiso_id' => $permissionId,
            'alcance' => 'empresa',
            'creado_en' => now(),
        ]);
        if (Schema::hasTable('elementos_menu')) {
            DB::table('elementos_menu')->where('codigo', 'admin.sessions')->update([
                'pantalla_id' => $screen->id,
                'permiso_id' => $permissionId,
                'habilitado' => true,
                'actualizado_en' => now(),
            ]);
        }
    }

    public function down(): void
    {
        throw new LogicException('This RBAC repair is forward-only to avoid revoking administrator permissions.');
    }
};
