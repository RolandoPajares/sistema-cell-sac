<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_05_120000_enable_admin_responsibles_screen.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pantallas') || ! Schema::hasTable('permisos') || ! Schema::hasTable('roles_permisos')) {
            return;
        }

        $screen = DB::table('pantallas')->where('codigo', 'admin.responsibles')->first();
        $moduleId = DB::table('modulos')->where('codigo', 'organizacion')->value('id');

        if ($screen === null || $moduleId === null) {
            return;
        }

        DB::table('pantallas')->where('id', $screen->id)->update([
            'ruta' => 'management.company_settings.responsibles',
            'habilitada' => true,
            'actualizado_en' => now(),
        ]);

        $permissionCode = 'pantalla.admin.responsibles.consultar';
        $permission = DB::table('permisos')->where('codigo', $permissionCode)->first();

        if ($permission === null) {
            $permissionId = DB::table('permisos')->insertGetId([
                'modulo_id' => $moduleId,
                'pantalla_id' => $screen->id,
                'codigo' => $permissionCode,
                'accion' => 'consultar',
                'nombre' => 'Responsables de sede - consultar',
                'es_sistema' => true,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        } else {
            $permissionId = $permission->id;
            DB::table('permisos')->where('id', $permissionId)->update([
                'modulo_id' => $moduleId,
                'pantalla_id' => $screen->id,
                'accion' => 'consultar',
                'activo' => true,
                'actualizado_en' => now(),
            ]);
        }

        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        if ($adminRoleId !== null) {
            DB::table('roles_permisos')->insertOrIgnore([
                'rol_id' => $adminRoleId,
                'permiso_id' => $permissionId,
                'alcance' => 'empresa',
                'creado_en' => now(),
            ]);
        }

        if (Schema::hasTable('elementos_menu')) {
            DB::table('elementos_menu')->where('codigo', 'admin.responsibles')->update([
                'pantalla_id' => $screen->id,
                'permiso_id' => $permissionId,
                'habilitado' => true,
                'actualizado_en' => now(),
            ]);
        }
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_05_120000_enable_admin_responsibles_screen.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('This RBAC repair is forward-only to avoid revoking an existing administrator permission.');
    }
};
